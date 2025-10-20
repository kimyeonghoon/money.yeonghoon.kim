import React from 'react';
import { Text, StyleSheet } from 'react-native';
import { Screen, Card } from '../components';
import { typography, spacing } from '../theme';

export const TransactionsScreen: React.FC = () => {
  return (
    <Screen centered>
      <Card>
        <Text style={styles.title}>거래내역</Text>
        <Text style={styles.subtitle}>
          수입과 지출 내역을 관리하는 화면입니다
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
