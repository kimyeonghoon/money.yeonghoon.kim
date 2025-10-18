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
  async requestLogin(credentials: LoginRequest): Promise<LoginRequestResponse> {
    try {
      const response = await api.post<LoginRequestResponse>(
        '/api/v1/auth/request-login',
        credentials
      );
      return response.data;
    } catch (error) {
      console.error('Request login error:', error);
      throw error;
    }
  }

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
    } catch (error) {
      console.error('Verify login error:', error);
      throw error;
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
