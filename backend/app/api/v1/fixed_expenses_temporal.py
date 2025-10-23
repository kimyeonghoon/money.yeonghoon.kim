"""
시간 유효성(Temporal Validity) 헬퍼 함수

고정지출 항목의 수정/삭제 시 시간 기반 로직을 처리합니다.
"""
from datetime import date, timedelta
import calendar
from sqlalchemy.orm import Session

from app.models.fixed_expense import FixedExpense, FixedExpenseRecord


def get_current_month_range(today: date = None) -> tuple[date, date]:
    """현재 달의 시작일과 종료일을 반환

    Args:
        today: 기준 날짜 (기본값: 오늘)

    Returns:
        tuple[date, date]: (월 시작일, 월 종료일)

    Example:
        >>> get_current_month_range(date(2025, 10, 23))
        (date(2025, 10, 1), date(2025, 10, 31))
    """
    if today is None:
        today = date.today()

    month_start = date(today.year, today.month, 1)
    last_day = calendar.monthrange(today.year, today.month)[1]
    month_end = date(today.year, today.month, last_day)

    return month_start, month_end


def get_next_month_start(today: date = None) -> date:
    """다음 달 1일을 반환

    Args:
        today: 기준 날짜 (기본값: 오늘)

    Returns:
        date: 다음 달 1일

    Example:
        >>> get_next_month_start(date(2025, 10, 23))
        date(2025, 11, 1)
    """
    if today is None:
        today = date.today()

    if today.month == 12:
        return date(today.year + 1, 1, 1)
    else:
        return date(today.year, today.month + 1, 1)


def close_expense_validity(expense: FixedExpense, db: Session, today: date = None) -> None:
    """고정지출 항목의 유효기간을 이번 달까지로 종료

    Args:
        expense: 종료할 고정지출 항목
        db: 데이터베이스 세션
        today: 기준 날짜 (기본값: 오늘)

    Example:
        >>> close_expense_validity(expense, db, date(2025, 10, 29))
        >>> assert expense.valid_until == date(2025, 10, 31)
    """
    _, month_end = get_current_month_range(today)
    expense.valid_until = month_end
    db.commit()


def create_new_expense_version(
    expense: FixedExpense,
    update_data: dict,
    db: Session,
    today: date = None
) -> FixedExpense:
    """새로운 버전의 고정지출 항목 생성 (다음 달부터 유효)

    Args:
        expense: 기존 고정지출 항목
        update_data: 수정할 데이터
        db: 데이터베이스 세션
        today: 기준 날짜 (기본값: 오늘)

    Returns:
        FixedExpense: 생성된 새 항목

    Example:
        >>> new_expense = create_new_expense_version(
        ...     expense,
        ...     {"default_amount": 850000},
        ...     db,
        ...     date(2025, 10, 29)
        ... )
        >>> assert new_expense.valid_from == date(2025, 11, 1)
    """
    next_month_start = get_next_month_start(today)

    new_expense = FixedExpense(
        user_id=expense.user_id,
        name=update_data.get("name", expense.name),
        default_amount=update_data.get("default_amount", expense.default_amount),
        is_fixed_amount=update_data.get("is_fixed_amount", expense.is_fixed_amount),
        expected_payment_day=update_data.get("expected_payment_day", expense.expected_payment_day),
        is_active=update_data.get("is_active", expense.is_active),
        valid_from=next_month_start,
        valid_until=None
    )

    db.add(new_expense)
    db.commit()
    db.refresh(new_expense)

    return new_expense


def update_current_month_records(
    expense_id: int,
    update_data: dict,
    db: Session,
    today: date = None
) -> None:
    """현재 달의 기록을 수정 (같은 달 안에서 수정 시)

    Args:
        expense_id: 고정지출 항목 ID
        update_data: 수정할 데이터
        db: 데이터베이스 세션
        today: 기준 날짜 (기본값: 오늘)

    Example:
        >>> update_current_month_records(
        ...     1,
        ...     {"default_amount": 850000},
        ...     db,
        ...     date(2025, 10, 29)
        ... )
        # 10월 기록의 amount가 850000으로 수정됨
    """
    if today is None:
        today = date.today()

    if "default_amount" in update_data and update_data["default_amount"] is not None:
        # RAW SQL: UPDATE fixed_expense_records
        # SET amount = ?
        # WHERE fixed_expense_id = ? AND year = ? AND month = ?
        db.query(FixedExpenseRecord).filter(
            FixedExpenseRecord.fixed_expense_id == expense_id,
            FixedExpenseRecord.year == today.year,
            FixedExpenseRecord.month == today.month
        ).update({"amount": update_data["default_amount"]})
        db.commit()


def delete_current_month_records(
    expense_id: int,
    db: Session,
    today: date = None
) -> None:
    """현재 달의 기록을 삭제 (같은 달 안에서 삭제 시)

    Args:
        expense_id: 고정지출 항목 ID
        db: 데이터베이스 세션
        today: 기준 날짜 (기본값: 오늘)

    Example:
        >>> delete_current_month_records(1, db, date(2025, 10, 29))
        # 10월 기록 삭제됨
    """
    if today is None:
        today = date.today()

    # RAW SQL: DELETE FROM fixed_expense_records
    # WHERE fixed_expense_id = ? AND year = ? AND month = ?
    db.query(FixedExpenseRecord).filter(
        FixedExpenseRecord.fixed_expense_id == expense_id,
        FixedExpenseRecord.year == today.year,
        FixedExpenseRecord.month == today.month
    ).delete()
    db.commit()
