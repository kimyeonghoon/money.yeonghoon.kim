"""
고정지출 모델 정의

사용자의 고정지출 항목 및 월별 기록을 관리하는 모델입니다.

테이블 구조:
    1. fixed_expenses (고정지출 항목)
    CREATE TABLE fixed_expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        default_amount INT,
        is_fixed_amount BOOLEAN DEFAULT FALSE,
        expected_payment_day INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT NOW(),
        updated_at DATETIME ON UPDATE NOW(),
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_user_id (user_id),
        INDEX idx_user_active (user_id, is_active)
    );

    2. fixed_expense_records (월별 기록)
    CREATE TABLE fixed_expense_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fixed_expense_id INT NOT NULL,
        year INT NOT NULL,
        month INT NOT NULL,
        amount INT NOT NULL,
        is_paid BOOLEAN DEFAULT FALSE,
        paid_at DATETIME,
        memo TEXT,
        created_at DATETIME DEFAULT NOW(),
        updated_at DATETIME ON UPDATE NOW(),
        FOREIGN KEY (fixed_expense_id) REFERENCES fixed_expenses(id) ON DELETE CASCADE,
        UNIQUE KEY unique_year_month_per_expense (fixed_expense_id, year, month),
        INDEX idx_year_month (year, month),
        INDEX idx_is_paid (is_paid)
    );
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey, Text, UniqueConstraint
from sqlalchemy.orm import relationship, Session
from sqlalchemy.sql import func
from datetime import datetime
from app.database import Base


class FixedExpense(Base):
    """고정지출 항목 ORM 모델

    사용자의 고정지출 항목(템플릿)을 관리합니다.
    예: 월세, 전기세, Netflix 구독료 등

    Attributes:
        id (int): 고정지출 항목 고유 식별자
        user_id (int): 사용자 ID (외래 키)
        name (str): 항목 이름 (예: 월세, 전기세)
        default_amount (int, optional): 기본 금액 (변동 금액인 경우 None)
        is_fixed_amount (bool): 금액 고정 여부 (True: 고정, False: 변동)
        expected_payment_day (int, optional): 예상 지출일 (1-31)
        is_active (bool): 활성 상태 (비활성화 시 삭제하지 않고 숨김)
        created_at (datetime): 생성 시각
        updated_at (datetime): 수정 시각
        records (List[FixedExpenseRecord]): 월별 기록 (relationship)

    Indexes:
        - idx_user_id: 사용자별 조회 최적화
        - idx_user_active: 활성 항목 조회 최적화

    Example:
        >>> expense = FixedExpense(
        ...     user_id=1,
        ...     name="월세",
        ...     default_amount=800000,
        ...     is_fixed_amount=True,
        ...     expected_payment_day=5
        ... )
    """

    __tablename__ = "fixed_expenses"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    name = Column(String(100), nullable=False)
    default_amount = Column(Integer, nullable=True)
    is_fixed_amount = Column(Boolean, default=False)
    expected_payment_day = Column(Integer, nullable=True)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    records = relationship("FixedExpenseRecord", back_populates="fixed_expense", cascade="all, delete-orphan")

    def __repr__(self):
        """객체의 문자열 표현

        Returns:
            str: 고정지출 정보를 포함한 문자열

        Example:
            >>> print(expense)
            <FixedExpense(id=1, name=월세, amount=800000)>
        """
        return f"<FixedExpense(id={self.id}, name={self.name}, amount={self.default_amount})>"


class FixedExpenseRecord(Base):
    """월별 고정지출 기록 ORM 모델

    고정지출 항목의 실제 월별 지출 기록을 관리합니다.

    Attributes:
        id (int): 기록 고유 식별자
        fixed_expense_id (int): 고정지출 항목 ID (외래 키)
        year (int): 년도
        month (int): 월 (1-12)
        amount (int): 실제 금액
        is_paid (bool): 지출 완료 여부
        paid_at (datetime, optional): 지출 완료 일시
        memo (str, optional): 메모
        created_at (datetime): 생성 시각
        updated_at (datetime): 수정 시각
        fixed_expense (FixedExpense): 고정지출 항목 (relationship)

    Constraints:
        - UNIQUE(fixed_expense_id, year, month): 동일 항목의 월별 기록 중복 방지

    Indexes:
        - idx_year_month: 월별 조회 최적화
        - idx_is_paid: 미지출 항목 필터링 최적화

    Example:
        >>> record = FixedExpenseRecord(
        ...     fixed_expense_id=1,
        ...     year=2025,
        ...     month=10,
        ...     amount=800000,
        ...     is_paid=False
        ... )
    """

    __tablename__ = "fixed_expense_records"
    __table_args__ = (
        UniqueConstraint('fixed_expense_id', 'year', 'month', name='unique_year_month_per_expense'),
    )

    id = Column(Integer, primary_key=True, index=True)
    fixed_expense_id = Column(
        Integer,
        ForeignKey("fixed_expenses.id", ondelete="CASCADE"),
        nullable=False
    )
    year = Column(Integer, nullable=False)
    month = Column(Integer, nullable=False)
    amount = Column(Integer, nullable=False)
    is_paid = Column(Boolean, default=False)
    paid_at = Column(DateTime(timezone=True), nullable=True)
    memo = Column(Text, nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    fixed_expense = relationship("FixedExpense", back_populates="records")

    def mark_as_paid(self, paid_at: datetime = None):
        """지출 완료 처리

        지출을 완료 상태로 변경하고 완료 일시를 기록합니다.

        Args:
            paid_at (datetime, optional): 지출 완료 일시. 미지정 시 현재 시각 사용

        Example:
            >>> record.mark_as_paid()
            >>> assert record.is_paid == True
            >>> assert record.paid_at is not None
        """
        self.is_paid = True
        self.paid_at = paid_at or datetime.now()

    def __repr__(self):
        """객체의 문자열 표현

        Returns:
            str: 기록 정보를 포함한 문자열

        Example:
            >>> print(record)
            <FixedExpenseRecord(id=1, year=2025, month=10, amount=800000, paid=False)>
        """
        return (
            f"<FixedExpenseRecord(id={self.id}, year={self.year}, "
            f"month={self.month}, amount={self.amount}, paid={self.is_paid})>"
        )
