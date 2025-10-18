import api from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';

export interface LoginCredentials {
  username: string;
  password: string;
}

export interface RegisterData {
  email: string;
  username: string;
  password: string;
  full_name?: string;
}

export interface User {
  id: number;
  email: string;
  username: string;
  full_name?: string;
  is_active: boolean;
  created_at: string;
}

export interface AuthResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
}

class AuthService {
  /**
   * 사용자 로그인
   */
  async login(credentials: LoginCredentials): Promise<User> {
    try {
      const response = await api.post<AuthResponse>('/api/v1/auth/login', credentials);
      const { access_token, refresh_token } = response.data;

      // 토큰 저장
      await AsyncStorage.setItem('access_token', access_token);
      await AsyncStorage.setItem('refresh_token', refresh_token);

      // 사용자 정보 가져오기
      const user = await this.getCurrentUser();
      await AsyncStorage.setItem('user', JSON.stringify(user));

      return user;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  /**
   * 사용자 회원가입
   */
  async register(data: RegisterData): Promise<User> {
    try {
      const response = await api.post<User>('/api/v1/auth/register', data);
      return response.data;
    } catch (error) {
      console.error('Register error:', error);
      throw error;
    }
  }

  /**
   * 로그아웃
   */
  async logout(): Promise<void> {
    try {
      await AsyncStorage.multiRemove(['access_token', 'refresh_token', 'user']);
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  }

  /**
   * 현재 사용자 정보 가져오기
   */
  async getCurrentUser(): Promise<User> {
    try {
      const response = await api.get<User>('/api/v1/users/me');
      return response.data;
    } catch (error) {
      console.error('Get current user error:', error);
      throw error;
    }
  }

  /**
   * 저장된 사용자 정보 가져오기
   */
  async getStoredUser(): Promise<User | null> {
    try {
      const userJson = await AsyncStorage.getItem('user');
      return userJson ? JSON.parse(userJson) : null;
    } catch (error) {
      console.error('Get stored user error:', error);
      return null;
    }
  }

  /**
   * 토큰 존재 여부 확인
   */
  async hasToken(): Promise<boolean> {
    const token = await AsyncStorage.getItem('access_token');
    return token !== null;
  }

  /**
   * 사용자 정보 업데이트
   */
  async updateProfile(data: Partial<User>): Promise<User> {
    try {
      const response = await api.put<User>('/api/v1/users/me', data);
      const user = response.data;
      await AsyncStorage.setItem('user', JSON.stringify(user));
      return user;
    } catch (error) {
      console.error('Update profile error:', error);
      throw error;
    }
  }
}

export default new AuthService();
