import api from './api';
import AsyncStorage from './storage';
import {
  LoginRequest,
  LoginRequestResponse,
  VerifyLoginRequest,
  TokenResponse,
  User,
  RegisterData,
} from '../types/auth';

class AuthService {
  /**
   * 2FA 로그인 요청
   *
   * @param credentials - 로그인 자격 증명 (username, password)
   * @returns 성공 메시지
   * @throws Error - 인증 실패 시
   */
  async requestLogin(credentials: LoginRequest): Promise<LoginRequestResponse> {
    try {
      const response = await api.post<LoginRequestResponse>(
        '/api/v1/auth/request-login',
        credentials
      );
      return response.data;
    } catch (error: any) {
      console.error('Request login error:', error);
      if (error.response?.status === 401) {
        throw new Error('사용자명 또는 비밀번호가 올바르지 않습니다');
      }
      if (error.response?.status === 500) {
        throw new Error('서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요');
      }
      throw new Error('로그인 중 오류가 발생했습니다');
    }
  }

  /**
   * 2FA 로그인 검증
   *
   * @param data - 검증 데이터 (username, code)
   * @returns 사용자 정보
   * @throws Error - 검증 실패 시
   */
  async verifyLogin(data: VerifyLoginRequest): Promise<User> {
    try {
      const response = await api.post<TokenResponse>(
        '/api/v1/auth/verify-login',
        data
      );
      const { access_token, refresh_token } = response.data;

      await AsyncStorage.setItem('access_token', access_token);
      await AsyncStorage.setItem('refresh_token', refresh_token);

      const user = await this.getCurrentUser();
      await AsyncStorage.setItem('user', JSON.stringify(user));

      return user;
    } catch (error: any) {
      console.error('Verify login error:', error);
      if (error.response?.status === 401) {
        throw new Error('인증 코드가 올바르지 않습니다');
      }
      if (error.response?.status === 500) {
        throw new Error('서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요');
      }
      throw new Error('인증 중 오류가 발생했습니다');
    }
  }

  async register(data: RegisterData): Promise<User> {
    try {
      const response = await api.post<User>('/api/v1/auth/register', data);
      return response.data;
    } catch (error) {
      console.error('Register error:', error);
      throw error;
    }
  }

  async logout(): Promise<void> {
    try {
      await AsyncStorage.multiRemove(['access_token', 'refresh_token', 'user']);
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  }

  async getCurrentUser(): Promise<User> {
    try {
      const response = await api.get<User>('/api/v1/users/me');
      return response.data;
    } catch (error) {
      console.error('Get current user error:', error);
      throw error;
    }
  }

  async getStoredUser(): Promise<User | null> {
    try {
      const userJson = await AsyncStorage.getItem('user');
      return userJson ? JSON.parse(userJson) : null;
    } catch (error) {
      console.error('Get stored user error:', error);
      return null;
    }
  }

  async hasToken(): Promise<boolean> {
    const token = await AsyncStorage.getItem('access_token');
    return token !== null;
  }
}

export default new AuthService();
