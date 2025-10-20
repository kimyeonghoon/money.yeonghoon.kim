import React from 'react';
import { render } from '@testing-library/react-native';
import { HomeScreen } from '../HomeScreen';
import { AuthContext } from '../../contexts/AuthContext';

const mockUser = {
  id: 1,
  username: 'testuser',
  email: 'test@example.com',
  full_name: 'Test User',
  is_active: true,
  created_at: '2025-01-01T00:00:00Z',
};

const mockAuthContext = {
  user: mockUser,
  loading: false,
  authStep: 'authenticated' as const,
  username: 'testuser',
  requestLogin: jest.fn(),
  verifyLogin: jest.fn(),
  logout: jest.fn(),
  setAuthStep: jest.fn(),
};

describe('HomeScreen', () => {
  describe('로그인된 사용자', () => {
    it('환영 메시지를 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('환영합니다!')).toBeTruthy();
    });

    it('사용자 이름을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('testuser님')).toBeTruthy();
    });

    it('MoneyWallet 설명을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('MoneyWallet에서 당신의 재정을 관리하세요')).toBeTruthy();
    });
  });

  describe('이번 달 요약', () => {
    it('이번 달 요약 섹션을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('이번 달 요약')).toBeTruthy();
    });

    it('총 수입을 표시해야 함', () => {
      const { getByText, getAllByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('총 수입')).toBeTruthy();
      expect(getAllByText('₩0').length).toBeGreaterThan(0);
    });

    it('총 지출을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('총 지출')).toBeTruthy();
    });

    it('잔액을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('잔액')).toBeTruthy();
    });
  });

  describe('빠른 작업', () => {
    it('빠른 작업 섹션을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('빠른 작업')).toBeTruthy();
    });

    it('거래 추가 기능 안내를 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('거래 추가 기능이 곧 제공됩니다')).toBeTruthy();
    });
  });

  describe('사용자 없음', () => {
    it('user가 null일 때도 렌더링되어야 함', () => {
      const contextWithoutUser = {
        ...mockAuthContext,
        user: null,
      };

      const { getByText } = render(
        <AuthContext.Provider value={contextWithoutUser}>
          <HomeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('환영합니다!')).toBeTruthy();
    });
  });
});
