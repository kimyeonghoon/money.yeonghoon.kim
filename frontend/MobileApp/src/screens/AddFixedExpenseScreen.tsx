/**
 * 고정지출 추가 화면
 *
 * 새로운 고정지출 항목을 생성합니다.
 */

import React, { useState, useCallback } from 'react';
import {
  Text,
  View,
  StyleSheet,
  TextInput,
  Alert,
  Switch,
  ScrollView,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { Screen, Card, Button } from '../components';
import { typography, spacing, colors } from '../theme';
import { createFixedExpense } from '../services/fixedExpenseService';
import { FixedExpenseCreateRequest } from '../types/fixedExpense';

interface AddFixedExpenseScreenProps {
  navigation: {
    goBack: () => void;
  };
}

export const AddFixedExpenseScreen: React.FC<AddFixedExpenseScreenProps> = ({
  navigation,
}) => {
  const [name, setName] = useState('');
  const [amount, setAmount] = useState('');
  const [paymentDay, setPaymentDay] = useState('');
  const [isFixedAmount, setIsFixedAmount] = useState(false);
  const [loading, setLoading] = useState(false);

  const hasUnsavedChanges = name.trim() !== '' || amount.trim() !== '' || paymentDay.trim() !== '';

  const validateInputs = useCallback((): string | null => {
    if (name.trim() === '') {
      return '항목명을 입력해주세요';
    }

    if (paymentDay.trim() !== '') {
      const day = parseInt(paymentDay, 10);
      if (isNaN(day) || day < 1 || day > 31) {
        return '지출일은 1-31 사이의 숫자여야 합니다';
      }
    }

    if (amount.trim() !== '') {
      const amountNum = parseFloat(amount);
      if (isNaN(amountNum) || amountNum < 0) {
        return '금액은 0 이상이어야 합니다';
      }
    }

    return null;
  }, [name, paymentDay, amount]);

  const handleSave = useCallback(async () => {
    const error = validateInputs();
    if (error) {
      Alert.alert('입력 오류', error);
      return;
    }

    setLoading(true);

    try {
      const request: FixedExpenseCreateRequest = {
        name: name.trim(),
        default_amount: amount.trim() !== '' ? parseFloat(amount) : null,
        is_fixed_amount: isFixedAmount,
        expected_payment_day: paymentDay.trim() !== '' ? parseInt(paymentDay, 10) : null,
      };

      await createFixedExpense(request);

      Alert.alert('성공', '고정지출이 추가되었습니다');
      navigation.goBack();
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : '고정지출 추가에 실패했습니다';
      Alert.alert('오류', errorMessage);
    } finally {
      setLoading(false);
    }
  }, [name, amount, paymentDay, isFixedAmount, validateInputs, navigation]);

  const handleCancel = useCallback(() => {
    if (hasUnsavedChanges) {
      Alert.alert(
        '취소 확인',
        '입력한 내용이 저장되지 않습니다. 취소하시겠습니까?',
        [
          { text: '계속 작성', style: 'cancel' },
          {
            text: '취소',
            style: 'destructive',
            onPress: () => navigation.goBack(),
          },
        ]
      );
    } else {
      navigation.goBack();
    }
  }, [hasUnsavedChanges, navigation]);

  return (
    <Screen>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.container}
      >
        <ScrollView contentContainerStyle={styles.scrollContent}>
          <Card style={styles.card}>
            <Text style={styles.title}>고정지출 추가</Text>

            <View style={styles.formGroup}>
              <Text style={styles.label}>항목명 *</Text>
              <TextInput
                style={styles.input}
                placeholder="항목명 (예: 월세, 전기세)"
                value={name}
                onChangeText={setName}
                editable={!loading}
              />
            </View>

            <View style={styles.formGroup}>
              <Text style={styles.label}>금액</Text>
              <TextInput
                style={styles.input}
                placeholder="금액 (선택사항)"
                value={amount}
                onChangeText={setAmount}
                keyboardType="numeric"
                editable={!loading}
              />
              <Text style={styles.hint}>비어 있으면 매달 변동 금액입니다</Text>
            </View>

            <View style={styles.formGroup}>
              <Text style={styles.label}>매월 지출일</Text>
              <TextInput
                style={styles.input}
                placeholder="매월 지출일 (1-31)"
                value={paymentDay}
                onChangeText={setPaymentDay}
                keyboardType="numeric"
                editable={!loading}
              />
              <Text style={styles.hint}>비어 있으면 지정 안 함</Text>
            </View>

            <View style={styles.formGroup}>
              <View style={styles.switchRow}>
                <Text style={styles.label}>고정 금액</Text>
                <Switch
                  testID="fixed-amount-toggle"
                  value={isFixedAmount}
                  onValueChange={setIsFixedAmount}
                  disabled={loading}
                  trackColor={{ false: colors.border, true: colors.primary }}
                  thumbColor={colors.cardBackground}
                />
              </View>
              <Text style={styles.hint}>
                고정 금액이면 매달 같은 금액이 청구됩니다
              </Text>
            </View>
          </Card>

          <View style={styles.buttonGroup}>
            <Button
              title="취소"
              onPress={handleCancel}
              variant="ghost"
              disabled={loading}
              style={styles.button}
            />
            <Button
              title="저장"
              onPress={handleSave}
              variant="primary"
              loading={loading}
              style={styles.button}
            />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </Screen>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContent: {
    padding: spacing.lg,
    paddingBottom: spacing.xl * 2,
  },
  card: {
    padding: spacing.lg,
  },
  title: {
    ...typography.title,
    color: colors.text,
    marginBottom: spacing.lg,
  },
  formGroup: {
    marginBottom: spacing.lg,
  },
  label: {
    ...typography.subheading,
    color: colors.text,
    marginBottom: spacing.sm,
  },
  input: {
    ...typography.body,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    padding: spacing.md,
    color: colors.text,
    backgroundColor: colors.background,
  },
  hint: {
    ...typography.caption,
    color: colors.textSecondary,
    marginTop: spacing.xs,
  },
  switchRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  buttonGroup: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: spacing.lg,
    gap: spacing.md,
  },
  button: {
    flex: 1,
  },
});
