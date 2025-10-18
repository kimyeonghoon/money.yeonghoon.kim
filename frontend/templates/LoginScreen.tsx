import React, { useState, useCallback } from 'react';
import { View, StyleSheet, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { TextInput, Button, Title, Snackbar, HelperText } from 'react-native-paper';
import { useAuth } from '../contexts/AuthContext';

const LoginScreen: React.FC = () => {
  const { login } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  const handleLogin = useCallback(async () => {
    // 입력 검증
    if (!username.trim() || !password.trim()) {
      setError('사용자명과 비밀번호를 입력해주세요.');
      return;
    }

    try {
      setLoading(true);
      setError('');
      await login({ username: username.trim(), password });
      // 로그인 성공 시 네비게이션은 AuthContext에서 자동으로 처리됨
    } catch (err: unknown) {
      console.error('Login error:', err);
      const errorMessage =
        (err as any)?.response?.data?.detail ||
        '로그인에 실패했습니다. 다시 시도해주세요.';
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  }, [username, password, login]);

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View style={styles.content}>
          <Title style={styles.title}>로그인</Title>

          <TextInput
            label="사용자명 또는 이메일"
            value={username}
            onChangeText={setUsername}
            mode="outlined"
            autoCapitalize="none"
            autoCorrect={false}
            disabled={loading}
            style={styles.input}
          />

          <TextInput
            label="비밀번호"
            value={password}
            onChangeText={setPassword}
            mode="outlined"
            secureTextEntry={!showPassword}
            autoCapitalize="none"
            disabled={loading}
            style={styles.input}
            right={
              <TextInput.Icon
                icon={showPassword ? 'eye-off' : 'eye'}
                onPress={() => setShowPassword(!showPassword)}
              />
            }
          />

          {error ? (
            <HelperText type="error" visible={!!error}>
              {error}
            </HelperText>
          ) : null}

          <Button
            mode="contained"
            onPress={handleLogin}
            loading={loading}
            disabled={loading}
            style={styles.button}
          >
            로그인
          </Button>

          <Button
            mode="text"
            onPress={() => {
              // 회원가입 화면으로 이동
              console.log('Navigate to register screen');
            }}
            disabled={loading}
            style={styles.registerButton}
          >
            계정이 없으신가요? 회원가입
          </Button>
        </View>
      </ScrollView>

      <Snackbar
        visible={!!error}
        onDismiss={() => setError('')}
        duration={3000}
        action={{
          label: '닫기',
          onPress: () => setError(''),
        }}
      >
        {error}
      </Snackbar>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
  },
  content: {
    padding: 20,
    maxWidth: 400,
    width: '100%',
    alignSelf: 'center',
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    marginBottom: 30,
    textAlign: 'center',
  },
  input: {
    marginBottom: 15,
  },
  button: {
    marginTop: 10,
    paddingVertical: 5,
  },
  registerButton: {
    marginTop: 10,
  },
});

export default LoginScreen;
