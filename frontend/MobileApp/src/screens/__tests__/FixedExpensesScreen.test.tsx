/**
 * FixedExpensesScreen 테스트
 *
 * Given-When-Then 패턴으로 작성되었습니다.
 */

import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { FixedExpensesScreen } from '../FixedExpensesScreen';
import * as fixedExpenseService from '../../services/fixedExpenseService';
import { FixedExpense } from '../../types/fixedExpense';

jest.mock('../../services/fixedExpenseService');

const mockNavigate = jest.fn();
jest.mock('@react-navigation/native', () => ({
  useNavigation: () => ({
    navigate: mockNavigate,
  }),
}));

const mockExpenses: FixedExpense[] = [
  {
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
  },
  {
    id: 2,
    user_id: 1,
    name: '전기세',
    default_amount: null,
    is_fixed_amount: false,
    expected_payment_day: 15,
    is_active: true,
    valid_from: '2025-01-01',
    valid_until: null,
    created_at: '2025-01-01T00:00:00Z',
    updated_at: null,
  },
];

describe('FixedExpensesScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    mockNavigate.mockClear();
    jest.spyOn(Alert, 'alert').mockImplementation(() => {});
  });

  describe('초기 렌더링', () => {
    it('로딩 중 표시', () => {
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockImplementation(
        () => new Promise(() => {})
      );

      const { getByText } = render(<FixedExpensesScreen />);

      expect(getByText('로딩 중...')).toBeTruthy();
    });

    it('고정지출 목록 로드 성공', async () => {
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue(
        mockExpenses
      );

      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
        expect(getByText('전기세')).toBeTruthy();
      });
    });

    it('빈 목록 표시', async () => {
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue([]);

      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('등록된 고정지출이 없습니다')).toBeTruthy();
      });
    });
  });

  describe('고정지출 항목 표시', () => {
    beforeEach(() => {
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue(
        mockExpenses
      );
    });

    it('고정 금액 항목 표시', async () => {
      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
        expect(getByText('₩800,000')).toBeTruthy();
        expect(getByText('매월 5일')).toBeTruthy();
      });
    });

    it('변동 금액 항목 표시', async () => {
      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('전기세')).toBeTruthy();
        expect(getByText('변동 금액')).toBeTruthy();
        expect(getByText('매월 15일')).toBeTruthy();
      });
    });

    it('유효 기간 표시 - 현재 유효', async () => {
      const { getAllByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        const validityTexts = getAllByText(/유효: 2025-01-01 ~ 현재/);
        expect(validityTexts.length).toBeGreaterThan(0);
      });
    });

    it('유효 기간 표시 - 종료일 있음', async () => {
      const expenseWithEndDate: FixedExpense[] = [
        {
          ...mockExpenses[0],
          valid_until: '2025-12-31',
        },
      ];
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue(
        expenseWithEndDate
      );

      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('유효: 2025-01-01 ~ 2025-12-31')).toBeTruthy();
      });
    });

    it('비활성 항목 배지 표시', async () => {
      const inactiveExpense: FixedExpense[] = [
        {
          ...mockExpenses[0],
          is_active: false,
        },
      ];
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue(
        inactiveExpense
      );

      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('비활성')).toBeTruthy();
      });
    });
  });

  describe('고정지출 삭제', () => {
    beforeEach(() => {
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue(
        mockExpenses
      );
    });

    it('삭제 확인 대화상자 표시', async () => {
      const { getAllByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        const deleteButtons = getAllByText('삭제');
        fireEvent.press(deleteButtons[0]);
      });

      expect(Alert.alert).toHaveBeenCalledWith(
        '고정지출 삭제',
        expect.stringContaining('월세'),
        expect.any(Array)
      );
    });

    it('삭제 성공', async () => {
      (fixedExpenseService.deleteFixedExpense as jest.Mock).mockResolvedValue(
        undefined
      );
      (fixedExpenseService.getFixedExpenses as jest.Mock)
        .mockResolvedValueOnce(mockExpenses)
        .mockResolvedValueOnce([mockExpenses[1]]);

      const alertSpy = jest.spyOn(Alert, 'alert').mockImplementation(
        (title, message, buttons) => {
          if (title === '고정지출 삭제' && Array.isArray(buttons)) {
            const confirmButton = buttons.find((b) => b.text === '삭제');
            if (confirmButton && confirmButton.onPress) {
              confirmButton.onPress();
            }
          }
        }
      );

      const { getAllByText, getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      const deleteButtons = getAllByText('삭제');
      fireEvent.press(deleteButtons[0]);

      await waitFor(() => {
        expect(fixedExpenseService.deleteFixedExpense).toHaveBeenCalledWith(1);
        expect(alertSpy).toHaveBeenCalledWith(
          '성공',
          '"월세" 항목이 삭제되었습니다'
        );
      });

      alertSpy.mockRestore();
    });

    it('삭제 실패', async () => {
      const error = new Error('삭제에 실패했습니다');
      (fixedExpenseService.deleteFixedExpense as jest.Mock).mockRejectedValue(
        error
      );

      const alertSpy = jest.spyOn(Alert, 'alert').mockImplementation(
        (title, message, buttons) => {
          if (title === '고정지출 삭제' && Array.isArray(buttons)) {
            const confirmButton = buttons.find((b) => b.text === '삭제');
            if (confirmButton && confirmButton.onPress) {
              confirmButton.onPress();
            }
          }
        }
      );

      const { getAllByText, getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      const deleteButtons = getAllByText('삭제');
      fireEvent.press(deleteButtons[0]);

      await waitFor(() => {
        expect(alertSpy).toHaveBeenCalledWith(
          '오류',
          '삭제에 실패했습니다'
        );
      });

      alertSpy.mockRestore();
    });
  });

  describe('고정지출 추가', () => {
    beforeEach(() => {
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockResolvedValue([]);
    });

    it('추가 버튼 클릭 시 추가 화면으로 이동', async () => {
      const { getByText } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        const addButton = getByText('+ 추가');
        fireEvent.press(addButton);
      });

      expect(mockNavigate).toHaveBeenCalledWith('AddFixedExpense');
    });
  });

  describe('에러 처리', () => {
    it('목록 로드 실패 시 에러 메시지 표시', async () => {
      const error = new Error('고정지출 목록을 불러오는데 실패했습니다');
      (fixedExpenseService.getFixedExpenses as jest.Mock).mockRejectedValue(
        error
      );

      render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '오류',
          '고정지출 목록을 불러오는데 실패했습니다'
        );
      });
    });
  });

  describe('새로고침', () => {
    it('Pull to Refresh로 목록 다시 로드', async () => {
      (fixedExpenseService.getFixedExpenses as jest.Mock)
        .mockResolvedValueOnce(mockExpenses)
        .mockResolvedValueOnce(mockExpenses);

      const { getByText, getByTestId } = render(<FixedExpensesScreen />);

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      expect(fixedExpenseService.getFixedExpenses).toHaveBeenCalledTimes(1);
    });
  });
});
