import React from 'react';
import { render, fireEvent, waitFor, act } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { VerifyCodeScreen } from '../VerifyCodeScreen';
import { AuthContext } from '../../contexts/AuthContext';

jest.spyOn(Alert, 'alert');
jest.useFakeTimers();

const mockVerifyLogin = jest.fn();
const mockSetAuthStep = jest.fn();

const mockAuthContext = {
  user: null,
  loading: false,
  authStep: 'verify' as const,
  username: 'testuser',
  requestLogin: jest.fn(),
  verifyLogin: mockVerifyLogin,
  register: jest.fn(),
  logout: jest.fn(),
  setAuthStep: mockSetAuthStep,
};

describe('VerifyCodeScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  afterAll(() => {
    jest.useRealTimers();
  });

  describe('기본 렌더링', () => {
    it('인증 코드 입력 화면을 표시해야 함', () => {
      const { getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('인증 코드 입력')).toBeTruthy();
      expect(getByText('텔레그램으로 6자리 코드를 발송했습니다')).toBeTruthy();
      expect(getByText('사용자명: testuser')).toBeTruthy();
      expect(getByPlaceholderText('000000')).toBeTruthy();
      expect(getByText('인증 코드는 5분간 유효합니다')).toBeTruthy();
    });

    it('username이 있으면 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('사용자명: testuser')).toBeTruthy();
    });

    it('username이 없으면 표시하지 않아야 함', () => {
      const contextWithoutUsername = {
        ...mockAuthContext,
        username: null,
      };

      const { queryByText } = render(
        <AuthContext.Provider value={contextWithoutUsername}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      expect(queryByText(/사용자명:/)).toBeNull();
    });
  });

  describe('코드 입력', () => {
    it('숫자만 입력되어야 함', () => {
      const { getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');

      fireEvent.changeText(codeInput, '123abc456');

      expect(codeInput.props.value).toBe('123456');
    });

    it('최대 6자리까지만 입력되어야 함', () => {
      const { getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');

      expect(codeInput.props.maxLength).toBe(6);
    });
  });

  describe('타이머', () => {
    it('5분(300초)부터 카운트다운해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      expect(getByText('남은 시간: 5:00')).toBeTruthy();
    });

    it('시간이 경과하면 타이머가 감소해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      act(() => {
        jest.advanceTimersByTime(1000);
      });

      expect(getByText('남은 시간: 4:59')).toBeTruthy();

      act(() => {
        jest.advanceTimersByTime(59000);
      });

      expect(getByText('남은 시간: 4:00')).toBeTruthy();
    });

    it('시간이 만료되면 만료 메시지를 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      act(() => {
        jest.advanceTimersByTime(300000);
      });

      expect(getByText('남은 시간: 0:00')).toBeTruthy();
      expect(getByText('코드가 만료되었습니다. 다시 시도하세요.')).toBeTruthy();
    });
  });

  describe('인증하기 버튼', () => {
    it('6자리 코드가 입력되면 인증을 시도해야 함', async () => {
      mockVerifyLogin.mockResolvedValue(undefined);

      const { getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');
      const verifyButton = getByText('인증하기');

      fireEvent.changeText(codeInput, '123456');
      fireEvent.press(verifyButton);

      await waitFor(() => {
        expect(mockVerifyLogin).toHaveBeenCalledWith('123456');
      });
    });

    it('코드가 비어있으면 Alert를 표시해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const verifyButton = getByText('인증하기');
      fireEvent.press(verifyButton);

      expect(Alert.alert).toHaveBeenCalledWith('오류', '6자리 코드를 입력하세요');
      expect(mockVerifyLogin).not.toHaveBeenCalled();
    });

    it('코드가 6자리가 아니면 Alert를 표시해야 함', () => {
      const { getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');
      const verifyButton = getByText('인증하기');

      fireEvent.changeText(codeInput, '123');
      fireEvent.press(verifyButton);

      expect(Alert.alert).toHaveBeenCalledWith('오류', '6자리 코드를 입력하세요');
      expect(mockVerifyLogin).not.toHaveBeenCalled();
    });

    it('인증 실패 시 에러 Alert를 표시해야 함', async () => {
      const error = new Error('Invalid code');
      mockVerifyLogin.mockRejectedValue(error);

      const { getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');
      const verifyButton = getByText('인증하기');

      fireEvent.changeText(codeInput, '123456');
      fireEvent.press(verifyButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith('인증 오류', 'Invalid code');
      });
    });

    it('시간이 만료되면 버튼이 비활성화되어야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      act(() => {
        jest.advanceTimersByTime(300000);
      });

      const verifyButton = getByText('인증하기');
      fireEvent.press(verifyButton);

      expect(mockVerifyLogin).not.toHaveBeenCalled();
    });
  });

  describe('로그인으로 돌아가기 버튼', () => {
    it('클릭 시 login 단계로 이동해야 함', () => {
      const { getByText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const backButton = getByText('로그인으로 돌아가기');
      fireEvent.press(backButton);

      expect(mockSetAuthStep).toHaveBeenCalledWith('login');
    });

    it('로딩 중에는 비활성화되어야 함', async () => {
      let resolveVerify: any;
      mockVerifyLogin.mockImplementation(
        () => new Promise((resolve) => (resolveVerify = resolve))
      );

      const { getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');
      const verifyButton = getByText('인증하기');
      const backButton = getByText('로그인으로 돌아가기');

      fireEvent.changeText(codeInput, '123456');
      fireEvent.press(verifyButton);

      await waitFor(() => {
        fireEvent.press(backButton);
        expect(mockSetAuthStep).not.toHaveBeenCalled();
      });

      resolveVerify();
    });
  });

  describe('로딩 상태', () => {
    it('로딩 중에는 입력 필드가 비활성화되어야 함', async () => {
      let resolveVerify: any;
      mockVerifyLogin.mockImplementation(
        () => new Promise((resolve) => (resolveVerify = resolve))
      );

      const { getByText, getByPlaceholderText } = render(
        <AuthContext.Provider value={mockAuthContext}>
          <VerifyCodeScreen />
        </AuthContext.Provider>
      );

      const codeInput = getByPlaceholderText('000000');
      const verifyButton = getByText('인증하기');

      fireEvent.changeText(codeInput, '123456');
      fireEvent.press(verifyButton);

      await waitFor(() => {
        expect(codeInput.props.editable).toBe(false);
      });

      resolveVerify();
    });
  });
});
