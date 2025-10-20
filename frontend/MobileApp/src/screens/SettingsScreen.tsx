import React from 'react';
import { Text, View, StyleSheet, Alert } from 'react-native';
import { Screen, Card, Button } from '../components';
import { useAuth } from '../contexts/AuthContext';
import { typography, spacing, colors } from '../theme';

export const SettingsScreen: React.FC = () => {
  const { user, logout } = useAuth();

  const handleLogout = async (): Promise<void> => {
    try {
      await logout();
    } catch (error) {
      console.error('Logout error:', error);
      Alert.alert('오류', '로그아웃 중 오류가 발생했습니다');
    }
  };

  return (
    <Screen centered>
      <Card>
        <Text style={styles.title}>설정</Text>

        {user && (
          <View style={styles.userInfo}>
            <View style={styles.infoRow}>
              <Text style={styles.label}>사용자명:</Text>
              <Text style={styles.value}>{user.username}</Text>
            </View>

            <View style={styles.infoRow}>
              <Text style={styles.label}>이메일:</Text>
              <Text style={styles.value}>{user.email}</Text>
            </View>

            {user.full_name && (
              <View style={styles.infoRow}>
                <Text style={styles.label}>이름:</Text>
                <Text style={styles.value}>{user.full_name}</Text>
              </View>
            )}

            <View style={styles.infoRow}>
              <Text style={styles.label}>상태:</Text>
              <Text style={[styles.value, styles.statusActive]}>
                {user.is_active ? '활성' : '비활성'}
              </Text>
            </View>
          </View>
        )}

        <Button
          title="로그아웃"
          onPress={handleLogout}
          variant="danger"
          style={styles.logoutButton}
        />
      </Card>
    </Screen>
  );
};

const styles = StyleSheet.create({
  title: {
    ...typography.title,
    marginBottom: spacing.xl,
    textAlign: 'center',
  },
  userInfo: {
    marginBottom: spacing.xl,
  },
  infoRow: {
    marginBottom: spacing.md,
  },
  label: {
    ...typography.label,
    marginBottom: spacing.xs,
  },
  value: {
    ...typography.body,
  },
  statusActive: {
    color: colors.success,
    fontWeight: '600',
  },
  logoutButton: {
    marginTop: spacing.sm,
  },
});
