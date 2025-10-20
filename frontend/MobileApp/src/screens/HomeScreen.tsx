import React from 'react';
import { Text, View, StyleSheet } from 'react-native';
import { Screen, Card } from '../components';
import { useAuth } from '../contexts/AuthContext';
import { typography, spacing, colors } from '../theme';

export const HomeScreen: React.FC = () => {
  const { user } = useAuth();

  return (
    <Screen>
      <Card style={styles.welcomeCard}>
        <Text style={styles.title}>환영합니다!</Text>
        {user && (
          <Text style={styles.username}>{user.username}님</Text>
        )}
        <Text style={styles.subtitle}>
          MoneyWallet에서 당신의 재정을 관리하세요
        </Text>
      </Card>

      <Card style={styles.summaryCard}>
        <Text style={styles.sectionTitle}>이번 달 요약</Text>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>총 수입</Text>
          <Text style={[styles.summaryValue, styles.income]}>
            ₩0
          </Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>총 지출</Text>
          <Text style={[styles.summaryValue, styles.expense]}>
            ₩0
          </Text>
        </View>
        <View style={[styles.summaryRow, styles.totalRow]}>
          <Text style={styles.summaryLabel}>잔액</Text>
          <Text style={styles.summaryValue}>₩0</Text>
        </View>
      </Card>

      <Card style={styles.quickActionsCard}>
        <Text style={styles.sectionTitle}>빠른 작업</Text>
        <Text style={styles.placeholder}>
          거래 추가 기능이 곧 제공됩니다
        </Text>
      </Card>
    </Screen>
  );
};

const styles = StyleSheet.create({
  welcomeCard: {
    marginBottom: spacing.lg,
  },
  title: {
    ...typography.title,
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  username: {
    ...typography.body,
    color: colors.primary,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  subtitle: {
    ...typography.subtitle,
    textAlign: 'center',
  },
  summaryCard: {
    marginBottom: spacing.lg,
  },
  sectionTitle: {
    ...typography.body,
    fontWeight: 'bold',
    marginBottom: spacing.lg,
    color: colors.text,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  totalRow: {
    marginTop: spacing.sm,
    paddingTop: spacing.md,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  summaryLabel: {
    ...typography.body,
    color: colors.textSecondary,
  },
  summaryValue: {
    ...typography.body,
    fontWeight: 'bold',
    color: colors.text,
  },
  income: {
    color: colors.success,
  },
  expense: {
    color: colors.danger,
  },
  quickActionsCard: {
    marginBottom: spacing.lg,
  },
  placeholder: {
    ...typography.bodySmall,
    color: colors.textSecondary,
    textAlign: 'center',
    fontStyle: 'italic',
  },
});
