import React, { createContext, useState, useContext, useEffect, ReactNode } from 'react';
import authService from '../services/authService';
import { User, LoginRequest, VerifyLoginRequest, RegisterData } from '../types/auth';

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
  }, []);

  const loadStoredUser = async (): Promise<void> => {
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
  };

  const requestLogin = async (credentials: LoginRequest): Promise<void> => {
    try {
      await authService.requestLogin(credentials);
      setUsername(credentials.username);
      setAuthStep('verify');
    } catch (error) {
      console.error('Request login error:', error);
      throw error;
    }
  };

  const verifyLogin = async (code: string): Promise<void> => {
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
  };

  const register = async (data: RegisterData): Promise<void> => {
    try {
      await authService.register(data);
    } catch (error) {
      console.error('Register error:', error);
      throw error;
    }
  };

  const logout = async (): Promise<void> => {
    try {
      await authService.logout();
      setUser(null);
      setAuthStep('login');
      setUsername(null);
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  };

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
