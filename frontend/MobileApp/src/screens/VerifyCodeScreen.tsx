import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { useAuth } from '../contexts/AuthContext';

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
    <View style={styles.container}>
      <View style={styles.form}>
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
            <Text style={styles.expiredText}>코드가 만료되었습니다. 다시 시도하세요.</Text>
          )}
        </View>

        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleVerify}
          disabled={loading || timeRemaining === 0}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.buttonText}>인증하기</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.backButton}
          onPress={handleBack}
          disabled={loading}
        >
          <Text style={styles.backButtonText}>로그인으로 돌아가기</Text>
        </TouchableOpacity>

        <Text style={styles.infoText}>
          인증 코드는 5분간 유효합니다
        </Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
    padding: 20,
  },
  form: {
    width: '100%',
    maxWidth: 400,
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 24,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    marginBottom: 8,
    textAlign: 'center',
    color: '#333',
  },
  subtitle: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
    textAlign: 'center',
  },
  username: {
    fontSize: 14,
    color: '#007AFF',
    marginBottom: 24,
    textAlign: 'center',
    fontWeight: '600',
  },
  codeInput: {
    borderWidth: 2,
    borderColor: '#007AFF',
    borderRadius: 8,
    padding: 16,
    fontSize: 32,
    textAlign: 'center',
    letterSpacing: 8,
    fontWeight: 'bold',
    marginBottom: 16,
  },
  timerContainer: {
    alignItems: 'center',
    marginBottom: 16,
  },
  timerText: {
    fontSize: 16,
    color: '#666',
    fontWeight: '600',
  },
  expiredText: {
    fontSize: 14,
    color: '#FF3B30',
    marginTop: 4,
  },
  button: {
    backgroundColor: '#007AFF',
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
    marginTop: 8,
  },
  buttonDisabled: {
    backgroundColor: '#999',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  backButton: {
    marginTop: 16,
    padding: 12,
    alignItems: 'center',
  },
  backButtonText: {
    color: '#007AFF',
    fontSize: 14,
    fontWeight: '600',
  },
  infoText: {
    marginTop: 8,
    fontSize: 12,
    color: '#666',
    textAlign: 'center',
  },
});
