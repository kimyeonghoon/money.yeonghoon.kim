import React from 'react';
import { Text, View, StyleSheet } from 'react-native';
import { Screen, Card } from '../components';
import { useAuth } from '../contexts/AuthContext';
import { typography, spacing, colors } from '../theme';

export const HomeScreen: React.FC = () => {
  const { user } = useAuth();

  return (
    <Screen centered>
      <Card>
        <Text style={styles.title}>환영합니다!</Text>
        {user && (
          <Text style={styles.username}>{user.username}님</Text>
        )}
        <Text style={styles.subtitle}>
          MoneyWallet에서 당신의 재정을 관리하세요
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
});
