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
      Alert.alert('Error', 'Please enter a 6-digit code');
      return;
    }

    setLoading(true);
    try {
      await verifyLogin(code);
    } catch (error) {
      const errorMessage =
        error instanceof Error ? error.message : 'Verification failed';
      Alert.alert('Verification Error', errorMessage);
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
        <Text style={styles.title}>Enter Verification Code</Text>
        <Text style={styles.subtitle}>
          We sent a 6-digit code to your Telegram
        </Text>
        {username && (
          <Text style={styles.username}>Username: {username}</Text>
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
            Time remaining: {formatTime(timeRemaining)}
          </Text>
          {timeRemaining === 0 && (
            <Text style={styles.expiredText}>Code expired. Please try again.</Text>
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
            <Text style={styles.buttonText}>Verify</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.backButton}
          onPress={handleBack}
          disabled={loading}
        >
          <Text style={styles.backButtonText}>Back to Login</Text>
        </TouchableOpacity>

        <Text style={styles.infoText}>
          The code is valid for 5 minutes
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
