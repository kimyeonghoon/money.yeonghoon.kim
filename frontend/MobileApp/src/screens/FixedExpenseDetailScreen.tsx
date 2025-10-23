/**
 * 고정지출 상세보기 화면
 *
 * 고정지출 항목의 상세 정보와 월별 기록을 표시합니다.
 */

import React, { useState, useCallback, useEffect } from 'react';
import {
  Text,
  View,
  StyleSheet,
  Alert,
  ScrollView,
  ActivityIndicator,
} from 'react-native';
import { Screen, Card, Button } from '../components';
import { typography, spacing, colors } from '../theme';
import {
  getFixedExpense,
  getMonthlySummary,
  deleteFixedExpense,
  markRecordAsPaid,
} from '../services/fixedExpenseService';
import { FixedExpense, MonthlyExpensesSummary, FixedExpenseRecord } from '../types/fixedExpense';

interface FixedExpenseDetailScreenProps {
  navigation: {
    navigate: (screen: string, params: any) => void;
    goBack: () => void;
  };
  route: {
    params: {
      expenseId: number;
    };
  };
}

export const FixedExpenseDetailScreen: React.FC<FixedExpenseDetailScreenProps> = ({
  navigation,
  route,
}) => {
  const { expenseId } = route.params;

  const [loading, setLoading] = useState(true);
  const [expense, setExpense] = useState<FixedExpense | null>(null);
  const [monthlySummary, setMonthlySummary] = useState<MonthlyExpensesSummary | null>(null);

  useEffect(() => {
    loadData();
  }, [expenseId]);

  const loadData = useCallback(async () => {
    try {
      setLoading(true);

      const now = new Date();
      const year = now.getFullYear();
      const month = now.getMonth() + 1;

      const [expenseData, summaryData] = await Promise.all([
        getFixedExpense(expenseId),
        getMonthlySummary(year, month),
      ]);

      setExpense(expenseData);
      setMonthlySummary(summaryData);
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : '고정지출을 불러오는데 실패했습니다';
      Alert.alert('오류', errorMessage);
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  }, [expenseId, navigation]);

  const getCurrentRecord = useCallback((): FixedExpenseRecord | null => {
    if (!monthlySummary || !expense) return null;

    const expenseWithRecords = monthlySummary.expenses.find((e) => e.id === expense.id);
    if (!expenseWithRecords || expenseWithRecords.records.length === 0) {
      return null;
    }

    return expenseWithRecords.records[0];
  }, [monthlySummary, expense]);

  const handleMarkAsPaid = useCallback(async () => {
    const record = getCurrentRecord();
    if (!record) {
      Alert.alert('오류', '이번 달 기록이 없습니다');
      return;
    }

    try {
      await markRecordAsPaid(record.id);
      Alert.alert('성공', '지출이 완료 처리되었습니다');
      loadData();
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : '지출 완료 처리에 실패했습니다';
      Alert.alert('오류', errorMessage);
    }
  }, [getCurrentRecord, loadData]);

  const handleEdit = useCallback(() => {
    navigation.navigate('EditFixedExpense', { expenseId });
  }, [navigation, expenseId]);

  const handleDelete = useCallback(() => {
    if (!expense) return;

    Alert.alert(
      '고정지출 삭제',
      `"${expense.name}" 항목을 삭제하시겠습니까?\n\n현재 달인 경우 이번 달 기록도 삭제됩니다.\n다음 달 이후인 경우 과거 기록은 보존됩니다.`,
      [
        { text: '취소', style: 'cancel' },
        {
          text: '삭제',
          style: 'destructive',
          onPress: async () => {
            try {
              await deleteFixedExpense(expense.id);
              Alert.alert('성공', `"${expense.name}" 항목이 삭제되었습니다`);
              navigation.goBack();
            } catch (err) {
              const errorMessage = err instanceof Error ? err.message : '삭제에 실패했습니다';
              Alert.alert('오류', errorMessage);
            }
          },
        },
      ]
    );
  }, [expense, navigation]);

  if (loading) {
    return (
      <Screen style={styles.centerContainer}>
        <ActivityIndicator size="large" color={colors.primary} />
        <Text style={styles.loadingText}>로딩 중...</Text>
      </Screen>
    );
  }

  if (!expense) {
    return null;
  }

  const currentRecord = getCurrentRecord();

  return (
    <Screen>
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <Card style={styles.card}>
          <Text style={styles.title}>{expense.name}</Text>

          <View style={styles.infoRow}>
            <Text style={styles.label}>금액:</Text>
            <Text style={styles.value}>
              {expense.default_amount !== null
                ? `₩${expense.default_amount.toLocaleString()}`
                : '변동 금액'}
            </Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.label}>지출일:</Text>
            <Text style={styles.value}>
              {expense.expected_payment_day !== null
                ? `매월 ${expense.expected_payment_day}일`
                : '지정 안 함'}
            </Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.label}>고정 금액:</Text>
            <Text style={styles.value}>{expense.is_fixed_amount ? '예' : '아니오'}</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.label}>상태:</Text>
            <Text style={[styles.value, expense.is_active ? styles.activeText : styles.inactiveText]}>
              {expense.is_active ? '활성' : '비활성'}
            </Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.label}>유효 기간:</Text>
            <Text style={styles.value}>
              {expense.valid_from}
              {expense.valid_until ? ` ~ ${expense.valid_until}` : ' ~ 현재'}
            </Text>
          </View>
        </Card>

        <Card style={styles.card}>
          <Text style={styles.sectionTitle}>
            {monthlySummary ? `${monthlySummary.year}년 ${monthlySummary.month}월 기록` : '월별 기록'}
          </Text>

          {currentRecord ? (
            <View>
              <View style={styles.recordRow}>
                <Text style={styles.label}>금액:</Text>
                <Text style={styles.value}>₩{currentRecord.amount.toLocaleString()}</Text>
              </View>

              <View style={styles.recordRow}>
                <Text style={styles.label}>상태:</Text>
                <Text
                  style={[
                    styles.value,
                    currentRecord.is_paid ? styles.paidText : styles.unpaidText,
                  ]}
                >
                  {currentRecord.is_paid ? '완료' : '미납'}
                </Text>
              </View>

              {currentRecord.is_paid && currentRecord.paid_at && (
                <View style={styles.recordRow}>
                  <Text style={styles.label}>완료일:</Text>
                  <Text style={styles.value}>
                    {new Date(currentRecord.paid_at).toLocaleDateString('ko-KR')}
                  </Text>
                </View>
              )}

              {!currentRecord.is_paid && (
                <Button
                  title="완료 처리"
                  onPress={handleMarkAsPaid}
                  variant="primary"
                  style={styles.markPaidButton}
                />
              )}
            </View>
          ) : (
            <Text style={styles.emptyText}>이번 달 기록이 없습니다</Text>
          )}
        </Card>

        <View style={styles.buttonGroup}>
          <Button
            title="수정"
            onPress={handleEdit}
            variant="secondary"
            style={styles.button}
          />
          <Button
            title="삭제"
            onPress={handleDelete}
            variant="danger"
            style={styles.button}
          />
        </View>
      </ScrollView>
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
  scrollContent: {
    padding: spacing.lg,
    paddingBottom: spacing.xl * 2,
  },
  card: {
    padding: spacing.lg,
    marginBottom: spacing.lg,
  },
  title: {
    ...typography.title,
    color: colors.text,
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    ...typography.heading,
    color: colors.text,
    marginBottom: spacing.md,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  recordRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  label: {
    ...typography.body,
    color: colors.textSecondary,
  },
  value: {
    ...typography.subheading,
    color: colors.text,
  },
  activeText: {
    color: colors.success || colors.primary,
  },
  inactiveText: {
    color: colors.error,
  },
  paidText: {
    color: colors.success || colors.primary,
  },
  unpaidText: {
    color: colors.error,
  },
  emptyText: {
    ...typography.body,
    color: colors.textSecondary,
    textAlign: 'center',
    paddingVertical: spacing.lg,
  },
  markPaidButton: {
    marginTop: spacing.md,
  },
  buttonGroup: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: spacing.md,
  },
  button: {
    flex: 1,
  },
});
