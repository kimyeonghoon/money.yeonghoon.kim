/**
 * 고정지출 관련 타입 정의
 *
 * 백엔드 API 스키마와 매핑됩니다.
 */

/**
 * 고정지출 항목
 *
 * 시간 유효성(Temporal Validity)을 지원합니다:
 * - valid_from: 항목이 유효한 시작일
 * - valid_until: 항목이 유효한 종료일 (null이면 현재 유효)
 */
export interface FixedExpense {
  id: number;
  user_id: number;
  name: string;
  default_amount: number | null;
  is_fixed_amount: boolean;
  expected_payment_day: number | null;
  is_active: boolean;
  valid_from: string;
  valid_until: string | null;
  created_at: string;
  updated_at: string | null;
}

/**
 * 고정지출 항목 생성 요청
 */
export interface FixedExpenseCreateRequest {
  name: string;
  default_amount?: number | null;
  is_fixed_amount?: boolean;
  expected_payment_day?: number | null;
}

/**
 * 고정지출 항목 수정 요청
 */
export interface FixedExpenseUpdateRequest {
  name?: string;
  default_amount?: number | null;
  is_fixed_amount?: boolean;
  expected_payment_day?: number | null;
  is_active?: boolean;
}

/**
 * 월별 고정지출 기록
 */
export interface FixedExpenseRecord {
  id: number;
  fixed_expense_id: number;
  year: number;
  month: number;
  amount: number;
  is_paid: boolean;
  paid_at: string | null;
  memo: string | null;
  created_at: string;
  updated_at: string | null;
}

/**
 * 월별 기록 생성 요청
 */
export interface FixedExpenseRecordCreateRequest {
  year: number;
  month: number;
  amount: number;
  is_paid?: boolean;
  memo?: string | null;
}

/**
 * 고정지출 항목 + 월별 기록
 *
 * 월별 요약 API에서 사용됩니다.
 */
export interface FixedExpenseWithRecords extends FixedExpense {
  records: FixedExpenseRecord[];
}

/**
 * 월별 고정지출 요약
 */
export interface MonthlyExpensesSummary {
  year: number;
  month: number;
  total_expected: number;
  total_paid: number;
  total_unpaid: number;
  total_count: number;
  paid_count: number;
  unpaid_count: number;
  completion_rate: number;
  expenses: FixedExpenseWithRecords[];
}
