import api, { setOnUnauthorized } from '../api';
import AsyncStorage from '../storage';
import { Platform } from 'react-native';
import axios from 'axios';

jest.mock('../storage');
jest.mock('react-native', () => ({
  Platform: {
    OS: 'web',
  },
}));

describe('API', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.spyOn(axios, 'post').mockClear();
  });

  describe('getApiBaseURL', () => {
    it('web 플랫폼에서는 localhost:8000을 반환해야 함', () => {
      Platform.OS = 'web';
      expect(api.defaults.baseURL).toContain('localhost:8000');
    });
  });

  describe('Request Interceptor', () => {
    it('토큰이 있으면 Authorization 헤더를 추가해야 함', async () => {
      (AsyncStorage.getItem as jest.Mock).mockResolvedValue('test_token');

      const config: any = {
        headers: {},
      };

      const interceptor = api.interceptors.request.handlers[0];
      const result = await (interceptor as any).fulfilled(config);

      expect(AsyncStorage.getItem).toHaveBeenCalledWith('access_token');
      expect(result.headers.Authorization).toBe('Bearer test_token');
    });

    it('토큰이 없으면 Authorization 헤더를 추가하지 않아야 함', async () => {
      (AsyncStorage.getItem as jest.Mock).mockResolvedValue(null);

      const config: any = {
        headers: {},
      };

      const interceptor = api.interceptors.request.handlers[0];
      const result = await (interceptor as any).fulfilled(config);

      expect(result.headers.Authorization).toBeUndefined();
    });
  });

  describe('Response Interceptor - 자동 로그아웃', () => {
    it('리프레시 토큰이 없고 401 에러 시 로그아웃 콜백을 호출해야 함', async () => {
      const onUnauthorizedMock = jest.fn();
      setOnUnauthorized(onUnauthorizedMock);

      (AsyncStorage.getItem as jest.Mock).mockResolvedValue(null);

      const error: any = {
        response: { status: 401 },
        config: { headers: {} },
      };

      const interceptor = api.interceptors.response.handlers[0];

      await expect((interceptor as any).rejected(error)).rejects.toEqual(error);
      expect(onUnauthorizedMock).toHaveBeenCalled();
      expect(AsyncStorage.multiRemove).toHaveBeenCalledWith([
        'access_token',
        'refresh_token',
        'user',
      ]);
    });

    it('리프레시 토큰 갱신 실패 시 로그아웃 콜백을 호출해야 함', async () => {
      const onUnauthorizedMock = jest.fn();
      setOnUnauthorized(onUnauthorizedMock);

      (AsyncStorage.getItem as jest.Mock).mockResolvedValue('refresh_token');
      (axios.post as jest.Mock).mockRejectedValue(new Error('Refresh failed'));

      const error: any = {
        response: { status: 401 },
        config: { headers: {} },
      };

      const interceptor = api.interceptors.response.handlers[0];

      await expect((interceptor as any).rejected(error)).rejects.toThrow('Refresh failed');
      expect(onUnauthorizedMock).toHaveBeenCalled();
      expect(AsyncStorage.multiRemove).toHaveBeenCalledWith([
        'access_token',
        'refresh_token',
        'user',
      ]);
    });

    it('리프레시 토큰 갱신 성공 시 로그아웃 콜백을 호출하지 않아야 함', async () => {
      const onUnauthorizedMock = jest.fn();
      setOnUnauthorized(onUnauthorizedMock);

      (AsyncStorage.getItem as jest.Mock)
        .mockResolvedValueOnce('old_access_token')
        .mockResolvedValueOnce('refresh_token');
      (axios.post as jest.Mock).mockResolvedValue({
        data: { access_token: 'new_access_token' },
      });

      const error: any = {
        response: { status: 401 },
        config: { headers: {} },
      };

      const interceptor = api.interceptors.response.handlers[0];

      try {
        await (interceptor as any).rejected(error);
      } catch (e) {
        // Ignore
      }

      expect(onUnauthorizedMock).not.toHaveBeenCalled();
    });
  });
});
