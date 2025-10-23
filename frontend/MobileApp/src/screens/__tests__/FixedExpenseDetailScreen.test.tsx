/**
 * FixedExpenseDetailScreen 테스트
 *
 * Given-When-Then 패턴으로 작성되었습니다.
 */

import React from 'react';
import { render, waitFor, fireEvent } from '@testing-library/react-native';
import { Alert } from 'react-native';
import { FixedExpenseDetailScreen } from '../FixedExpenseDetailScreen';
import * as fixedExpenseService from '../../services/fixedExpenseService';
import { FixedExpense, MonthlyExpensesSummary } from '../../types/fixedExpense';

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

const mockMonthlySummary: MonthlyExpensesSummary = {
  year: 2025,
  month: 10,
  total_expected: 800000,
  total_paid: 0,
  total_unpaid: 800000,
  total_count: 1,
  paid_count: 0,
  unpaid_count: 1,
  completion_rate: 0,
  expenses: [
    {
      ...mockExpense,
      records: [
        {
          id: 1,
          fixed_expense_id: 1,
          year: 2025,
          month: 10,
          amount: 800000,
          is_paid: false,
          paid_at: null,
          memo: null,
          created_at: '2025-10-01T00:00:00Z',
          updated_at: null,
        },
      ],
    },
  ],
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

describe('FixedExpenseDetailScreen', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.spyOn(Alert, 'alert').mockImplementation(() => {});
    (fixedExpenseService.getFixedExpense as jest.Mock).mockResolvedValue(mockExpense);
    (fixedExpenseService.getMonthlySummary as jest.Mock).mockResolvedValue(mockMonthlySummary);
  });

  describe('초기 렌더링', () => {
    it('로딩 중 표시', () => {
      (fixedExpenseService.getFixedExpense as jest.Mock).mockImplementation(
        () => new Promise(() => {})
      );

      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      expect(getByText('로딩 중...')).toBeTruthy();
    });

    it('고정지출 상세 정보 표시', async () => {
      const { getByText, getAllByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
        const amountTexts = getAllByText('₩800,000');
        expect(amountTexts.length).toBeGreaterThan(0);
        expect(getByText('매월 5일')).toBeTruthy();
        expect(getByText('활성')).toBeTruthy();
      });
    });

    it('데이터 로드 실패 시 에러', async () => {
      const error = new Error('고정지출을 불러오는데 실패했습니다');
      (fixedExpenseService.getFixedExpense as jest.Mock).mockRejectedValue(error);

      render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
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

  describe('월별 기록 표시', () => {
    it('이번 달 기록 표시', async () => {
      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText(/2025년 10월/)).toBeTruthy();
        expect(getByText(/미납/)).toBeTruthy();
      });
    });

    it('지출 완료된 기록 표시', async () => {
      const paidSummary: MonthlyExpensesSummary = {
        ...mockMonthlySummary,
        total_paid: 800000,
        total_unpaid: 0,
        paid_count: 1,
        unpaid_count: 0,
        completion_rate: 100,
        expenses: [
          {
            ...mockExpense,
            records: [
              {
                ...mockMonthlySummary.expenses[0].records[0],
                is_paid: true,
                paid_at: '2025-10-05T00:00:00Z',
              },
            ],
          },
        ],
      };
      (fixedExpenseService.getMonthlySummary as jest.Mock).mockResolvedValue(paidSummary);

      const { getAllByText, queryByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        const completeTexts = getAllByText(/완료/);
        expect(completeTexts.length).toBeGreaterThan(0);
        expect(queryByText('완료 처리')).toBeNull();
      });
    });

    it('월별 기록이 없을 때', async () => {
      const emptySummary: MonthlyExpensesSummary = {
        year: 2025,
        month: 10,
        total_expected: 0,
        total_paid: 0,
        total_unpaid: 0,
        total_count: 1,
        paid_count: 0,
        unpaid_count: 0,
        completion_rate: 0,
        expenses: [
          {
            ...mockExpense,
            records: [],
          },
        ],
      };
      (fixedExpenseService.getMonthlySummary as jest.Mock).mockResolvedValue(emptySummary);

      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText(/이번 달 기록이 없습니다/)).toBeTruthy();
      });
    });
  });

  describe('지출 완료 처리', () => {
    it('지출 완료 버튼 클릭', async () => {
      (fixedExpenseService.markRecordAsPaid as jest.Mock).mockResolvedValue({
        ...mockMonthlySummary.expenses[0].records[0],
        is_paid: true,
        paid_at: '2025-10-24T00:00:00Z',
      });

      const paidSummary: MonthlyExpensesSummary = {
        ...mockMonthlySummary,
        total_paid: 800000,
        total_unpaid: 0,
        paid_count: 1,
        unpaid_count: 0,
        completion_rate: 100,
        expenses: [
          {
            ...mockExpense,
            records: [
              {
                ...mockMonthlySummary.expenses[0].records[0],
                is_paid: true,
                paid_at: '2025-10-24T00:00:00Z',
              },
            ],
          },
        ],
      };
      (fixedExpenseService.getMonthlySummary as jest.Mock)
        .mockResolvedValueOnce(mockMonthlySummary)
        .mockResolvedValueOnce(paidSummary);

      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText(/미납/)).toBeTruthy();
      });

      const markPaidButton = getByText('완료 처리');
      fireEvent.press(markPaidButton);

      await waitFor(() => {
        expect(fixedExpenseService.markRecordAsPaid).toHaveBeenCalledWith(1);
        expect(Alert.alert).toHaveBeenCalledWith(
          '성공',
          '지출이 완료 처리되었습니다'
        );
      });
    });

    it('지출 완료 처리 실패', async () => {
      const error = new Error('지출 완료 처리에 실패했습니다');
      (fixedExpenseService.markRecordAsPaid as jest.Mock).mockRejectedValue(error);

      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText(/미납/)).toBeTruthy();
      });

      const markPaidButton = getByText('완료 처리');
      fireEvent.press(markPaidButton);

      await waitFor(() => {
        expect(Alert.alert).toHaveBeenCalledWith(
          '오류',
          '지출 완료 처리에 실패했습니다'
        );
      });
    });
  });

  describe('수정 기능', () => {
    it('수정 버튼 클릭 시 수정 화면으로 이동', async () => {
      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      const editButton = getByText('수정');
      fireEvent.press(editButton);

      expect(mockNavigation.navigate).toHaveBeenCalledWith('EditFixedExpense', {
        expenseId: 1,
      });
    });
  });

  describe('삭제 기능', () => {
    it('삭제 확인 대화상자 표시', async () => {
      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      const deleteButton = getByText('삭제');
      fireEvent.press(deleteButton);

      expect(Alert.alert).toHaveBeenCalledWith(
        '고정지출 삭제',
        expect.stringContaining('월세'),
        expect.any(Array)
      );
    });

    it('삭제 성공', async () => {
      (fixedExpenseService.deleteFixedExpense as jest.Mock).mockResolvedValue(undefined);

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

      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      const deleteButton = getByText('삭제');
      fireEvent.press(deleteButton);

      await waitFor(() => {
        expect(fixedExpenseService.deleteFixedExpense).toHaveBeenCalledWith(1);
        expect(alertSpy).toHaveBeenCalledWith(
          '성공',
          '"월세" 항목이 삭제되었습니다'
        );
        expect(mockNavigation.goBack).toHaveBeenCalled();
      });

      alertSpy.mockRestore();
    });

    it('삭제 실패', async () => {
      const error = new Error('삭제에 실패했습니다');
      (fixedExpenseService.deleteFixedExpense as jest.Mock).mockRejectedValue(error);

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

      const { getByText } = render(
        <FixedExpenseDetailScreen navigation={mockNavigation as any} route={mockRoute as any} />
      );

      await waitFor(() => {
        expect(getByText('월세')).toBeTruthy();
      });

      const deleteButton = getByText('삭제');
      fireEvent.press(deleteButton);

      await waitFor(() => {
        expect(alertSpy).toHaveBeenCalledWith('오류', '삭제에 실패했습니다');
      });

      alertSpy.mockRestore();
    });
  });
});
