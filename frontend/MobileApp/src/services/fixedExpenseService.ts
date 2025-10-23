/**
 * 고정지출 API 서비스
 *
 * 백엔드 /api/v1/fixed-expenses 엔드포인트와 통신합니다.
 */

import api from './api';
import {
  FixedExpense,
  FixedExpenseCreateRequest,
  FixedExpenseUpdateRequest,
  FixedExpenseRecord,
  FixedExpenseRecordCreateRequest,
  MonthlyExpensesSummary,
} from '../types/fixedExpense';

const BASE_URL = '/api/v1/fixed-expenses';

/**
 * 고정지출 항목 목록 조회
 *
 * @param isActive - 활성 항목만 조회 (기본값: true)
 * @param year - 특정 년도의 항목만 조회 (시간 유효성 필터링)
 * @param month - 특정 월의 항목만 조회 (시간 유효성 필터링)
 * @returns 고정지출 항목 리스트
 */
export const getFixedExpenses = async (
  isActive: boolean = true,
  year?: number,
  month?: number
): Promise<FixedExpense[]> => {
  const params: Record<string, boolean | number> = { is_active: isActive };
  if (year !== undefined) params.year = year;
  if (month !== undefined) params.month = month;

  const response = await api.get<FixedExpense[]>(BASE_URL, { params });
  return response.data;
};

/**
 * 고정지출 항목 상세 조회
 *
 * @param id - 고정지출 항목 ID
 * @returns 고정지출 항목
 */
export const getFixedExpense = async (id: number): Promise<FixedExpense> => {
  const response = await api.get<FixedExpense>(`${BASE_URL}/${id}`);
  return response.data;
};

/**
 * 고정지출 항목 생성
 *
 * @param data - 생성할 데이터
 * @returns 생성된 고정지출 항목
 */
export const createFixedExpense = async (
  data: FixedExpenseCreateRequest
): Promise<FixedExpense> => {
  const response = await api.post<FixedExpense>(BASE_URL, data);
  return response.data;
};

/**
 * 고정지출 항목 수정 (시간 유효성 적용)
 *
 * 현재 달(10월 29일): 기존 항목 종료(10월까지), 새 항목 생성(11월부터), 10월 기록 수정
 * 다음 달(11월 2일): 기존 항목 종료(10월까지), 새 항목 생성(11월부터), 10월 기록 보존
 *
 * @param id - 고정지출 항목 ID
 * @param data - 수정할 데이터
 * @returns 새로 생성된 고정지출 항목 (다음 달부터 유효)
 */
export const updateFixedExpense = async (
  id: number,
  data: FixedExpenseUpdateRequest
): Promise<FixedExpense> => {
  const response = await api.put<FixedExpense>(`${BASE_URL}/${id}`, data);
  return response.data;
};

/**
 * 고정지출 항목 삭제 (시간 유효성 적용)
 *
 * 현재 달(10월 29일): 기존 항목 종료(10월까지), 10월 기록 삭제
 * 다음 달(11월 2일): 기존 항목 종료(10월까지), 10월 기록 보존
 *
 * @param id - 고정지출 항목 ID
 */
export const deleteFixedExpense = async (id: number): Promise<void> => {
  await api.delete(`${BASE_URL}/${id}`);
};

/**
 * 월별 기록 생성
 *
 * @param expenseId - 고정지출 항목 ID
 * @param data - 월별 기록 데이터
 * @returns 생성된 월별 기록
 */
export const createExpenseRecord = async (
  expenseId: number,
  data: FixedExpenseRecordCreateRequest
): Promise<FixedExpenseRecord> => {
  const response = await api.post<FixedExpenseRecord>(
    `${BASE_URL}/${expenseId}/records`,
    data
  );
  return response.data;
};

/**
 * 월별 기록 지출 완료 처리
 *
 * @param recordId - 월별 기록 ID
 * @returns 업데이트된 월별 기록
 */
export const markRecordAsPaid = async (
  recordId: number
): Promise<FixedExpenseRecord> => {
  const response = await api.put<FixedExpenseRecord>(
    `${BASE_URL}/records/${recordId}/mark-paid`
  );
  return response.data;
};

/**
 * 월별 고정지출 요약 조회
 *
 * @param year - 년도
 * @param month - 월 (1-12)
 * @returns 월별 요약 정보
 */
export const getMonthlySummary = async (
  year: number,
  month: number
): Promise<MonthlyExpensesSummary> => {
  const response = await api.get<MonthlyExpensesSummary>(
    `${BASE_URL}/summary/${year}/${month}`
  );
  return response.data;
};
