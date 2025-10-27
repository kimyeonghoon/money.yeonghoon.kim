import React, { createContext, useState, useContext, useEffect, useCallback, ReactNode } from 'react';
import authService from '../services/authService';
import { User, LoginRequest, VerifyLoginRequest, RegisterData } from '../types/auth';
import { setOnUnauthorized } from '../services/api';

type AuthStep = 'login' | 'verify' | 'authenticated';

interface AuthContextData {
  user: User | null;
  authStep: AuthStep;
  username: string | null;
  loading: boolean;
  requestLogin: (credentials: LoginRequest) => Promise<void>;
  verifyLogin: (code: string) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
  setAuthStep: (step: AuthStep) => void;
}

export const AuthContext = createContext<AuthContextData | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [authStep, setAuthStep] = useState<AuthStep>('login');
  const [username, setUsername] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStoredUser();
  }, [loadStoredUser]);

  /**
   * 토큰 만료 시 자동 로그아웃 콜백 등록
   */
  useEffect(() => {
    const handleUnauthorized = (): void => {
      setUser(null);
      setAuthStep('login');
      setUsername(null);
    };

    setOnUnauthorized(handleUnauthorized);

    return () => {
      setOnUnauthorized(null);
    };
  }, []);

  /**
   * 저장된 사용자 정보 로드
   */
  const loadStoredUser = useCallback(async (): Promise<void> => {
    try {
      const hasToken = await authService.hasToken();
      if (hasToken) {
        const storedUser = await authService.getStoredUser();
        if (storedUser) {
          setUser(storedUser);
          setAuthStep('authenticated');
        }
      }
    } catch (error) {
      console.error('Load stored user error:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  /**
   * 로그인 요청 (2FA 1단계)
   *
   * @param credentials - 사용자명/이메일 및 비밀번호
   */
  const requestLogin = useCallback(async (credentials: LoginRequest): Promise<void> => {
    try {
      await authService.requestLogin(credentials);
      setUsername(credentials.username);
      setAuthStep('verify');
    } catch (error) {
      console.error('Request login error:', error);
      throw error;
    }
  }, []);

  /**
   * 로그인 검증 (2FA 2단계)
   *
   * @param code - 6자리 인증 코드
   */
  const verifyLogin = useCallback(async (code: string): Promise<void> => {
    try {
      if (!username) {
        throw new Error('Username not found');
      }

      const verifyData: VerifyLoginRequest = {
        username,
        code,
      };

      const loggedInUser = await authService.verifyLogin(verifyData);
      setUser(loggedInUser);
      setAuthStep('authenticated');
      setUsername(null);
    } catch (error) {
      console.error('Verify login error:', error);
      throw error;
    }
  }, [username]);

  /**
   * 사용자 등록
   *
   * @param data - 등록 데이터
   */
  const register = useCallback(async (data: RegisterData): Promise<void> => {
    try {
      await authService.register(data);
    } catch (error) {
      console.error('Register error:', error);
      throw error;
    }
  }, []);

  /**
   * 로그아웃
   */
  const logout = useCallback(async (): Promise<void> => {
    try {
      await authService.logout();
      setUser(null);
      setAuthStep('login');
      setUsername(null);
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  }, []);

  return (
    <AuthContext.Provider
      value={{
        user,
        authStep,
        username,
        loading,
        requestLogin,
        verifyLogin,
        register,
        logout,
        setAuthStep,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextData => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
