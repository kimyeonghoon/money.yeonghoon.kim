"""
FixedExpense 모델 테스트

고정지출 항목 및 월별 기록 모델을 테스트합니다.
"""
import pytest
from datetime import datetime
from sqlalchemy.orm import Session


class TestFixedExpenseModel:
    """FixedExpense 모델 테스트 클래스"""

    def test_create_fixed_expense(self, db: Session, test_user):
        """고정지출 항목 생성 테스트

        Given: 사용자가 있을 때
        When: 고정지출 항목을 생성하면
        Then: DB에 저장되어야 함
        """
        from app.models.fixed_expense import FixedExpense

        expense = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000,
            is_fixed_amount=True,
            expected_payment_day=5
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        assert expense.id is not None
        assert expense.user_id == test_user.id
        assert expense.name == "월세"
        assert expense.default_amount == 800000
        assert expense.is_fixed_amount is True
        assert expense.expected_payment_day == 5
        assert expense.is_active is True

    def test_create_variable_expense(self, db: Session, test_user):
        """변동 금액 고정지출 생성 테스트

        Given: 사용자가 있을 때
        When: 변동 금액 고정지출을 생성하면
        Then: is_fixed_amount가 False여야 함
        """
        from app.models.fixed_expense import FixedExpense

        expense = FixedExpense(
            user_id=test_user.id,
            name="전기세",
            default_amount=None,
            is_fixed_amount=False,
            expected_payment_day=15
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        assert expense.id is not None
        assert expense.is_fixed_amount is False
        assert expense.default_amount is None

    def test_get_active_expenses(self, db: Session, test_user):
        """활성 고정지출 항목 조회 테스트

        Given: 활성/비활성 고정지출이 있을 때
        When: 활성 항목만 조회하면
        Then: is_active=True인 항목만 반환되어야 함
        """
        from app.models.fixed_expense import FixedExpense

        active = FixedExpense(
            user_id=test_user.id,
            name="활성",
            is_active=True
        )
        inactive = FixedExpense(
            user_id=test_user.id,
            name="비활성",
            is_active=False
        )
        db.add(active)
        db.add(inactive)
        db.commit()

        # RAW SQL: SELECT * FROM fixed_expenses WHERE user_id = ? AND is_active = True
        result = db.query(FixedExpense).filter(
            FixedExpense.user_id == test_user.id,
            FixedExpense.is_active == True
        ).all()

        assert len(result) == 1
        assert result[0].name == "활성"


class TestFixedExpenseRecordModel:
    """FixedExpenseRecord 모델 테스트 클래스"""

    def test_create_expense_record(self, db: Session, test_user):
        """월별 고정지출 기록 생성 테스트

        Given: 고정지출 항목이 있을 때
        When: 월별 기록을 생성하면
        Then: DB에 저장되어야 함
        """
        from app.models.fixed_expense import FixedExpense, FixedExpenseRecord

        expense = FixedExpense(
            user_id=test_user.id,
            name="월세"
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        record = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=800000,
            is_paid=False
        )
        db.add(record)
        db.commit()
        db.refresh(record)

        assert record.id is not None
        assert record.fixed_expense_id == expense.id
        assert record.year == 2025
        assert record.month == 10
        assert record.amount == 800000
        assert record.is_paid is False
        assert record.paid_at is None

    def test_mark_as_paid(self, db: Session, test_user):
        """지출 완료 처리 테스트

        Given: 미지출 기록이 있을 때
        When: 지출 완료 처리하면
        Then: is_paid=True, paid_at에 일자가 설정되어야 함
        """
        from app.models.fixed_expense import FixedExpense, FixedExpenseRecord

        expense = FixedExpense(user_id=test_user.id, name="Netflix")
        db.add(expense)
        db.commit()

        record = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=13500,
            is_paid=False
        )
        db.add(record)
        db.commit()
        db.refresh(record)

        record.mark_as_paid()
        db.commit()
        db.refresh(record)

        assert record.is_paid is True
        assert record.paid_at is not None

    def test_get_monthly_records(self, db: Session, test_user):
        """특정 월의 모든 기록 조회 테스트

        Given: 여러 월의 기록이 있을 때
        When: 특정 월의 기록을 조회하면
        Then: 해당 월의 기록만 반환되어야 함
        """
        from app.models.fixed_expense import FixedExpense, FixedExpenseRecord

        expense1 = FixedExpense(user_id=test_user.id, name="월세")
        expense2 = FixedExpense(user_id=test_user.id, name="전기세")
        db.add_all([expense1, expense2])
        db.commit()

        oct_record1 = FixedExpenseRecord(
            fixed_expense_id=expense1.id,
            year=2025,
            month=10,
            amount=800000
        )
        oct_record2 = FixedExpenseRecord(
            fixed_expense_id=expense2.id,
            year=2025,
            month=10,
            amount=45000
        )
        sep_record = FixedExpenseRecord(
            fixed_expense_id=expense1.id,
            year=2025,
            month=9,
            amount=800000
        )
        db.add_all([oct_record1, oct_record2, sep_record])
        db.commit()

        # RAW SQL: SELECT * FROM fixed_expense_records WHERE year = 2025 AND month = 10
        records = db.query(FixedExpenseRecord).filter(
            FixedExpenseRecord.year == 2025,
            FixedExpenseRecord.month == 10
        ).all()

        assert len(records) == 2
        assert all(r.month == 10 for r in records)

    def test_expense_record_relationship(self, db: Session, test_user):
        """고정지출 항목과 기록의 관계 테스트

        Given: 고정지출 항목과 기록이 있을 때
        When: relationship을 통해 접근하면
        Then: 양방향으로 접근 가능해야 함
        """
        from app.models.fixed_expense import FixedExpense, FixedExpenseRecord

        expense = FixedExpense(user_id=test_user.id, name="월세")
        db.add(expense)
        db.commit()
        db.refresh(expense)

        record1 = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=9,
            amount=800000
        )
        record2 = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=800000
        )
        db.add_all([record1, record2])
        db.commit()
        db.refresh(expense)

        assert len(expense.records) == 2
        assert record1.fixed_expense.name == "월세"
        assert record2.fixed_expense.name == "월세"

    def test_unique_year_month_per_expense(self, db: Session, test_user):
        """동일 항목의 월별 기록 중복 방지 테스트

        Given: 고정지출 항목이 있을 때
        When: 같은 년/월에 중복 기록 생성을 시도하면
        Then: 에러가 발생해야 함
        """
        from app.models.fixed_expense import FixedExpense, FixedExpenseRecord
        from sqlalchemy.exc import IntegrityError

        expense = FixedExpense(user_id=test_user.id, name="월세")
        db.add(expense)
        db.commit()

        record1 = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=800000
        )
        db.add(record1)
        db.commit()

        record2 = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=850000
        )
        db.add(record2)

        with pytest.raises(IntegrityError):
            db.commit()
