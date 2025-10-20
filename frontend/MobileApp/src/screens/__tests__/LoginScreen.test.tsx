import React from 'react';
import { render, fireEvent, waitFor } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { LoginScreen } from '../LoginScreen';
import { AuthContext } from '../../contexts/AuthContext';

jest.spyOn(Alert, 'alert');

const mockRequestLogin = jest.fn();

const mockAuthContext = {
  user: null,
  loading: false,
  authStep: 'login' as const,
  username: null,
  requestLogin: mockRequestLogin,
  verifyLogin: jest.fn(),
  register: jest.fn(),
  logout: jest.fn(),
  setAuthStep: jest.fn(),
};

describe('LoginScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('기본 렌더링', () => {
    it('로그인 화면을 표시해야 함', () => {
      const { getAllByText, getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      expect(getAllByText('로그인').length).toBeGreaterThan(0);
      expect(getByText('계정 정보를 입력하세요')).toBeTruthy();
      expect(getByPlaceholderText('사용자명 또는 이메일')).toBeTruthy();
      expect(getByPlaceholderText('비밀번호')).toBeTruthy();
      expect(getByText('로그인 후 텔레그램으로 6자리 인증 코드가 발송됩니다')).toBeTruthy();
    });
  });

  describe('입력 필드', () => {
    it('사용자명 입력이 가능해야 함', () => {
      const { getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const usernameInput = getByPlaceholderText('사용자명 또는 이메일');
      fireEvent.changeText(usernameInput, 'testuser');

      expect(usernameInput.props.value).toBe('testuser');
    });

    it('비밀번호 입력이 가능해야 함', () => {
      const { getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const passwordInput = getByPlaceholderText('비밀번호');
      fireEvent.changeText(passwordInput, 'password123');

      expect(passwordInput.props.value).toBe('password123');
      expect(passwordInput.props.secureTextEntry).toBe(true);
    });
  });

  describe('로그인 버튼', () => {
    it('사용자명과 비밀번호가 입력되면 로그인을 시도해야 함', async () => {
      mockRequestLogin.mockResolvedValue(undefined);

      const { getAllByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const usernameInput = getByPlaceholderText('사용자명 또는 이메일');
      const passwordInput = getByPlaceholderText('비밀번호');
      const loginButtons = getAllByText('로그인');
      const loginButton = loginButtons[loginButtons.length - 1];

      fireEvent.changeText(usernameInput, 'testuser');
      fireEvent.changeText(passwordInput, 'password123');
      fireEvent.press(loginButton);

      await waitFor(() => {
        expect(mockRequestLogin).toHaveBeenCalledWith({
          username: 'testuser',
          password: 'password123',
        });
      });
    });

    it('사용자명이 비어있으면 Alert를 표시해야 함', () => {
      const { getAllByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const passwordInput = getByPlaceholderText('비밀번호');
      const loginButtons = getAllByText('로그인');
      const loginButton = loginButtons[loginButtons.length - 1];

      fireEvent.changeText(passwordInput, 'password123');
      fireEvent.press(loginButton);

      expect(Alert.alert).toHaveBeenCalledWith(
        '오류',
        '사용자명과 비밀번호를 입력하세요'
      );
      expect(mockRequestLogin).not.toHaveBeenCalled();
    });

    it('비밀번호가 비어있으면 Alert를 표시해야 함', () => {
      const { getAllByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const usernameInput = getByPlaceholderText('사용자명 또는 이메일');
      const loginButtons = getAllByText('로그인');
      const loginButton = loginButtons[loginButtons.length - 1];

      fireEvent.changeText(usernameInput, 'testuser');
      fireEvent.press(loginButton);

      expect(Alert.alert).toHaveBeenCalledWith(
        '오류',
        '사용자명과 비밀번호를 입력하세요'
      );
      expect(mockRequestLogin).not.toHaveBeenCalled();
    });

    it('로그인 실패 시 에러 Alert를 표시해야 함', async () => {
      const error = new Error('Invalid credentials');
      mockRequestLogin.mockRejectedValue(error);

      const { getAllByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const usernameInput = getByPlaceholderText('사용자명 또는 이메일');
      const passwordInput = getByPlaceholderText('비밀번호');
      const loginButtons = getAllByText('로그인');
      const loginButton = loginButtons[loginButtons.length - 1];

      fireEvent.changeText(usernameInput, 'testuser');
      fireEvent.changeText(passwordInput, 'wrong');
      fireEvent.press(loginButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '로그인 오류',
          'Invalid credentials'
        );
      });
    });

    it('로그인 중에는 입력 필드가 비활성화되어야 함', async () => {
      let resolveLogin: any;
      mockRequestLogin.mockImplementation(
        () => new Promise((resolve) => (resolveLogin = resolve))
      );

      const { getAllByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const usernameInput = getByPlaceholderText('사용자명 또는 이메일');
      const passwordInput = getByPlaceholderText('비밀번호');
      const loginButtons = getAllByText('로그인');
      const loginButton = loginButtons[loginButtons.length - 1];

      fireEvent.changeText(usernameInput, 'testuser');
      fireEvent.changeText(passwordInput, 'password123');
      fireEvent.press(loginButton);

      await waitFor(() => {
        expect(usernameInput.props.editable).toBe(false);
        expect(passwordInput.props.editable).toBe(false);
      });

      resolveLogin();
    });
  });

  describe('로딩 상태', () => {
    it('로딩 중에는 ActivityIndicator를 표시해야 함', async () => {
      let resolveLogin: any;
      mockRequestLogin.mockImplementation(
        () => new Promise((resolve) => (resolveLogin = resolve))
      );

      const { getAllByText, getByPlaceholderText, UNSAFE_getByType } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <LoginScreen />
        </AuthContext.Provider>
      );

      const usernameInput = getByPlaceholderText('사용자명 또는 이메일');
      const passwordInput = getByPlaceholderText('비밀번호');
      const loginButtons = getAllByText('로그인');
      const loginButton = loginButtons[loginButtons.length - 1];

      fireEvent.changeText(usernameInput, 'testuser');
      fireEvent.changeText(passwordInput, 'password123');
      fireEvent.press(loginButton);

      await waitFor(() => {
        expect(UNSAFE_getByType('ActivityIndicator' as any)).toBeTruthy();
      });

      resolveLogin();
    });
  });
});
