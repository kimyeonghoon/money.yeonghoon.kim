/**
 * AddFixedExpenseScreen 테스트
 *
 * Given-When-Then 패턴으로 작성되었습니다.
 */

import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { AddFixedExpenseScreen } from '../AddFixedExpenseScreen';
import * as fixedExpenseService from '../../services/fixedExpenseService';
import { FixedExpenseCreateRequest } from '../../types/fixedExpense';

jest.mock('../../services/fixedExpenseService');

const mockNavigation = {
  navigate: jest.fn(),
  goBack: jest.fn(),
  setOptions: jest.fn(),
};

describe('AddFixedExpenseScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.spyOn(Alert, 'alert').mockImplementation(() => {});
  });

  describe('초기 렌더링', () => {
    it('모든 입력 필드가 표시됨', () => {
      const { getByPlaceholderText, getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      expect(getByPlaceholderText('항목명 (예: 월세, 전기세)')).toBeTruthy();
      expect(getByPlaceholderText('금액 (선택사항)')).toBeTruthy();
      expect(getByPlaceholderText('매월 지출일 (1-31)')).toBeTruthy();
      expect(getByText('고정 금액')).toBeTruthy();
      expect(getByText('저장')).toBeTruthy();
      expect(getByText('취소')).toBeTruthy();
    });

    it('초기 값이 비어있음', () => {
      const { getByPlaceholderText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      const amountInput = getByPlaceholderText('금액 (선택사항)');
      const dayInput = getByPlaceholderText('매월 지출일 (1-31)');

      expect(nameInput.props.value).toBe('');
      expect(amountInput.props.value).toBe('');
      expect(dayInput.props.value).toBe('');
    });
  });

  describe('입력 필드 변경', () => {
    it('항목명 입력', () => {
      const { getByPlaceholderText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      fireEvent.changeText(nameInput, '월세');

      expect(nameInput.props.value).toBe('월세');
    });

    it('금액 입력', () => {
      const { getByPlaceholderText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const amountInput = getByPlaceholderText('금액 (선택사항)');
      fireEvent.changeText(amountInput, '800000');

      expect(amountInput.props.value).toBe('800000');
    });

    it('지출일 입력', () => {
      const { getByPlaceholderText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const dayInput = getByPlaceholderText('매월 지출일 (1-31)');
      fireEvent.changeText(dayInput, '5');

      expect(dayInput.props.value).toBe('5');
    });

    it('고정 금액 토글', () => {
      const { getByTestId } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const toggle = getByTestId('fixed-amount-toggle');
      fireEvent(toggle, 'valueChange', true);

      expect(toggle.props.value).toBe(true);
    });
  });

  describe('유효성 검증', () => {
    it('항목명 없이 저장 시 에러', async () => {
      const { getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '입력 오류',
          '항목명을 입력해주세요'
        );
      });

      expect(fixedExpenseService.createFixedExpense).not.toHaveBeenCalled();
    });

    it('지출일이 1-31 범위 밖일 때 에러', async () => {
      const { getByPlaceholderText, getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      const dayInput = getByPlaceholderText('매월 지출일 (1-31)');

      fireEvent.changeText(nameInput, '월세');
      fireEvent.changeText(dayInput, '32');

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '입력 오류',
          '지출일은 1-31 사이의 숫자여야 합니다'
        );
      });

      expect(fixedExpenseService.createFixedExpense).not.toHaveBeenCalled();
    });

    it('금액이 음수일 때 에러', async () => {
      const { getByPlaceholderText, getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      const amountInput = getByPlaceholderText('금액 (선택사항)');

      fireEvent.changeText(nameInput, '월세');
      fireEvent.changeText(amountInput, '-1000');

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '입력 오류',
          '금액은 0 이상이어야 합니다'
        );
      });

      expect(fixedExpenseService.createFixedExpense).not.toHaveBeenCalled();
    });
  });

  describe('저장 기능', () => {
    it('최소 정보로 저장 성공 (항목명만)', async () => {
      (fixedExpenseService.createFixedExpense as jest.Mock).mockResolvedValue({
        id: 1,
        user_id: 1,
        name: '월세',
        default_amount: null,
        is_fixed_amount: false,
        expected_payment_day: null,
        is_active: true,
        valid_from: '2025-10-24',
        valid_until: null,
        created_at: '2025-10-24T00:00:00Z',
        updated_at: null,
      });

      const { getByPlaceholderText, getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      fireEvent.changeText(nameInput, '월세');

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(fixedExpenseService.createFixedExpense).toHaveBeenCalledWith({
          name: '월세',
          default_amount: null,
          is_fixed_amount: false,
          expected_payment_day: null,
        });
      });

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '성공',
          '고정지출이 추가되었습니다'
        );
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });
    });

    it('모든 정보로 저장 성공', async () => {
      (fixedExpenseService.createFixedExpense as jest.Mock).mockResolvedValue({
        id: 1,
        user_id: 1,
        name: '월세',
        default_amount: 800000,
        is_fixed_amount: true,
        expected_payment_day: 5,
        is_active: true,
        valid_from: '2025-10-24',
        valid_until: null,
        created_at: '2025-10-24T00:00:00Z',
        updated_at: null,
      });

      const { getByPlaceholderText, getByText, getByTestId } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      const amountInput = getByPlaceholderText('금액 (선택사항)');
      const dayInput = getByPlaceholderText('매월 지출일 (1-31)');
      const toggle = getByTestId('fixed-amount-toggle');

      fireEvent.changeText(nameInput, '월세');
      fireEvent.changeText(amountInput, '800000');
      fireEvent.changeText(dayInput, '5');
      fireEvent(toggle, 'valueChange', true);

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(fixedExpenseService.createFixedExpense).toHaveBeenCalledWith({
          name: '월세',
          default_amount: 800000,
          is_fixed_amount: true,
          expected_payment_day: 5,
        });
      });

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '성공',
          '고정지출이 추가되었습니다'
        );
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });
    });

    it('저장 중 로딩 표시', async () => {
      let resolvePromise: () => void;
      const promise = new Promise<any>((resolve) => {
        resolvePromise = () => resolve({
          id: 1,
          user_id: 1,
          name: '월세',
          default_amount: null,
          is_fixed_amount: false,
          expected_payment_day: null,
          is_active: true,
          valid_from: '2025-10-24',
          valid_until: null,
          created_at: '2025-10-24T00:00:00Z',
          updated_at: null,
        });
      });

      (fixedExpenseService.createFixedExpense as jest.Mock).mockReturnValue(promise);

      const { getByPlaceholderText, getByText, queryByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      fireEvent.changeText(nameInput, '월세');

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      expect(fixedExpenseService.createFixedExpense).toHaveBeenCalled();

      resolvePromise!();
      await waitFor(() => {
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });
    });

    it('저장 실패 시 에러 메시지', async () => {
      const error = new Error('서버 오류가 발생했습니다');
      (fixedExpenseService.createFixedExpense as jest.Mock).mockRejectedValue(
        error
      );

      const { getByPlaceholderText, getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      fireEvent.changeText(nameInput, '월세');

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '오류',
          '서버 오류가 발생했습니다'
        );
        expect(mockNavigation.goBack).not.toHaveBeenCalled();
      });
    });
  });

  describe('취소 기능', () => {
    it('취소 버튼 클릭 시 뒤로 가기', () => {
      const { getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const cancelButton = getByText('취소');
      fireEvent.press(cancelButton);

      expect(mockNavigation.goBack).toHaveBeenCalled();
    });

    it('입력 중 취소 시 확인 대화상자', () => {
      const { getByPlaceholderText, getByText } = render(
        <AddFixedExpenseScreen navigation={mockNavigation as any} />
      );

      const nameInput = getByPlaceholderText('항목명 (예: 월세, 전기세)');
      fireEvent.changeText(nameInput, '월세');

      const cancelButton = getByText('취소');
      fireEvent.press(cancelButton);

      expect(Alert.alert).toHaveBeenCalledWith(
        '취소 확인',
        '입력한 내용이 저장되지 않습니다. 취소하시겠습니까?',
        expect.any(Array)
      );
    });
  });
});
