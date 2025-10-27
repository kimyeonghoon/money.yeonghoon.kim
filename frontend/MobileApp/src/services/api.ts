import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import AsyncStorage from './storage';
import { Platform } from 'react-native';

const API_TIMEOUT_MS = 15000;

let onUnauthorizedCallback: (() => void) | null = null;

/**
 * 401 에러 시 호출될 콜백 등록
 *
 * @param callback - 로그아웃 콜백 함수
 */
export const setOnUnauthorized = (callback: (() => void) | null): void => {
  onUnauthorizedCallback = callback;
};

/**
 * 플랫폼에 따른 API 기본 URL 반환
 *
 * @returns API 기본 URL
 */
const getApiBaseUrl = (): string => {
  if (Platform.OS === 'web') {
    return 'http://localhost:8000';
  }
  if (Platform.OS === 'android') {
    return 'http://192.168.200.181:8000';
  }
  return 'http://localhost:8000';
};

/**
 * Axios API 클라이언트
 *
 * Features:
 * - 자동 JWT 토큰 주입 (Authorization 헤더)
 * - 401 에러 시 리프레시 토큰으로 자동 갱신
 * - 플랫폼별 API URL 자동 선택
 * - 타임아웃: 15초
 */
const api = axios.create({
  baseURL: getApiBaseUrl(),
  timeout: API_TIMEOUT_MS,
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    const token = await AsyncStorage.getItem('access_token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error: AxiosError) => {
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const refreshToken = await AsyncStorage.getItem('refresh_token');
        if (refreshToken) {
          const response = await axios.post<{ access_token: string }>(
            `${getApiBaseUrl()}/api/v1/auth/refresh`,
            { refresh_token: refreshToken }
          );

          const { access_token } = response.data;
          await AsyncStorage.setItem('access_token', access_token);

          if (originalRequest.headers) {
            originalRequest.headers.Authorization = `Bearer ${access_token}`;
          }
          return api(originalRequest);
        } else {
          await AsyncStorage.multiRemove(['access_token', 'refresh_token', 'user']);
          if (onUnauthorizedCallback) {
            onUnauthorizedCallback();
          }
        }
      } catch (refreshError) {
        await AsyncStorage.multiRemove(['access_token', 'refresh_token', 'user']);
        if (onUnauthorizedCallback) {
          onUnauthorizedCallback();
        }
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default api;
