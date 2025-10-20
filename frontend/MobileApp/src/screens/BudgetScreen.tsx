import React from 'react';
import { Text, StyleSheet } from 'react-native';
import { Screen, Card } from '../components';
import { typography, spacing } from '../theme';

export const BudgetScreen: React.FC = () => {
  return (
    <Screen centered>
      <Card>
        <Text style={styles.title}>예산 관리</Text>
        <Text style={styles.subtitle}>
          월별 예산을 설정하고 관리하는 화면입니다
        </Text>
      </Card>
    </Screen>
  );
};

const styles = StyleSheet.create({
  title: {
    ...typography.title,
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  subtitle: {
    ...typography.subtitle,
    textAlign: 'center',
  },
});
