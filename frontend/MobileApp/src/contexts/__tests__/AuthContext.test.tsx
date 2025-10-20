import React from 'react';
import { renderHook, act, waitFor } from '@testing-library/react-native';
import { AuthProvider, useAuth, AuthContext } from '../AuthContext';
import authService from '../../services/authService';
import { User } from '../../types/auth';

jest.mock('../../services/authService');

const mockUser: User = {
  id: 1,
  username: 'testuser',
  email: 'test@example.com',
  full_name: 'Test User',
  is_active: true,
  created_at: '2025-01-01T00:00:00Z',
};

describe('AuthContext', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    (authService.hasToken as jest.Mock).mockResolvedValue(false);
    (authService.getStoredUser as jest.Mock).mockResolvedValue(null);
  });

  describe('useAuth hook', () => {
    it('Provider 없이 사용하면 에러를 발생시켜야 함', () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();

      expect(() => {
        renderHook(() => useAuth());
      }).toThrow('useAuth must be used within an AuthProvider');

      consoleError.mockRestore();
    });

    it('Provider 내에서 사용하면 context를 반환해야 함', () => {
      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      expect(result.current).toBeDefined();
      expect(result.current.user).toBeNull();
      expect(result.current.authStep).toBe('login');
    });
  });

  describe('AuthProvider 초기화', () => {
    it('초기 상태가 올바르게 설정되어야 함', async () => {
      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      expect(result.current.user).toBeNull();
      expect(result.current.authStep).toBe('login');
      expect(result.current.username).toBeNull();
    });

    it('저장된 토큰이 있으면 사용자를 로드해야 함', async () => {
      (authService.hasToken as jest.Mock).mockResolvedValue(true);
      (authService.getStoredUser as jest.Mock).mockResolvedValue(mockUser);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      expect(result.current.user).toEqual(mockUser);
      expect(result.current.authStep).toBe('authenticated');
    });

    it('저장된 토큰 로드 실패 시 에러를 처리해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      (authService.hasToken as jest.Mock).mockRejectedValue(
        new Error('Load failed')
      );

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      expect(consoleError).toHaveBeenCalledWith(
        'Load stored user error:',
        expect.any(Error)
      );
      expect(result.current.user).toBeNull();

      consoleError.mockRestore();
    });
  });

  describe('requestLogin', () => {
    it('로그인 요청 성공 시 verify 단계로 이동해야 함', async () => {
      (authService.requestLogin as jest.Mock).mockResolvedValue(undefined);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await act(async () => {
        await result.current.requestLogin({
          username: 'testuser',
          password: 'password',
        });
      });

      expect(authService.requestLogin).toHaveBeenCalledWith({
        username: 'testuser',
        password: 'password',
      });
      expect(result.current.authStep).toBe('verify');
      expect(result.current.username).toBe('testuser');
    });

    it('로그인 요청 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Login failed');
      (authService.requestLogin as jest.Mock).mockRejectedValue(error);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await expect(
        act(async () => {
          await result.current.requestLogin({
            username: 'testuser',
            password: 'wrong',
          });
        })
      ).rejects.toThrow('Login failed');

      expect(consoleError).toHaveBeenCalledWith('Request login error:', error);

      consoleError.mockRestore();
    });
  });

  describe('verifyLogin', () => {
    it('인증 성공 시 사용자를 설정하고 authenticated로 이동해야 함', async () => {
      (authService.requestLogin as jest.Mock).mockResolvedValue(undefined);
      (authService.verifyLogin as jest.Mock).mockResolvedValue(mockUser);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await act(async () => {
        await result.current.requestLogin({
          username: 'testuser',
          password: 'password',
        });
      });

      await act(async () => {
        await result.current.verifyLogin('123456');
      });

      expect(authService.verifyLogin).toHaveBeenCalledWith({
        username: 'testuser',
        code: '123456',
      });
      expect(result.current.user).toEqual(mockUser);
      expect(result.current.authStep).toBe('authenticated');
      expect(result.current.username).toBeNull();
    });

    it('username이 없으면 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await expect(
        act(async () => {
          await result.current.verifyLogin('123456');
        })
      ).rejects.toThrow('Username not found');

      consoleError.mockRestore();
    });

    it('인증 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      (authService.requestLogin as jest.Mock).mockResolvedValue(undefined);
      const error = new Error('Invalid code');
      (authService.verifyLogin as jest.Mock).mockRejectedValue(error);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await act(async () => {
        await result.current.requestLogin({
          username: 'testuser',
          password: 'password',
        });
      });

      await expect(
        act(async () => {
          await result.current.verifyLogin('wrong');
        })
      ).rejects.toThrow('Invalid code');

      expect(consoleError).toHaveBeenCalledWith('Verify login error:', error);

      consoleError.mockRestore();
    });
  });

  describe('register', () => {
    it('회원가입 성공 시 authService.register를 호출해야 함', async () => {
      (authService.register as jest.Mock).mockResolvedValue(undefined);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      const registerData = {
        username: 'newuser',
        email: 'new@example.com',
        password: 'password123',
        full_name: 'New User',
      };

      await act(async () => {
        await result.current.register(registerData);
      });

      expect(authService.register).toHaveBeenCalledWith(registerData);
    });

    it('회원가입 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Registration failed');
      (authService.register as jest.Mock).mockRejectedValue(error);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await expect(
        act(async () => {
          await result.current.register({
            username: 'newuser',
            email: 'new@example.com',
            password: 'password123',
            full_name: 'New User',
          });
        })
      ).rejects.toThrow('Registration failed');

      expect(consoleError).toHaveBeenCalledWith('Register error:', error);

      consoleError.mockRestore();
    });
  });

  describe('logout', () => {
    it('로그아웃 성공 시 상태를 초기화해야 함', async () => {
      (authService.hasToken as jest.Mock).mockResolvedValue(true);
      (authService.getStoredUser as jest.Mock).mockResolvedValue(mockUser);
      (authService.logout as jest.Mock).mockResolvedValue(undefined);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.user).toEqual(mockUser);
      });

      await act(async () => {
        await result.current.logout();
      });

      expect(authService.logout).toHaveBeenCalled();
      expect(result.current.user).toBeNull();
      expect(result.current.authStep).toBe('login');
      expect(result.current.username).toBeNull();
    });

    it('로그아웃 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Logout failed');
      (authService.logout as jest.Mock).mockRejectedValue(error);

      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      await expect(
        act(async () => {
          await result.current.logout();
        })
      ).rejects.toThrow('Logout failed');

      expect(consoleError).toHaveBeenCalledWith('Logout error:', error);

      consoleError.mockRestore();
    });
  });

  describe('setAuthStep', () => {
    it('authStep을 변경할 수 있어야 함', async () => {
      const wrapper = ({ children }: { children: React.ReactNode }) => (
        <AuthProvider>{children}</AuthProvider>
      );

      const { result } = renderHook(() => useAuth(), { wrapper });

      await waitFor(() => {
        expect(result.current.loading).toBe(false);
      });

      expect(result.current.authStep).toBe('login');

      act(() => {
        result.current.setAuthStep('verify');
      });

      expect(result.current.authStep).toBe('verify');

      act(() => {
        result.current.setAuthStep('authenticated');
      });

      expect(result.current.authStep).toBe('authenticated');
    });
  });
});
