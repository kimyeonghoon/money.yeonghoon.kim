import React from 'react';
import { render, fireEvent, waitFor } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { SettingsScreen } from '../SettingsScreen';
import { AuthContext } from '../../contexts/AuthContext';

jest.spyOn(Alert, 'alert');

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

describe('SettingsScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('기본 렌더링', () => {
    it('설정 제목을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(getByText('설정')).toBeTruthy();
    });

    it('사용자 정보를 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(getByText('사용자명:')).toBeTruthy();
      expect(getByText('testuser')).toBeTruthy();
      expect(getByText('이메일:')).toBeTruthy();
      expect(getByText('test@example.com')).toBeTruthy();
    });

    it('전체 이름을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(getByText('이름:')).toBeTruthy();
      expect(getByText('Test User')).toBeTruthy();
    });

    it('활성 상태를 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(getByText('상태:')).toBeTruthy();
      expect(getByText('활성')).toBeTruthy();
    });

    it('로그아웃 버튼을 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(getByText('로그아웃')).toBeTruthy();
    });
  });

  describe('full_name이 없는 경우', () => {
    it('full_name이 null일 때 이름 필드를 표시하지 않아야 함', () => {
      const userWithoutFullName = { ...mockUser, full_name: null };
      const contextWithoutFullName = {
        ...mockAuthContext,
        user: userWithoutFullName,
      };

      const { queryByText } = render(
        <AuthContext.Provider value={contextWithoutFullName}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(queryByText('이름:')).toBeNull();
    });
  });

  describe('로그아웃 기능', () => {
    it('로그아웃 버튼 클릭 시 logout 함수를 호출해야 함', async () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      const logoutButton = getByText('로그아웃');
      fireEvent.press(logoutButton);

      await waitFor(() => {
        expect(mockAuthContext.logout).toHaveBeenCalledTimes(1);
      });
    });

    it('로그아웃 실패 시 에러 Alert를 표시해야 함', async () => {
      const mockLogoutError = jest.fn().mockRejectedValue(new Error('Logout failed'));
      const contextWithError = {
        ...mockAuthContext,
        logout: mockLogoutError,
      };

      const { getByText } = render(
        <AuthContext.Provider value={contextWithError}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      const logoutButton = getByText('로그아웃');
      fireEvent.press(logoutButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '오류',
          '로그아웃 중 오류가 발생했습니다'
        );
      });
    });
  });

  describe('사용자 없음', () => {
    it('user가 null일 때 로그아웃 버튼만 표시해야 함', () => {
      const contextWithoutUser = {
        ...mockAuthContext,
        user: null,
      };

      const { getByText, queryByText } = render(
        <AuthContext.Provider value={contextWithoutUser}>
          <SettingsScreen />
        </AuthContext.Provider>
      );

      expect(getByText('설정')).toBeTruthy();
      expect(getByText('로그아웃')).toBeTruthy();
      expect(queryByText('사용자명:')).toBeNull();
    });
  });
});
