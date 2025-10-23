/**
 * 고정지출 목록 화면
 *
 * 사용자의 고정지출 항목을 표시하고 관리합니다.
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  Text,
  View,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  RefreshControl,
} from 'react-native';
import { Screen, Card } from '../components';
import { typography, spacing, colors } from '../theme';
import { FixedExpense } from '../types/fixedExpense';
import {
  getFixedExpenses,
  deleteFixedExpense,
} from '../services/fixedExpenseService';

interface FixedExpenseItemProps {
  expense: FixedExpense;
  onPress: (expense: FixedExpense) => void;
  onDelete: (expense: FixedExpense) => void;
}

const FixedExpenseItem: React.FC<FixedExpenseItemProps> = ({
  expense,
  onPress,
  onDelete,
}) => {
  const handleDelete = (): void => {
    Alert.alert(
      '고정지출 삭제',
      `"${expense.name}" 항목을 삭제하시겠습니까?\n\n현재 달인 경우 이번 달 기록도 삭제됩니다.\n다음 달 이후인 경우 과거 기록은 보존됩니다.`,
      [
        { text: '취소', style: 'cancel' },
        {
          text: '삭제',
          style: 'destructive',
          onPress: () => onDelete(expense),
        },
      ]
    );
  };

  return (
    <Card style={styles.expenseCard}>
      <TouchableOpacity
        onPress={() => onPress(expense)}
        style={styles.expenseContent}
      >
        <View style={styles.expenseHeader}>
          <Text style={styles.expenseName}>{expense.name}</Text>
          {!expense.is_active && (
            <Text style={styles.inactiveBadge}>비활성</Text>
          )}
        </View>
        <View style={styles.expenseDetails}>
          <Text style={styles.expenseAmount}>
            {expense.default_amount
              ? `₩${expense.default_amount.toLocaleString()}`
              : '변동 금액'}
          </Text>
          {expense.expected_payment_day && (
            <Text style={styles.expenseDay}>
              매월 {expense.expected_payment_day}일
            </Text>
          )}
        </View>
        <View style={styles.validityInfo}>
          <Text style={styles.validityText}>
            유효: {expense.valid_from}
            {expense.valid_until ? ` ~ ${expense.valid_until}` : ' ~ 현재'}
          </Text>
        </View>
      </TouchableOpacity>
      <TouchableOpacity
        style={styles.deleteButton}
        onPress={handleDelete}
      >
        <Text style={styles.deleteButtonText}>삭제</Text>
      </TouchableOpacity>
    </Card>
  );
};

export const FixedExpensesScreen: React.FC = () => {
  const [expenses, setExpenses] = useState<FixedExpense[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadExpenses = useCallback(async () => {
    try {
      setError(null);
      const data = await getFixedExpenses(true);
      setExpenses(data);
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : '고정지출 목록을 불러오는데 실패했습니다';
      setError(errorMessage);
      Alert.alert('오류', errorMessage);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadExpenses();
  }, [loadExpenses]);

  const handleRefresh = useCallback(() => {
    setRefreshing(true);
    loadExpenses();
  }, [loadExpenses]);

  const handlePress = useCallback((expense: FixedExpense) => {
    Alert.alert('상세 정보', `${expense.name}\n${JSON.stringify(expense, null, 2)}`);
  }, []);

  const handleDelete = useCallback(
    async (expense: FixedExpense) => {
      try {
        await deleteFixedExpense(expense.id);
        Alert.alert('성공', `"${expense.name}" 항목이 삭제되었습니다`);
        loadExpenses();
      } catch (err) {
        const errorMessage =
          err instanceof Error ? err.message : '삭제에 실패했습니다';
        Alert.alert('오류', errorMessage);
      }
    },
    [loadExpenses]
  );

  const handleAddExpense = useCallback(() => {
    Alert.alert('준비 중', '고정지출 추가 기능이 곧 제공됩니다');
  }, []);

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
      <View style={styles.header}>
        <Text style={styles.title}>고정지출 관리</Text>
        <TouchableOpacity
          style={styles.addButton}
          onPress={handleAddExpense}
        >
          <Text style={styles.addButtonText}>+ 추가</Text>
        </TouchableOpacity>
      </View>

      {expenses.length === 0 ? (
        <Card style={styles.emptyCard}>
          <Text style={styles.emptyText}>
            등록된 고정지출이 없습니다
          </Text>
          <Text style={styles.emptySubtext}>
            "+" 버튼을 눌러 고정지출을 추가하세요
          </Text>
        </Card>
      ) : (
        <FlatList
          data={expenses}
          keyExtractor={(item) => item.id.toString()}
          renderItem={({ item }) => (
            <FixedExpenseItem
              expense={item}
              onPress={handlePress}
              onDelete={handleDelete}
            />
          )}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              colors={[colors.primary]}
            />
          }
          contentContainerStyle={styles.listContent}
        />
      )}
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
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  title: {
    ...typography.title,
    color: colors.text,
  },
  addButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: 8,
  },
  addButtonText: {
    ...typography.button,
    color: colors.white,
  },
  listContent: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl * 3,
  },
  expenseCard: {
    marginBottom: spacing.md,
  },
  expenseContent: {
    padding: spacing.md,
  },
  expenseHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  expenseName: {
    ...typography.heading,
    color: colors.text,
    flex: 1,
  },
  inactiveBadge: {
    ...typography.caption,
    color: colors.white,
    backgroundColor: colors.error,
    paddingHorizontal: spacing.sm,
    paddingVertical: 2,
    borderRadius: 4,
  },
  expenseDetails: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.xs,
  },
  expenseAmount: {
    ...typography.subheading,
    color: colors.primary,
    fontWeight: '600',
  },
  expenseDay: {
    ...typography.caption,
    color: colors.textSecondary,
  },
  validityInfo: {
    marginTop: spacing.xs,
    paddingTop: spacing.xs,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  validityText: {
    ...typography.caption,
    color: colors.textSecondary,
  },
  deleteButton: {
    backgroundColor: colors.error,
    padding: spacing.sm,
    alignItems: 'center',
    borderBottomLeftRadius: 8,
    borderBottomRightRadius: 8,
  },
  deleteButtonText: {
    ...typography.button,
    color: colors.white,
  },
  emptyCard: {
    alignItems: 'center',
    paddingVertical: spacing.xl * 2,
  },
  emptyText: {
    ...typography.subheading,
    color: colors.text,
    marginBottom: spacing.sm,
  },
  emptySubtext: {
    ...typography.body,
    color: colors.textSecondary,
  },
});
