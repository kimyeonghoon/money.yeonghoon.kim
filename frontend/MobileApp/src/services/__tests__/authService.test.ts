import authService from '../authService';
import api from '../api';
import AsyncStorage from '../storage';
import { User } from '../../types/auth';

jest.mock('../api');
jest.mock('../storage');

const mockUser: User = {
  id: 1,
  username: 'testuser',
  email: 'test@example.com',
  full_name: 'Test User',
  is_active: true,
  created_at: '2025-01-01T00:00:00Z',
};

describe('AuthService', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('requestLogin', () => {
    it('로그인 요청을 성공적으로 처리해야 함', async () => {
      const credentials = { username: 'testuser', password: 'password123' };
      const mockResponse = { message: 'Code sent' };

      (api.post as jest.Mock).mockResolvedValue({ data: mockResponse });

      const result = await authService.requestLogin(credentials);

      expect(api.post).toHaveBeenCalledWith(
        '/api/v1/auth/request-login',
        credentials
      );
      expect(result).toEqual(mockResponse);
    });

    it('로그인 요청 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Login failed');
      (api.post as jest.Mock).mockRejectedValue(error);

      await expect(
        authService.requestLogin({
          username: 'testuser',
          password: 'wrong',
        })
      ).rejects.toThrow('로그인 중 오류가 발생했습니다');

      expect(consoleError).toHaveBeenCalledWith('Request login error:', error);

      consoleError.mockRestore();
    });
  });

  describe('verifyLogin', () => {
    it('인증 성공 시 토큰을 저장하고 사용자 정보를 반환해야 함', async () => {
      const verifyData = { username: 'testuser', code: '123456' };
      const tokenResponse = {
        access_token: 'access_token_123',
        refresh_token: 'refresh_token_123',
      };

      (api.post as jest.Mock).mockResolvedValue({ data: tokenResponse });
      (api.get as jest.Mock).mockResolvedValue({ data: mockUser });
      (AsyncStorage.setItem as jest.Mock).mockResolvedValue(undefined);

      const result = await authService.verifyLogin(verifyData);

      expect(api.post).toHaveBeenCalledWith(
        '/api/v1/auth/verify-login',
        verifyData
      );
      expect(AsyncStorage.setItem).toHaveBeenCalledWith(
        'access_token',
        'access_token_123'
      );
      expect(AsyncStorage.setItem).toHaveBeenCalledWith(
        'refresh_token',
        'refresh_token_123'
      );
      expect(api.get).toHaveBeenCalledWith('/api/v1/users/me');
      expect(AsyncStorage.setItem).toHaveBeenCalledWith(
        'user',
        JSON.stringify(mockUser)
      );
      expect(result).toEqual(mockUser);
    });

    it('인증 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Invalid code');
      (api.post as jest.Mock).mockRejectedValue(error);

      await expect(
        authService.verifyLogin({ username: 'testuser', code: 'wrong' })
      ).rejects.toThrow('인증 중 오류가 발생했습니다');

      expect(consoleError).toHaveBeenCalledWith('Verify login error:', error);

      consoleError.mockRestore();
    });
  });

  describe('register', () => {
    it('회원가입을 성공적으로 처리해야 함', async () => {
      const registerData = {
        username: 'newuser',
        email: 'new@example.com',
        password: 'password123',
        full_name: 'New User',
      };

      (api.post as jest.Mock).mockResolvedValue({ data: mockUser });

      const result = await authService.register(registerData);

      expect(api.post).toHaveBeenCalledWith(
        '/api/v1/auth/register',
        registerData
      );
      expect(result).toEqual(mockUser);
    });

    it('회원가입 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Registration failed');
      (api.post as jest.Mock).mockRejectedValue(error);

      await expect(
        authService.register({
          username: 'newuser',
          email: 'new@example.com',
          password: 'password123',
          full_name: 'New User',
        })
      ).rejects.toThrow('Registration failed');

      expect(consoleError).toHaveBeenCalledWith('Register error:', error);

      consoleError.mockRestore();
    });
  });

  describe('logout', () => {
    it('로그아웃 시 모든 토큰과 사용자 정보를 삭제해야 함', async () => {
      (AsyncStorage.multiRemove as jest.Mock).mockResolvedValue(undefined);

      await authService.logout();

      expect(AsyncStorage.multiRemove).toHaveBeenCalledWith([
        'access_token',
        'refresh_token',
        'user',
      ]);
    });

    it('로그아웃 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Logout failed');
      (AsyncStorage.multiRemove as jest.Mock).mockRejectedValue(error);

      await expect(authService.logout()).rejects.toThrow('Logout failed');

      expect(consoleError).toHaveBeenCalledWith('Logout error:', error);

      consoleError.mockRestore();
    });
  });

  describe('getCurrentUser', () => {
    it('현재 사용자 정보를 조회해야 함', async () => {
      (api.get as jest.Mock).mockResolvedValue({ data: mockUser });

      const result = await authService.getCurrentUser();

      expect(api.get).toHaveBeenCalledWith('/api/v1/users/me');
      expect(result).toEqual(mockUser);
    });

    it('사용자 조회 실패 시 에러를 throw해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      const error = new Error('Unauthorized');
      (api.get as jest.Mock).mockRejectedValue(error);

      await expect(authService.getCurrentUser()).rejects.toThrow(
        'Unauthorized'
      );

      expect(consoleError).toHaveBeenCalledWith(
        'Get current user error:',
        error
      );

      consoleError.mockRestore();
    });
  });

  describe('getStoredUser', () => {
    it('저장된 사용자 정보를 조회해야 함', async () => {
      (AsyncStorage.getItem as jest.Mock).mockResolvedValue(
        JSON.stringify(mockUser)
      );

      const result = await authService.getStoredUser();

      expect(AsyncStorage.getItem).toHaveBeenCalledWith('user');
      expect(result).toEqual(mockUser);
    });

    it('저장된 사용자가 없으면 null을 반환해야 함', async () => {
      (AsyncStorage.getItem as jest.Mock).mockResolvedValue(null);

      const result = await authService.getStoredUser();

      expect(result).toBeNull();
    });

    it('조회 실패 시 null을 반환해야 함', async () => {
      const consoleError = jest.spyOn(console, 'error').mockImplementation();
      (AsyncStorage.getItem as jest.Mock).mockRejectedValue(
        new Error('Storage error')
      );

      const result = await authService.getStoredUser();

      expect(result).toBeNull();
      expect(consoleError).toHaveBeenCalled();

      consoleError.mockRestore();
    });
  });

  describe('hasToken', () => {
    it('토큰이 있으면 true를 반환해야 함', async () => {
      (AsyncStorage.getItem as jest.Mock).mockResolvedValue('token_123');

      const result = await authService.hasToken();

      expect(AsyncStorage.getItem).toHaveBeenCalledWith('access_token');
      expect(result).toBe(true);
    });

    it('토큰이 없으면 false를 반환해야 함', async () => {
      (AsyncStorage.getItem as jest.Mock).mockResolvedValue(null);

      const result = await authService.hasToken();

      expect(result).toBe(false);
    });
  });
});
