/**
 * 고정지출 수정 화면
 *
 * 기존 고정지출 항목을 수정합니다.
 */

import React, { useState, useCallback, useEffect } from 'react';
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
  ActivityIndicator,
} from 'react-native';
import { Screen, Card, Button } from '../components';
import { typography, spacing, colors } from '../theme';
import {
  getFixedExpense,
  updateFixedExpense,
} from '../services/fixedExpenseService';
import { FixedExpense, FixedExpenseUpdateRequest } from '../types/fixedExpense';

interface EditFixedExpenseScreenProps {
  navigation: {
    goBack: () => void;
  };
  route: {
    params: {
      expenseId: number;
    };
  };
}

export const EditFixedExpenseScreen: React.FC<EditFixedExpenseScreenProps> = ({
  navigation,
  route,
}) => {
  const { expenseId } = route.params;

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [originalExpense, setOriginalExpense] = useState<FixedExpense | null>(null);

  const [name, setName] = useState('');
  const [amount, setAmount] = useState('');
  const [paymentDay, setPaymentDay] = useState('');
  const [isFixedAmount, setIsFixedAmount] = useState(false);
  const [isActive, setIsActive] = useState(true);

  useEffect(() => {
    loadExpense();
  }, [expenseId]);

  const loadExpense = useCallback(async () => {
    try {
      setLoading(true);
      const expense = await getFixedExpense(expenseId);
      setOriginalExpense(expense);
      setName(expense.name);
      setAmount(expense.default_amount !== null ? expense.default_amount.toString() : '');
      setPaymentDay(expense.expected_payment_day !== null ? expense.expected_payment_day.toString() : '');
      setIsFixedAmount(expense.is_fixed_amount);
      setIsActive(expense.is_active);
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : '고정지출을 불러오는데 실패했습니다';
      Alert.alert('오류', errorMessage);
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  }, [expenseId, navigation]);

  const hasChanges = useCallback((): boolean => {
    if (!originalExpense) return false;

    return (
      name !== originalExpense.name ||
      amount !== (originalExpense.default_amount !== null ? originalExpense.default_amount.toString() : '') ||
      paymentDay !== (originalExpense.expected_payment_day !== null ? originalExpense.expected_payment_day.toString() : '') ||
      isFixedAmount !== originalExpense.is_fixed_amount ||
      isActive !== originalExpense.is_active
    );
  }, [originalExpense, name, amount, paymentDay, isFixedAmount, isActive]);

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

    setSaving(true);

    try {
      const request: FixedExpenseUpdateRequest = {
        name: name.trim(),
        default_amount: amount.trim() !== '' ? parseFloat(amount) : null,
        is_fixed_amount: isFixedAmount,
        expected_payment_day: paymentDay.trim() !== '' ? parseInt(paymentDay, 10) : null,
        is_active: isActive,
      };

      await updateFixedExpense(expenseId, request);

      Alert.alert('성공', '고정지출이 수정되었습니다');
      navigation.goBack();
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : '고정지출 수정에 실패했습니다';
      Alert.alert('오류', errorMessage);
    } finally {
      setSaving(false);
    }
  }, [expenseId, name, amount, paymentDay, isFixedAmount, isActive, validateInputs, navigation]);

  const handleCancel = useCallback(() => {
    if (hasChanges()) {
      Alert.alert(
        '취소 확인',
        '변경한 내용이 저장되지 않습니다. 취소하시겠습니까?',
        [
          { text: '계속 수정', style: 'cancel' },
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
  }, [hasChanges, navigation]);

  if (loading) {
    return (
      <Screen style={styles.centerContainer}>
        <ActivityIndicator size="large" color={colors.primary} />
        <Text style={styles.loadingText}>로딩 중...</Text>
      </Screen>
    );
  }

  return (
    <Screen>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.container}
      >
        <ScrollView contentContainerStyle={styles.scrollContent}>
          <Card style={styles.card}>
            <Text style={styles.title}>고정지출 수정</Text>

            <View style={styles.formGroup}>
              <Text style={styles.label}>항목명 *</Text>
              <TextInput
                style={styles.input}
                placeholder="항목명 (예: 월세, 전기세)"
                value={name}
                onChangeText={setName}
                editable={!saving}
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
                editable={!saving}
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
                editable={!saving}
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
                  disabled={saving}
                  trackColor={{ false: colors.border, true: colors.primary }}
                  thumbColor={colors.cardBackground}
                />
              </View>
              <Text style={styles.hint}>
                고정 금액이면 매달 같은 금액이 청구됩니다
              </Text>
            </View>

            <View style={styles.formGroup}>
              <View style={styles.switchRow}>
                <Text style={styles.label}>활성 상태</Text>
                <Switch
                  testID="active-toggle"
                  value={isActive}
                  onValueChange={setIsActive}
                  disabled={saving}
                  trackColor={{ false: colors.border, true: colors.primary }}
                  thumbColor={colors.cardBackground}
                />
              </View>
              <Text style={styles.hint}>
                비활성화하면 목록에서 숨겨집니다
              </Text>
            </View>
          </Card>

          <View style={styles.buttonGroup}>
            <Button
              title="취소"
              onPress={handleCancel}
              variant="ghost"
              disabled={saving}
              style={styles.button}
            />
            <Button
              title="저장"
              onPress={handleSave}
              variant="primary"
              loading={saving}
              style={styles.button}
            />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </Screen>
  );
};

const styles = StyleSheet.create({
  centerContainer: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    ...typography.body,
    marginTop: spacing.md,
    color: colors.textSecondary,
  },
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
