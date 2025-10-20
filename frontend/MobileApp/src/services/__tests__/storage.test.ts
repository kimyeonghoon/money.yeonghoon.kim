import AsyncStorage from '../storage';

describe('Storage', () => {
  describe('AsyncStorage', () => {
    it('AsyncStorage가 정의되어 있어야 함', () => {
      expect(AsyncStorage).toBeDefined();
    });

    it('setItem 메서드가 있어야 함', () => {
      expect(typeof AsyncStorage.setItem).toBe('function');
    });

    it('getItem 메서드가 있어야 함', () => {
      expect(typeof AsyncStorage.getItem).toBe('function');
    });

    it('removeItem 메서드가 있어야 함', () => {
      expect(typeof AsyncStorage.removeItem).toBe('function');
    });

    it('multiRemove 메서드가 있어야 함', () => {
      expect(typeof AsyncStorage.multiRemove).toBe('function');
    });
  });
});
