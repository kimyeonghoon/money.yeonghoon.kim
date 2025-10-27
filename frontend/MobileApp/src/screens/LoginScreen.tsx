import React, { useState } from 'react';
import { Text, TextInput, StyleSheet } from 'react-native';
import { Screen, Card, Button, Toast } from '../components';
import { useAuth } from '../contexts/AuthContext';
import { typography, spacing, colors, borderRadius } from '../theme';

export const LoginScreen: React.FC = () => {
  const { requestLogin } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  const handleLogin = async (): Promise<void> => {
    if (!username || !password) {
      setToast({ message: '사용자명과 비밀번호를 입력하세요', type: 'error' });
      return;
    }

    setLoading(true);
    try {
      await requestLogin({ username, password });
    } catch (error) {
      const errorMessage =
        error instanceof Error ? error.message : '로그인 실패';
      setToast({ message: errorMessage, type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Screen centered>
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          onHide={() => setToast(null)}
        />
      )}
      <Card>
        <Text style={styles.title}>로그인</Text>
        <Text style={styles.subtitle}>계정 정보를 입력하세요</Text>

        <TextInput
          style={styles.input}
          placeholder="사용자명 또는 이메일"
          value={username}
          onChangeText={setUsername}
          autoCapitalize="none"
          editable={!loading}
        />

        <TextInput
          style={styles.input}
          placeholder="비밀번호"
          value={password}
          onChangeText={setPassword}
          secureTextEntry
          editable={!loading}
        />

        <Button
          title="로그인"
          onPress={handleLogin}
          loading={loading}
          style={styles.button}
        />

        <Text style={styles.infoText}>
          로그인 후 텔레그램으로 6자리 인증 코드가 발송됩니다
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
    marginBottom: spacing.xl,
    textAlign: 'center',
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: borderRadius.small,
    padding: spacing.md,
    marginBottom: spacing.lg,
    fontSize: 16,
    backgroundColor: colors.cardBackground,
  },
  button: {
    marginTop: spacing.sm,
  },
  infoText: {
    ...typography.caption,
    marginTop: spacing.lg,
    textAlign: 'center',
  },
});
