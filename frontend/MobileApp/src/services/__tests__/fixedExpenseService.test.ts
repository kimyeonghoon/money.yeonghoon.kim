/**
 * fixedExpenseService 테스트
 *
 * 고정지출 API 서비스 함수를 테스트합니다.
 */

import * as fixedExpenseService from '../fixedExpenseService';
import api from '../api';
import {
  FixedExpense,
  FixedExpenseCreateRequest,
  FixedExpenseUpdateRequest,
  FixedExpenseRecordCreateRequest,
  MonthlyExpensesSummary,
} from '../../types/fixedExpense';

jest.mock('../api');

describe('fixedExpenseService', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('getFixedExpenses', () => {
    it('활성 항목 목록 조회', async () => {
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
      ];

      (api.get as jest.Mock).mockResolvedValue({ data: mockExpenses });

      const result = await fixedExpenseService.getFixedExpenses();

      expect(api.get).toHaveBeenCalledWith('/api/v1/fixed-expenses', {
        params: { is_active: true },
      });
      expect(result).toEqual(mockExpenses);
    });

    it('특정 년월의 항목 조회', async () => {
      const mockExpenses: FixedExpense[] = [];
      (api.get as jest.Mock).mockResolvedValue({ data: mockExpenses });

      await fixedExpenseService.getFixedExpenses(true, 2025, 10);

      expect(api.get).toHaveBeenCalledWith('/api/v1/fixed-expenses', {
        params: { is_active: true, year: 2025, month: 10 },
      });
    });

    it('비활성 항목 포함 조회', async () => {
      const mockExpenses: FixedExpense[] = [];
      (api.get as jest.Mock).mockResolvedValue({ data: mockExpenses });

      await fixedExpenseService.getFixedExpenses(false);

      expect(api.get).toHaveBeenCalledWith('/api/v1/fixed-expenses', {
        params: { is_active: false },
      });
    });
  });

  describe('getFixedExpense', () => {
    it('특정 고정지출 항목 조회', async () => {
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

      (api.get as jest.Mock).mockResolvedValue({ data: mockExpense });

      const result = await fixedExpenseService.getFixedExpense(1);

      expect(api.get).toHaveBeenCalledWith('/api/v1/fixed-expenses/1');
      expect(result).toEqual(mockExpense);
    });
  });

  describe('createFixedExpense', () => {
    it('고정지출 항목 생성', async () => {
      const request: FixedExpenseCreateRequest = {
        name: '월세',
        default_amount: 800000,
        is_fixed_amount: true,
        expected_payment_day: 5,
      };

      const mockResponse: FixedExpense = {
        id: 1,
        user_id: 1,
        ...request,
        is_active: true,
        valid_from: '2025-01-01',
        valid_until: null,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: null,
      };

      (api.post as jest.Mock).mockResolvedValue({ data: mockResponse });

      const result = await fixedExpenseService.createFixedExpense(request);

      expect(api.post).toHaveBeenCalledWith('/api/v1/fixed-expenses', request);
      expect(result).toEqual(mockResponse);
    });
  });

  describe('updateFixedExpense', () => {
    it('고정지출 항목 수정', async () => {
      const request: FixedExpenseUpdateRequest = {
        default_amount: 850000,
      };

      const mockResponse: FixedExpense = {
        id: 2,
        user_id: 1,
        name: '월세',
        default_amount: 850000,
        is_fixed_amount: true,
        expected_payment_day: 5,
        is_active: true,
        valid_from: '2025-11-01',
        valid_until: null,
        created_at: '2025-10-29T00:00:00Z',
        updated_at: null,
      };

      (api.put as jest.Mock).mockResolvedValue({ data: mockResponse });

      const result = await fixedExpenseService.updateFixedExpense(1, request);

      expect(api.put).toHaveBeenCalledWith('/api/v1/fixed-expenses/1', request);
      expect(result).toEqual(mockResponse);
    });
  });

  describe('deleteFixedExpense', () => {
    it('고정지출 항목 삭제', async () => {
      (api.delete as jest.Mock).mockResolvedValue({});

      await fixedExpenseService.deleteFixedExpense(1);

      expect(api.delete).toHaveBeenCalledWith('/api/v1/fixed-expenses/1');
    });
  });

  describe('createExpenseRecord', () => {
    it('월별 기록 생성', async () => {
      const request: FixedExpenseRecordCreateRequest = {
        year: 2025,
        month: 10,
        amount: 800000,
        is_paid: false,
      };

      const mockResponse = {
        id: 1,
        fixed_expense_id: 1,
        ...request,
        paid_at: null,
        memo: null,
        created_at: '2025-10-01T00:00:00Z',
        updated_at: null,
      };

      (api.post as jest.Mock).mockResolvedValue({ data: mockResponse });

      const result = await fixedExpenseService.createExpenseRecord(1, request);

      expect(api.post).toHaveBeenCalledWith(
        '/api/v1/fixed-expenses/1/records',
        request
      );
      expect(result).toEqual(mockResponse);
    });
  });

  describe('markRecordAsPaid', () => {
    it('월별 기록 지출 완료 처리', async () => {
      const mockResponse = {
        id: 1,
        fixed_expense_id: 1,
        year: 2025,
        month: 10,
        amount: 800000,
        is_paid: true,
        paid_at: '2025-10-05T12:00:00Z',
        memo: null,
        created_at: '2025-10-01T00:00:00Z',
        updated_at: '2025-10-05T12:00:00Z',
      };

      (api.put as jest.Mock).mockResolvedValue({ data: mockResponse });

      const result = await fixedExpenseService.markRecordAsPaid(1);

      expect(api.put).toHaveBeenCalledWith(
        '/api/v1/fixed-expenses/records/1/mark-paid'
      );
      expect(result).toEqual(mockResponse);
    });
  });

  describe('getMonthlySummary', () => {
    it('월별 요약 조회', async () => {
      const mockSummary: MonthlyExpensesSummary = {
        year: 2025,
        month: 10,
        total_expected: 1250000,
        total_paid: 845000,
        total_unpaid: 405000,
        total_count: 4,
        paid_count: 2,
        unpaid_count: 2,
        completion_rate: 50.0,
        expenses: [],
      };

      (api.get as jest.Mock).mockResolvedValue({ data: mockSummary });

      const result = await fixedExpenseService.getMonthlySummary(2025, 10);

      expect(api.get).toHaveBeenCalledWith(
        '/api/v1/fixed-expenses/summary/2025/10'
      );
      expect(result).toEqual(mockSummary);
    });
  });
});
