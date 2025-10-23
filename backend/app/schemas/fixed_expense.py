"""
고정지출 Pydantic 스키마 정의

API 요청/응답 데이터의 검증 및 직렬화를 위한 스키마입니다.

주요 차이점:
- models/fixed_expense.py: SQLAlchemy ORM 모델 (DB 테이블 구조)
- schemas/fixed_expense.py: Pydantic 스키마 (API 입출력 데이터 구조)
"""

from pydantic import BaseModel, Field, field_validator
from typing import Optional, List
from datetime import datetime


class FixedExpenseBase(BaseModel):
    """고정지출 항목 기본 스키마

    여러 스키마에서 공통으로 사용하는 필드를 정의합니다.
    """

    name: Optional[str] = None
    default_amount: Optional[int] = None
    is_fixed_amount: Optional[bool] = False
    expected_payment_day: Optional[int] = None
    is_active: Optional[bool] = True


class FixedExpenseCreate(BaseModel):
    """고정지출 항목 생성 요청 스키마

    POST /api/v1/fixed-expenses 엔드포인트에서 사용합니다.

    Attributes:
        name (str): 항목 이름 (예: 월세, 전기세)
        default_amount (int, optional): 기본 금액 (변동 금액인 경우 None)
        is_fixed_amount (bool): 금액 고정 여부 (기본값: False)
        expected_payment_day (int, optional): 예상 지출일 (1-31)

    Example:
        {
            "name": "월세",
            "default_amount": 800000,
            "is_fixed_amount": true,
            "expected_payment_day": 5
        }
    """

    name: str = Field(..., min_length=1, max_length=100)
    default_amount: Optional[int] = Field(None, ge=0)
    is_fixed_amount: bool = False
    expected_payment_day: Optional[int] = Field(None, ge=1, le=31)

    @field_validator('expected_payment_day')
    @classmethod
    def validate_payment_day(cls, v: Optional[int]) -> Optional[int]:
        """예상 지출일 검증

        Args:
            v: 검증할 지출일 (1-31)

        Returns:
            Optional[int]: 검증된 지출일

        Raises:
            ValueError: 지출일이 1-31 범위를 벗어날 경우
        """
        if v is not None and (v < 1 or v > 31):
            raise ValueError('Payment day must be between 1 and 31')
        return v


class FixedExpenseUpdate(BaseModel):
    """고정지출 항목 수정 요청 스키마

    PUT /api/v1/fixed-expenses/{id} 엔드포인트에서 사용합니다.

    Note:
        - 모든 필드가 Optional: 원하는 필드만 수정 가능

    Example:
        {
            "name": "전기세",
            "is_fixed_amount": false
        }
    """

    name: Optional[str] = Field(None, min_length=1, max_length=100)
    default_amount: Optional[int] = Field(None, ge=0)
    is_fixed_amount: Optional[bool] = None
    expected_payment_day: Optional[int] = Field(None, ge=1, le=31)
    is_active: Optional[bool] = None


class FixedExpenseInDB(FixedExpenseBase):
    """데이터베이스에서 가져온 고정지출 항목 스키마

    Attributes:
        id (int): 고정지출 항목 ID
        user_id (int): 사용자 ID
        created_at (datetime): 생성 시각
        updated_at (datetime, optional): 수정 시각
    """

    id: int
    user_id: int
    name: str
    created_at: datetime
    updated_at: Optional[datetime] = None

    class Config:
        from_attributes = True


class FixedExpense(FixedExpenseInDB):
    """API 응답용 고정지출 항목 스키마

    GET /api/v1/fixed-expenses 등에서 사용합니다.

    Example Response:
        {
            "id": 1,
            "user_id": 1,
            "name": "월세",
            "default_amount": 800000,
            "is_fixed_amount": true,
            "expected_payment_day": 5,
            "is_active": true,
            "created_at": "2025-10-01T00:00:00+09:00",
            "updated_at": null
        }
    """

    pass


class FixedExpenseRecordBase(BaseModel):
    """월별 고정지출 기록 기본 스키마

    여러 스키마에서 공통으로 사용하는 필드를 정의합니다.
    """

    year: Optional[int] = None
    month: Optional[int] = None
    amount: Optional[int] = None
    is_paid: Optional[bool] = False
    paid_at: Optional[datetime] = None
    memo: Optional[str] = None


class FixedExpenseRecordCreate(BaseModel):
    """월별 고정지출 기록 생성 요청 스키마

    POST /api/v1/fixed-expenses/{expense_id}/records 엔드포인트에서 사용합니다.

    Attributes:
        year (int): 년도
        month (int): 월 (1-12)
        amount (int): 금액
        is_paid (bool): 지출 완료 여부 (기본값: False)
        memo (str, optional): 메모

    Example:
        {
            "year": 2025,
            "month": 10,
            "amount": 800000,
            "is_paid": false,
            "memo": "10월 월세"
        }
    """

    year: int = Field(..., ge=2000, le=2100)
    month: int = Field(..., ge=1, le=12)
    amount: int = Field(..., ge=0)
    is_paid: bool = False
    memo: Optional[str] = Field(None, max_length=500)

    @field_validator('month')
    @classmethod
    def validate_month(cls, v: int) -> int:
        """월 검증

        Args:
            v: 검증할 월 (1-12)

        Returns:
            int: 검증된 월

        Raises:
            ValueError: 월이 1-12 범위를 벗어날 경우
        """
        if v < 1 or v > 12:
            raise ValueError('Month must be between 1 and 12')
        return v


class FixedExpenseRecordUpdate(BaseModel):
    """월별 고정지출 기록 수정 요청 스키마

    PUT /api/v1/fixed-expense-records/{id} 엔드포인트에서 사용합니다.

    Note:
        - 모든 필드가 Optional: 원하는 필드만 수정 가능

    Example:
        {
            "amount": 850000,
            "is_paid": true,
            "memo": "금액 변경됨"
        }
    """

    amount: Optional[int] = Field(None, ge=0)
    is_paid: Optional[bool] = None
    paid_at: Optional[datetime] = None
    memo: Optional[str] = Field(None, max_length=500)


class FixedExpenseRecordInDB(FixedExpenseRecordBase):
    """데이터베이스에서 가져온 월별 기록 스키마

    Attributes:
        id (int): 기록 ID
        fixed_expense_id (int): 고정지출 항목 ID
        created_at (datetime): 생성 시각
        updated_at (datetime, optional): 수정 시각
    """

    id: int
    fixed_expense_id: int
    year: int
    month: int
    amount: int
    created_at: datetime
    updated_at: Optional[datetime] = None

    class Config:
        from_attributes = True


class FixedExpenseRecord(FixedExpenseRecordInDB):
    """API 응답용 월별 기록 스키마

    GET /api/v1/fixed-expense-records 등에서 사용합니다.

    Example Response:
        {
            "id": 1,
            "fixed_expense_id": 1,
            "year": 2025,
            "month": 10,
            "amount": 800000,
            "is_paid": false,
            "paid_at": null,
            "memo": "10월 월세",
            "created_at": "2025-10-01T00:00:00+09:00",
            "updated_at": null
        }
    """

    pass


class FixedExpenseWithRecords(FixedExpense):
    """고정지출 항목과 월별 기록을 함께 반환하는 스키마

    특정 월의 고정지출 내역을 조회할 때 사용합니다.

    Attributes:
        records (List[FixedExpenseRecord]): 해당 항목의 월별 기록 리스트

    Example Response:
        {
            "id": 1,
            "name": "월세",
            "default_amount": 800000,
            "is_fixed_amount": true,
            "records": [
                {
                    "id": 1,
                    "year": 2025,
                    "month": 10,
                    "amount": 800000,
                    "is_paid": true,
                    "paid_at": "2025-10-05T12:00:00+09:00"
                }
            ]
        }
    """

    records: List[FixedExpenseRecord] = []


class MonthlyExpensesSummary(BaseModel):
    """월별 고정지출 요약 스키마

    GET /api/v1/fixed-expenses/summary/{year}/{month} 엔드포인트에서 사용합니다.

    Attributes:
        year (int): 년도
        month (int): 월
        total_expected (int): 예상 총 금액
        total_paid (int): 지출 완료 금액
        total_unpaid (int): 미지출 금액
        total_count (int): 전체 항목 수
        paid_count (int): 지출 완료 항목 수
        unpaid_count (int): 미지출 항목 수
        completion_rate (float): 완료율 (0-100)
        expenses (List[FixedExpenseWithRecords]): 고정지출 항목 리스트

    Example Response:
        {
            "year": 2025,
            "month": 10,
            "total_expected": 1250000,
            "total_paid": 845000,
            "total_unpaid": 405000,
            "total_count": 4,
            "paid_count": 2,
            "unpaid_count": 2,
            "completion_rate": 50.0,
            "expenses": [...]
        }
    """

    year: int
    month: int
    total_expected: int
    total_paid: int
    total_unpaid: int
    total_count: int
    paid_count: int
    unpaid_count: int
    completion_rate: float
    expenses: List[FixedExpenseWithRecords] = []
