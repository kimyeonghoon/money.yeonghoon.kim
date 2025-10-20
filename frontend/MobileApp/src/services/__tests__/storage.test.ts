// Mock @react-native-async-storage/async-storage
const mockAsyncStorage = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  multiRemove: jest.fn(),
  clear: jest.fn(),
  getAllKeys: jest.fn(),
};

jest.mock('@react-native-async-storage/async-storage', () => ({
  default: mockAsyncStorage,
}));

describe('Storage', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('AsyncStorage - Basic Methods', () => {
    it('AsyncStorage가 정의되어 있어야 함', () => {
      const AsyncStorage = require('../storage').default;
      expect(AsyncStorage).toBeDefined();
    });

    it('setItem 메서드가 있어야 함', () => {
      const AsyncStorage = require('../storage').default;
      expect(typeof AsyncStorage.setItem).toBe('function');
    });

    it('getItem 메서드가 있어야 함', () => {
      const AsyncStorage = require('../storage').default;
      expect(typeof AsyncStorage.getItem).toBe('function');
    });

    it('removeItem 메서드가 있어야 함', () => {
      const AsyncStorage = require('../storage').default;
      expect(typeof AsyncStorage.removeItem).toBe('function');
    });

    it('multiRemove 메서드가 있어야 함', () => {
      const AsyncStorage = require('../storage').default;
      expect(typeof AsyncStorage.multiRemove).toBe('function');
    });
  });

  describe('AsyncStorage - Storage Operations', () => {
    let AsyncStorage: any;

    beforeEach(() => {
      AsyncStorage = require('../storage').default;
    });

    describe('getItem', () => {
      it('값이 존재할 때 정상적으로 반환해야 함', async () => {
        // Given: getItem이 값을 반환하도록 모킹
        mockAsyncStorage.getItem.mockResolvedValue('testValue');

        // When: getItem 호출
        const result = await AsyncStorage.getItem('testKey');

        // Then: 저장된 값 반환
        expect(result).toBe('testValue');
        expect(mockAsyncStorage.getItem).toHaveBeenCalledWith('testKey');
      });

      it('값이 없을 때 null을 반환해야 함', async () => {
        // Given: getItem이 null을 반환하도록 모킹
        mockAsyncStorage.getItem.mockResolvedValue(null);

        // When: 존재하지 않는 키로 getItem 호출
        const result = await AsyncStorage.getItem('nonexistentKey');

        // Then: null 반환
        expect(result).toBeNull();
        expect(mockAsyncStorage.getItem).toHaveBeenCalledWith('nonexistentKey');
      });

      it('에러 발생 시 처리되어야 함', async () => {
        // Given: getItem이 에러를 던지도록 모킹
        const mockError = new Error('Storage error');
        mockAsyncStorage.getItem.mockRejectedValue(mockError);

        // When & Then: getItem 호출 시 에러가 전파됨
        await expect(AsyncStorage.getItem('testKey')).rejects.toThrow('Storage error');
        expect(mockAsyncStorage.getItem).toHaveBeenCalledWith('testKey');
      });
    });

    describe('setItem', () => {
      it('정상적으로 값을 저장해야 함', async () => {
        // Given: setItem이 성공하도록 모킹
        mockAsyncStorage.setItem.mockResolvedValue(undefined);

        // When: setItem 호출
        await AsyncStorage.setItem('testKey', 'testValue');

        // Then: setItem이 호출됨
        expect(mockAsyncStorage.setItem).toHaveBeenCalledWith('testKey', 'testValue');
      });

      it('에러 발생 시 처리되어야 함', async () => {
        // Given: setItem이 에러를 던지도록 모킹
        const mockError = new Error('Storage quota exceeded');
        mockAsyncStorage.setItem.mockRejectedValue(mockError);

        // When & Then: setItem 호출 시 에러가 전파됨
        await expect(AsyncStorage.setItem('testKey', 'testValue')).rejects.toThrow('Storage quota exceeded');
        expect(mockAsyncStorage.setItem).toHaveBeenCalledWith('testKey', 'testValue');
      });
    });

    describe('removeItem', () => {
      it('정상적으로 값을 삭제해야 함', async () => {
        // Given: removeItem이 성공하도록 모킹
        mockAsyncStorage.removeItem.mockResolvedValue(undefined);

        // When: removeItem 호출
        await AsyncStorage.removeItem('testKey');

        // Then: removeItem이 호출됨
        expect(mockAsyncStorage.removeItem).toHaveBeenCalledWith('testKey');
      });

      it('에러 발생 시 처리되어야 함', async () => {
        // Given: removeItem이 에러를 던지도록 모킹
        const mockError = new Error('Storage error');
        mockAsyncStorage.removeItem.mockRejectedValue(mockError);

        // When & Then: removeItem 호출 시 에러가 전파됨
        await expect(AsyncStorage.removeItem('testKey')).rejects.toThrow('Storage error');
        expect(mockAsyncStorage.removeItem).toHaveBeenCalledWith('testKey');
      });
    });

    describe('multiRemove', () => {
      it('여러 키를 동시에 삭제해야 함', async () => {
        // Given: multiRemove가 성공하도록 모킹
        mockAsyncStorage.multiRemove.mockResolvedValue(undefined);

        // When: multiRemove 호출
        await AsyncStorage.multiRemove(['key1', 'key2', 'key3']);

        // Then: multiRemove가 호출됨
        expect(mockAsyncStorage.multiRemove).toHaveBeenCalledWith(['key1', 'key2', 'key3']);
      });

      it('빈 배열을 전달해도 에러가 발생하지 않아야 함', async () => {
        // Given: multiRemove가 성공하도록 모킹
        mockAsyncStorage.multiRemove.mockResolvedValue(undefined);

        // When: multiRemove 호출
        await AsyncStorage.multiRemove([]);

        // Then: 에러 없이 호출됨
        expect(mockAsyncStorage.multiRemove).toHaveBeenCalledWith([]);
      });

      it('에러 발생 시 처리되어야 함', async () => {
        // Given: multiRemove가 에러를 던지도록 모킹
        const mockError = new Error('Storage error');
        mockAsyncStorage.multiRemove.mockRejectedValue(mockError);

        // When & Then: multiRemove 호출 시 에러가 전파됨
        await expect(AsyncStorage.multiRemove(['key1'])).rejects.toThrow('Storage error');
        expect(mockAsyncStorage.multiRemove).toHaveBeenCalledWith(['key1']);
      });
    });
  });

  describe('Web Platform (localStorage wrapper)', () => {
    let webAsyncStorage: any;

    beforeAll(() => {
      // localStorage 모킹
      class LocalStorageMock {
        private store: Map<string, string>;

        constructor() {
          this.store = new Map();
        }

        clear() {
          this.store.clear();
        }

        getItem(key: string) {
          return this.store.get(key) || null;
        }

        setItem(key: string, value: string) {
          this.store.set(key, value);
        }

        removeItem(key: string) {
          this.store.delete(key);
        }
      }

      global.localStorage = new LocalStorageMock() as any;

      // Platform.OS를 'web'으로 설정하고 모듈 재로드
      jest.resetModules();
      jest.doMock('react-native/Libraries/Utilities/Platform', () => ({
        OS: 'web',
        select: jest.fn(),
      }));

      // storage 모듈을 web 플랫폼으로 로드
      webAsyncStorage = require('../storage').default;
    });

    beforeEach(() => {
      localStorage.clear();
    });

    describe('getItem (Web)', () => {
      it('localStorage에서 값을 가져와야 함', async () => {
        // Given: localStorage에 값 저장
        localStorage.setItem('testKey', 'testValue');

        // When: getItem 호출
        const result = await webAsyncStorage.getItem('testKey');

        // Then: 저장된 값 반환
        expect(result).toBe('testValue');
      });

      it('값이 없을 때 null 반환', async () => {
        // Given: 빈 localStorage

        // When: getItem 호출
        const result = await webAsyncStorage.getItem('nonexistent');

        // Then: null 반환
        expect(result).toBeNull();
      });

      it('에러 발생 시 null 반환 및 로그', async () => {
        // Given: localStorage.getItem이 에러를 던지도록 모킹
        const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation();
        const originalGetItem = localStorage.getItem;
        const mockError = new Error('Storage error');
        localStorage.getItem = jest.fn(() => {
          throw mockError;
        });

        // When: getItem 호출
        const result = await webAsyncStorage.getItem('testKey');

        // Then: null 반환 및 에러 로깅
        expect(result).toBeNull();
        expect(consoleErrorSpy).toHaveBeenCalledWith('Storage getItem error:', mockError);

        // 복원
        localStorage.getItem = originalGetItem;
        consoleErrorSpy.mockRestore();
      });
    });

    describe('setItem (Web)', () => {
      it('localStorage에 값을 저장해야 함', async () => {
        // Given: 저장할 데이터

        // When: setItem 호출
        await webAsyncStorage.setItem('testKey', 'testValue');

        // Then: localStorage에 저장됨
        expect(localStorage.getItem('testKey')).toBe('testValue');
      });

      it('에러 발생 시 로그 기록', async () => {
        // Given: localStorage.setItem이 에러를 던지도록 모킹
        const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation();
        const originalSetItem = localStorage.setItem;
        const mockError = new Error('Storage quota exceeded');
        localStorage.setItem = jest.fn(() => {
          throw mockError;
        });

        // When: setItem 호출
        await webAsyncStorage.setItem('testKey', 'testValue');

        // Then: 에러 로깅
        expect(consoleErrorSpy).toHaveBeenCalledWith('Storage setItem error:', mockError);

        // 복원
        localStorage.setItem = originalSetItem;
        consoleErrorSpy.mockRestore();
      });
    });

    describe('removeItem (Web)', () => {
      it('localStorage에서 값을 삭제해야 함', async () => {
        // Given: localStorage에 값 저장
        localStorage.setItem('testKey', 'testValue');

        // When: removeItem 호출
        await webAsyncStorage.removeItem('testKey');

        // Then: localStorage에서 삭제됨
        expect(localStorage.getItem('testKey')).toBeNull();
      });

      it('에러 발생 시 로그 기록', async () => {
        // Given: localStorage.removeItem이 에러를 던지도록 모킹
        const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation();
        const originalRemoveItem = localStorage.removeItem;
        const mockError = new Error('Storage error');
        localStorage.removeItem = jest.fn(() => {
          throw mockError;
        });

        // When: removeItem 호출
        await webAsyncStorage.removeItem('testKey');

        // Then: 에러 로깅
        expect(consoleErrorSpy).toHaveBeenCalledWith('Storage removeItem error:', mockError);

        // 복원
        localStorage.removeItem = originalRemoveItem;
        consoleErrorSpy.mockRestore();
      });
    });

    describe('multiRemove (Web)', () => {
      it('localStorage에서 여러 키를 삭제해야 함', async () => {
        // Given: localStorage에 여러 값 저장
        localStorage.setItem('key1', 'value1');
        localStorage.setItem('key2', 'value2');
        localStorage.setItem('key3', 'value3');

        // When: multiRemove 호출
        await webAsyncStorage.multiRemove(['key1', 'key2', 'key3']);

        // Then: 모든 키가 삭제됨
        expect(localStorage.getItem('key1')).toBeNull();
        expect(localStorage.getItem('key2')).toBeNull();
        expect(localStorage.getItem('key3')).toBeNull();
      });

      it('빈 배열도 처리 가능', async () => {
        // Given: 빈 배열

        // When: multiRemove 호출
        await webAsyncStorage.multiRemove([]);

        // Then: 에러 없음 (아무 동작도 하지 않음)
        expect(true).toBe(true);
      });

      it('에러 발생 시 로그 기록', async () => {
        // Given: localStorage.removeItem이 에러를 던지도록 모킹
        const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation();
        const originalRemoveItem = localStorage.removeItem;
        const mockError = new Error('Storage error');
        localStorage.removeItem = jest.fn(() => {
          throw mockError;
        });

        // When: multiRemove 호출
        await webAsyncStorage.multiRemove(['key1']);

        // Then: 에러 로깅
        expect(consoleErrorSpy).toHaveBeenCalledWith('Storage multiRemove error:', mockError);

        // 복원
        localStorage.removeItem = originalRemoveItem;
        consoleErrorSpy.mockRestore();
      });
    });
  });
});
