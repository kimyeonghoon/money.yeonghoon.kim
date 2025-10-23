/**
 * EditFixedExpenseScreen 테스트
 *
 * Given-When-Then 패턴으로 작성되었습니다.
 */

import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { EditFixedExpenseScreen } from '../EditFixedExpenseScreen';
import * as fixedExpenseService from '../../services/fixedExpenseService';
import { FixedExpense } from '../../types/fixedExpense';

jest.mock('../../services/fixedExpenseService');

const mockExpense: FixedExpense = {
  id: 1,
  user_id: 1,
  name: '월세',
  default_amount: 800000,
  is_fixed_amount: true,
  expected_payment_day: 5,
  is_active: true,
  valid_from: '2025-01-01',
  valid_until: null,
  created_at: '2025-01-01T00:00:00Z',
  updated_at: null,
};

const mockNavigation = {
  navigate: jest.fn(),
  goBack: jest.fn(),
  setOptions: jest.fn(),
};

const mockRoute = {
  params: {
    expenseId: 1,
  },
};

describe('EditFixedExpenseScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.spyOn(Alert, 'alert').mockImplementation(() => {});
    (fixedExpenseService.getFixedExpense as jest.Mock).mockResolvedValue(mockExpense);
  });

  describe('초기 렌더링', () => {
    it('로딩 중 표시', () => {
      (fixedExpenseService.getFixedExpense as jest.Mock).mockImplementation(
        () => new Promise(() => {})
      );

      const { getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      expect(getByText('로딩 중...')).toBeTruthy();
    });

    it('기존 데이터로 입력 필드 채우기', async () => {
      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByDisplayValue('월세')).toBeTruthy();
        expect(getByDisplayValue('800000')).toBeTruthy();
        expect(getByDisplayValue('5')).toBeTruthy();
      });
    });

    it('데이터 로드 실패 시 에러', async () => {
      const error = new Error('고정지출을 불러오는데 실패했습니다');
      (fixedExpenseService.getFixedExpense as jest.Mock).mockRejectedValue(error);

      render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '오류',
          '고정지출을 불러오는데 실패했습니다'
        );
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });
    });
  });

  describe('입력 필드 수정', () => {
    it('항목명 수정', async () => {
      const { getByDisplayValue } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const nameInput = getByDisplayValue('월세');
        fireEvent.changeText(nameInput, '전세');
        expect(nameInput.props.value).toBe('전세');
      });
    });

    it('금액 수정', async () => {
      const { getByDisplayValue } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const amountInput = getByDisplayValue('800000');
        fireEvent.changeText(amountInput, '900000');
        expect(amountInput.props.value).toBe('900000');
      });
    });

    it('활성 상태 토글', async () => {
      const { getByTestId } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const toggle = getByTestId('active-toggle');
        expect(toggle.props.value).toBe(true);
        fireEvent(toggle, 'valueChange', false);
        expect(toggle.props.value).toBe(false);
      });
    });
  });

  describe('유효성 검증', () => {
    it('항목명 없이 저장 시 에러', async () => {
      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const nameInput = getByDisplayValue('월세');
        fireEvent.changeText(nameInput, '');
      });

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '입력 오류',
          '항목명을 입력해주세요'
        );
      });

      expect(fixedExpenseService.updateFixedExpense).not.toHaveBeenCalled();
    });

    it('지출일이 1-31 범위 밖일 때 에러', async () => {
      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const dayInput = getByDisplayValue('5');
        fireEvent.changeText(dayInput, '32');
      });

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '입력 오류',
          '지출일은 1-31 사이의 숫자여야 합니다'
        );
      });

      expect(fixedExpenseService.updateFixedExpense).not.toHaveBeenCalled();
    });

    it('금액이 음수일 때 에러', async () => {
      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const amountInput = getByDisplayValue('800000');
        fireEvent.changeText(amountInput, '-1000');
      });

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '입력 오류',
          '금액은 0 이상이어야 합니다'
        );
      });

      expect(fixedExpenseService.updateFixedExpense).not.toHaveBeenCalled();
    });
  });

  describe('저장 기능', () => {
    it('수정 성공', async () => {
      const updatedExpense = {
        ...mockExpense,
        name: '전세',
        default_amount: 900000,
      };
      (fixedExpenseService.updateFixedExpense as jest.Mock).mockResolvedValue(updatedExpense);

      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const nameInput = getByDisplayValue('월세');
        fireEvent.changeText(nameInput, '전세');
        const amountInput = getByDisplayValue('800000');
        fireEvent.changeText(amountInput, '900000');
      });

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(fixedExpenseService.updateFixedExpense).toHaveBeenCalledWith(1, {
          name: '전세',
          default_amount: 900000,
          is_fixed_amount: true,
          expected_payment_day: 5,
          is_active: true,
        });
      });

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '성공',
          '고정지출이 수정되었습니다'
        );
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });
    });

    it('활성 상태 변경 저장', async () => {
      const updatedExpense = {
        ...mockExpense,
        is_active: false,
      };
      (fixedExpenseService.updateFixedExpense as jest.Mock).mockResolvedValue(updatedExpense);

      const { getByTestId, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const toggle = getByTestId('active-toggle');
        fireEvent(toggle, 'valueChange', false);
      });

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(fixedExpenseService.updateFixedExpense).toHaveBeenCalledWith(1, {
          name: '월세',
          default_amount: 800000,
          is_fixed_amount: true,
          expected_payment_day: 5,
          is_active: false,
        });
      });

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '성공',
          '고정지출이 수정되었습니다'
        );
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });
    });

    it('금액 제거 (변동 금액으로 변경)', async () => {
      const updatedExpense = {
        ...mockExpense,
        default_amount: null,
        is_fixed_amount: false,
      };
      (fixedExpenseService.updateFixedExpense as jest.Mock).mockResolvedValue(updatedExpense);

      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const amountInput = getByDisplayValue('800000');
        fireEvent.changeText(amountInput, '');
      });

      const saveButton = getByText('저장');
      fireEvent.press(saveButton);

      await waitFor(() => {
        expect(fixedExpenseService.updateFixedExpense).toHaveBeenCalledWith(1, {
          name: '월세',
          default_amount: null,
          is_fixed_amount: true,
          expected_payment_day: 5,
          is_active: true,
        });
      });
    });

    it('저장 실패 시 에러 메시지', async () => {
      const error = new Error('서버 오류가 발생했습니다');
      (fixedExpenseService.updateFixedExpense as jest.Mock).mockRejectedValue(error);

      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const nameInput = getByDisplayValue('월세');
        fireEvent.changeText(nameInput, '전세');
      });

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
    it('변경 없이 취소 시 즉시 뒤로 가기', async () => {
      const { getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText('취소')).toBeTruthy();
      });

      const cancelButton = getByText('취소');
      fireEvent.press(cancelButton);

      expect(mockNavigation.goBack).toHaveBeenCalled();
      expect(Alert.alert).not.toHaveBeenCalled();
    });

    it('변경 후 취소 시 확인 대화상자', async () => {
      const { getByDisplayValue, getByText } = render(
        <EditFixedExpenseScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const nameInput = getByDisplayValue('월세');
        fireEvent.changeText(nameInput, '전세');
      });

      const cancelButton = getByText('취소');
      fireEvent.press(cancelButton);

      expect(Alert.alert).toHaveBeenCalledWith(
        '취소 확인',
        '변경한 내용이 저장되지 않습니다. 취소하시겠습니까?',
        expect.any(Array)
      );
    });
  });
});
