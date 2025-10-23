"""
고정지출 API 엔드포인트

사용자의 고정지출 항목 및 월별 기록을 관리하는 API입니다.
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from datetime import datetime

from app.database import get_db
from app.models.user import User
from app.models.fixed_expense import FixedExpense, FixedExpenseRecord
from app.schemas.fixed_expense import (
    FixedExpenseCreate,
    FixedExpenseUpdate,
    FixedExpense as FixedExpenseSchema,
    FixedExpenseRecordCreate,
    FixedExpenseRecordUpdate,
    FixedExpenseRecord as FixedExpenseRecordSchema,
    MonthlyExpensesSummary,
    FixedExpenseWithRecords,
)
from app.api.deps import get_current_active_user

router = APIRouter()


@router.post("", response_model=FixedExpenseSchema, status_code=status.HTTP_201_CREATED)
def create_fixed_expense(
    expense_in: FixedExpenseCreate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """고정지출 항목 생성

    새로운 고정지출 항목을 생성합니다.

    Args:
        expense_in: 고정지출 생성 데이터
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        FixedExpenseSchema: 생성된 고정지출 항목

    Example:
        POST /api/v1/fixed-expenses
        {
            "name": "월세",
            "default_amount": 800000,
            "is_fixed_amount": true,
            "expected_payment_day": 5
        }
    """
    # RAW SQL: INSERT INTO fixed_expenses (...) VALUES (...) RETURNING *
    expense = FixedExpense(
        user_id=current_user.id,
        **expense_in.model_dump()
    )
    db.add(expense)
    db.commit()
    db.refresh(expense)

    return expense


@router.get("", response_model=List[FixedExpenseSchema])
def get_fixed_expenses(
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
    is_active: bool = True,
):
    """고정지출 항목 목록 조회

    현재 사용자의 모든 고정지출 항목을 조회합니다.

    Args:
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션
        is_active: 활성 항목만 조회 여부

    Returns:
        List[FixedExpenseSchema]: 고정지출 항목 리스트
    """
    # RAW SQL: SELECT * FROM fixed_expenses WHERE user_id = ? AND is_active = ?
    query = db.query(FixedExpense).filter(FixedExpense.user_id == current_user.id)

    if is_active is not None:
        query = query.filter(FixedExpense.is_active == is_active)

    expenses = query.all()
    return expenses


@router.get("/{expense_id}", response_model=FixedExpenseSchema)
def get_fixed_expense(
    expense_id: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """고정지출 항목 상세 조회

    특정 고정지출 항목의 상세 정보를 조회합니다.

    Args:
        expense_id: 고정지출 항목 ID
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        FixedExpenseSchema: 고정지출 항목 정보

    Raises:
        HTTPException: 항목이 없거나 권한이 없을 경우 404
    """
    # RAW SQL: SELECT * FROM fixed_expenses WHERE id = ? AND user_id = ? LIMIT 1
    expense = db.query(FixedExpense).filter(
        FixedExpense.id == expense_id,
        FixedExpense.user_id == current_user.id
    ).first()

    if not expense:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Fixed expense not found"
        )

    return expense


@router.put("/{expense_id}", response_model=FixedExpenseSchema)
def update_fixed_expense(
    expense_id: int,
    expense_in: FixedExpenseUpdate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """고정지출 항목 수정

    기존 고정지출 항목을 수정합니다.

    Args:
        expense_id: 고정지출 항목 ID
        expense_in: 수정할 데이터
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        FixedExpenseSchema: 수정된 고정지출 항목

    Raises:
        HTTPException: 항목이 없거나 권한이 없을 경우 404
    """
    # RAW SQL: SELECT * FROM fixed_expenses WHERE id = ? AND user_id = ? LIMIT 1
    expense = db.query(FixedExpense).filter(
        FixedExpense.id == expense_id,
        FixedExpense.user_id == current_user.id
    ).first()

    if not expense:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Fixed expense not found"
        )

    update_data = expense_in.model_dump(exclude_unset=True)

    # RAW SQL: UPDATE fixed_expenses SET ... WHERE id = ?
    for field, value in update_data.items():
        setattr(expense, field, value)

    db.commit()
    db.refresh(expense)

    return expense


@router.delete("/{expense_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_fixed_expense(
    expense_id: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """고정지출 항목 삭제

    고정지출 항목을 삭제합니다. 관련된 월별 기록도 함께 삭제됩니다 (CASCADE).

    Args:
        expense_id: 고정지출 항목 ID
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Raises:
        HTTPException: 항목이 없거나 권한이 없을 경우 404
    """
    # RAW SQL: SELECT * FROM fixed_expenses WHERE id = ? AND user_id = ? LIMIT 1
    expense = db.query(FixedExpense).filter(
        FixedExpense.id == expense_id,
        FixedExpense.user_id == current_user.id
    ).first()

    if not expense:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Fixed expense not found"
        )

    # RAW SQL: DELETE FROM fixed_expenses WHERE id = ?
    db.delete(expense)
    db.commit()


@router.post("/{expense_id}/records", response_model=FixedExpenseRecordSchema, status_code=status.HTTP_201_CREATED)
def create_expense_record(
    expense_id: int,
    record_in: FixedExpenseRecordCreate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """월별 고정지출 기록 생성

    특정 고정지출 항목의 월별 기록을 생성합니다.

    Args:
        expense_id: 고정지출 항목 ID
        record_in: 월별 기록 생성 데이터
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        FixedExpenseRecordSchema: 생성된 월별 기록

    Raises:
        HTTPException: 항목이 없거나 중복 기록이 있을 경우
    """
    # RAW SQL: SELECT * FROM fixed_expenses WHERE id = ? AND user_id = ? LIMIT 1
    expense = db.query(FixedExpense).filter(
        FixedExpense.id == expense_id,
        FixedExpense.user_id == current_user.id
    ).first()

    if not expense:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Fixed expense not found"
        )

    # RAW SQL: SELECT * FROM fixed_expense_records WHERE fixed_expense_id = ? AND year = ? AND month = ? LIMIT 1
    existing_record = db.query(FixedExpenseRecord).filter(
        FixedExpenseRecord.fixed_expense_id == expense_id,
        FixedExpenseRecord.year == record_in.year,
        FixedExpenseRecord.month == record_in.month
    ).first()

    if existing_record:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Record for this month already exists"
        )

    # RAW SQL: INSERT INTO fixed_expense_records (...) VALUES (...) RETURNING *
    record = FixedExpenseRecord(
        fixed_expense_id=expense_id,
        **record_in.model_dump()
    )
    db.add(record)
    db.commit()
    db.refresh(record)

    return record


@router.put("/records/{record_id}/mark-paid", response_model=FixedExpenseRecordSchema)
def mark_record_as_paid(
    record_id: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """월별 기록 지출 완료 처리

    미지출 기록을 지출 완료 상태로 변경합니다.

    Args:
        record_id: 월별 기록 ID
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        FixedExpenseRecordSchema: 업데이트된 월별 기록

    Raises:
        HTTPException: 기록이 없거나 권한이 없을 경우 404
    """
    # RAW SQL: SELECT * FROM fixed_expense_records r
    # JOIN fixed_expenses e ON r.fixed_expense_id = e.id
    # WHERE r.id = ? AND e.user_id = ? LIMIT 1
    record = db.query(FixedExpenseRecord).join(FixedExpense).filter(
        FixedExpenseRecord.id == record_id,
        FixedExpense.user_id == current_user.id
    ).first()

    if not record:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Expense record not found"
        )

    record.mark_as_paid()
    db.commit()
    db.refresh(record)

    return record


@router.get("/summary/{year}/{month}", response_model=MonthlyExpensesSummary)
def get_monthly_summary(
    year: int,
    month: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """월별 고정지출 요약 조회

    특정 월의 고정지출 요약 정보를 조회합니다.

    Args:
        year: 년도
        month: 월 (1-12)
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        MonthlyExpensesSummary: 월별 요약 정보

    Raises:
        HTTPException: 잘못된 월 입력 시 400
    """
    if month < 1 or month > 12:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Invalid month"
        )

    # RAW SQL: SELECT * FROM fixed_expense_records r
    # JOIN fixed_expenses e ON r.fixed_expense_id = e.id
    # WHERE e.user_id = ? AND r.year = ? AND r.month = ?
    records = db.query(FixedExpenseRecord).join(FixedExpense).filter(
        FixedExpense.user_id == current_user.id,
        FixedExpenseRecord.year == year,
        FixedExpenseRecord.month == month
    ).all()

    total_expected = sum(r.amount for r in records)
    total_paid = sum(r.amount for r in records if r.is_paid)
    total_unpaid = total_expected - total_paid
    paid_count = sum(1 for r in records if r.is_paid)
    unpaid_count = len(records) - paid_count
    completion_rate = (paid_count / len(records) * 100) if records else 0.0

    # 고정지출 항목과 기록을 함께 조회
    expenses_with_records = []
    for record in records:
        expense = record.fixed_expense
        expense_dict = FixedExpenseWithRecords(
            id=expense.id,
            user_id=expense.user_id,
            name=expense.name,
            default_amount=expense.default_amount,
            is_fixed_amount=expense.is_fixed_amount,
            expected_payment_day=expense.expected_payment_day,
            is_active=expense.is_active,
            created_at=expense.created_at,
            updated_at=expense.updated_at,
            records=[FixedExpenseRecordSchema(
                id=record.id,
                fixed_expense_id=record.fixed_expense_id,
                year=record.year,
                month=record.month,
                amount=record.amount,
                is_paid=record.is_paid,
                paid_at=record.paid_at,
                memo=record.memo,
                created_at=record.created_at,
                updated_at=record.updated_at
            )]
        )
        expenses_with_records.append(expense_dict)

    return MonthlyExpensesSummary(
        year=year,
        month=month,
        total_expected=total_expected,
        total_paid=total_paid,
        total_unpaid=total_unpaid,
        total_count=len(records),
        paid_count=paid_count,
        unpaid_count=unpaid_count,
        completion_rate=round(completion_rate, 2),
        expenses=expenses_with_records
    )
