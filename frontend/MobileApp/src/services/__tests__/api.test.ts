import api from '../api';
import AsyncStorage from '../storage';
import { Platform } from 'react-native';

jest.mock('../storage');
jest.mock('react-native', () => ({
  Platform: {
    OS: 'web',
  },
}));

describe('API', () => {
  beforeEach(() => {
    jest.clearAllMocks();
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
});
