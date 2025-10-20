import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, StyleSheet, Alert } from 'react-native';
import { Screen, Card, Button } from '../components';
import { useAuth } from '../contexts/AuthContext';
import { typography, spacing, colors, borderRadius } from '../theme';

export const VerifyCodeScreen: React.FC = () => {
  const { verifyLogin, username, setAuthStep } = useAuth();
  const [code, setCode] = useState('');
  const [loading, setLoading] = useState(false);
  const [timeRemaining, setTimeRemaining] = useState(300);

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeRemaining((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  const formatTime = (seconds: number): string => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  const handleVerify = async (): Promise<void> => {
    if (!code || code.length !== 6) {
      Alert.alert('오류', '6자리 코드를 입력하세요');
      return;
    }

    setLoading(true);
    try {
      await verifyLogin(code);
    } catch (error) {
      const errorMessage =
        error instanceof Error ? error.message : '인증 실패';
      Alert.alert('인증 오류', errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const handleBack = (): void => {
    setAuthStep('login');
  };

  return (
    <Screen centered>
      <Card>
        <Text style={styles.title}>인증 코드 입력</Text>
        <Text style={styles.subtitle}>
          텔레그램으로 6자리 코드를 발송했습니다
        </Text>
        {username && (
          <Text style={styles.username}>사용자명: {username}</Text>
        )}

        <TextInput
          style={styles.codeInput}
          placeholder="000000"
          value={code}
          onChangeText={(text) => setCode(text.replace(/[^0-9]/g, ''))}
          keyboardType="number-pad"
          maxLength={6}
          editable={!loading}
          autoFocus
        />

        <View style={styles.timerContainer}>
          <Text style={styles.timerText}>
            남은 시간: {formatTime(timeRemaining)}
          </Text>
          {timeRemaining === 0 && (
            <Text style={styles.expiredText}>
              코드가 만료되었습니다. 다시 시도하세요.
            </Text>
          )}
        </View>

        <Button
          title="인증하기"
          onPress={handleVerify}
          loading={loading}
          disabled={timeRemaining === 0}
          style={styles.verifyButton}
        />

        <Button
          title="로그인으로 돌아가기"
          onPress={handleBack}
          variant="ghost"
          disabled={loading}
          style={styles.backButton}
        />

        <Text style={styles.infoText}>
          인증 코드는 5분간 유효합니다
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
    marginBottom: spacing.sm,
    textAlign: 'center',
  },
  username: {
    ...typography.bodySmall,
    color: colors.primary,
    marginBottom: spacing.xl,
    textAlign: 'center',
    fontWeight: '600',
  },
  codeInput: {
    borderWidth: 2,
    borderColor: colors.borderFocus,
    borderRadius: borderRadius.small,
    padding: spacing.lg,
    fontSize: 32,
    textAlign: 'center',
    letterSpacing: 8,
    fontWeight: 'bold',
    marginBottom: spacing.lg,
  },
  timerContainer: {
    alignItems: 'center',
    marginBottom: spacing.lg,
  },
  timerText: {
    ...typography.body,
    color: colors.textSecondary,
    fontWeight: '600',
  },
  expiredText: {
    ...typography.bodySmall,
    color: colors.danger,
    marginTop: spacing.xs,
  },
  verifyButton: {
    marginTop: spacing.sm,
  },
  backButton: {
    marginTop: spacing.lg,
  },
  infoText: {
    ...typography.caption,
    marginTop: spacing.sm,
    textAlign: 'center',
  },
});
