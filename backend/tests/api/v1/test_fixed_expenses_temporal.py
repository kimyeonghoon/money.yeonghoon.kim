"""
고정지출 시간 유효성(Temporal Validity) 테스트

시나리오:
- 10월 29일 수정/삭제: 현재 달 기록 수정/삭제됨
- 11월 2일 수정/삭제: 과거 달 기록 보존, 미래부터 적용
"""
import pytest
from datetime import date, datetime
from freezegun import freeze_time
from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.models.user import User
from app.models.fixed_expense import FixedExpense, FixedExpenseRecord


class TestTemporalUpdate:
    """시간 기반 수정 테스트"""

    @pytest.mark.xfail(reason="freezegun과 JWT 토큰 생성 타이밍 이슈 - 추후 수정 예정")
    @freeze_time("2025-10-29")
    def test_update_within_same_month(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """10월 29일에 수정: 현재 달(10월) 기록도 수정됨

        Given: 10월 23일에 생성된 "월세 80만원" 항목 및 10월 기록
        When: 10월 29일에 85만원으로 수정
        Then:
            - 기존 항목은 10월까지만 유효 (valid_until = 2025-10-31)
            - 새 항목 생성 (11월부터 유효, 85만원)
            - 10월 기록 금액 85만원으로 수정됨
        """
        expense = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000,
            valid_from=date(2025, 1, 1),
            valid_until=None
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

        response = client.put(
            f"/api/v1/fixed-expenses/{expense.id}",
            json={"default_amount": 850000},
            headers=auth_headers
        )

        assert response.status_code == 200
        data = response.json()
        assert data["default_amount"] == 850000
        assert data["valid_from"] == "2025-11-01"
        assert data["valid_until"] is None

        db.refresh(expense)
        assert expense.valid_until == date(2025, 10, 31)

        db.refresh(record)
        assert record.amount == 850000

    @pytest.mark.xfail(reason="freezegun과 JWT 토큰 생성 타이밍 이슈 - 추후 수정 예정")
    @freeze_time("2025-11-02")
    def test_update_next_month(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """11월 2일에 수정: 과거 달(10월) 기록 보존

        Given: 10월 기록이 있는 "월세 80만원" 항목
        When: 11월 2일에 85만원으로 수정
        Then:
            - 기존 항목은 10월까지만 유효
            - 새 항목 생성 (11월부터 유효, 85만원)
            - 10월 기록은 80만원 그대로 유지 (과거 불변)
        """
        expense = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000,
            valid_from=date(2025, 1, 1),
            valid_until=None
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        record = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=800000,
            is_paid=True
        )
        db.add(record)
        db.commit()

        response = client.put(
            f"/api/v1/fixed-expenses/{expense.id}",
            json={"default_amount": 850000},
            headers=auth_headers
        )

        assert response.status_code == 200
        data = response.json()
        assert data["default_amount"] == 850000
        assert data["valid_from"] == "2025-11-01"

        db.refresh(expense)
        assert expense.valid_until == date(2025, 10, 31)

        db.refresh(record)
        assert record.amount == 800000


class TestTemporalDelete:
    """시간 기반 삭제 테스트"""

    @pytest.mark.xfail(reason="freezegun과 JWT 토큰 생성 타이밍 이슈 - 추후 수정 예정")
    @freeze_time("2025-10-29")
    def test_delete_within_same_month(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """10월 29일에 삭제: 현재 달(10월) 기록도 삭제됨

        Given: 10월 기록이 있는 "월세" 항목
        When: 10월 29일에 삭제
        Then:
            - 항목은 10월까지만 유효 (valid_until = 2025-10-31)
            - 10월 기록 삭제됨
        """
        expense = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000,
            valid_from=date(2025, 1, 1),
            valid_until=None
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        record = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=800000
        )
        db.add(record)
        db.commit()
        record_id = record.id

        response = client.delete(
            f"/api/v1/fixed-expenses/{expense.id}",
            headers=auth_headers
        )

        assert response.status_code == 204

        db.refresh(expense)
        assert expense.valid_until == date(2025, 10, 31)

        deleted_record = db.query(FixedExpenseRecord).filter(
            FixedExpenseRecord.id == record_id
        ).first()
        assert deleted_record is None

    @pytest.mark.xfail(reason="freezegun과 JWT 토큰 생성 타이밍 이슈 - 추후 수정 예정")
    @freeze_time("2025-11-02")
    def test_delete_next_month(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """11월 2일에 삭제: 과거 달(10월) 기록 보존

        Given: 10월 기록이 있는 "월세" 항목
        When: 11월 2일에 삭제
        Then:
            - 항목은 10월까지만 유효 (valid_until = 2025-10-31)
            - 10월 기록은 그대로 보존 (과거 불변)
        """
        expense = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000,
            valid_from=date(2025, 1, 1),
            valid_until=None
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        record = FixedExpenseRecord(
            fixed_expense_id=expense.id,
            year=2025,
            month=10,
            amount=800000,
            is_paid=True
        )
        db.add(record)
        db.commit()
        record_id = record.id

        response = client.delete(
            f"/api/v1/fixed-expenses/{expense.id}",
            headers=auth_headers
        )

        assert response.status_code == 204

        db.refresh(expense)
        assert expense.valid_until == date(2025, 10, 31)

        preserved_record = db.query(FixedExpenseRecord).filter(
            FixedExpenseRecord.id == record_id
        ).first()
        assert preserved_record is not None
        assert preserved_record.amount == 800000


class TestTemporalQuery:
    """시간 기반 조회 테스트"""

    def test_get_expenses_for_specific_month(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """특정 월의 유효한 고정지출만 조회

        Given:
            - 항목1: 1월~10월 유효 (80만원)
            - 항목2: 11월~ 유효 (85만원)
        When: 10월 조회
        Then: 항목1만 반환
        When: 11월 조회
        Then: 항목2만 반환
        """
        expense1 = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000,
            valid_from=date(2025, 1, 1),
            valid_until=date(2025, 10, 31)
        )
        expense2 = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=850000,
            valid_from=date(2025, 11, 1),
            valid_until=None
        )
        db.add_all([expense1, expense2])
        db.commit()

        response = client.get(
            "/api/v1/fixed-expenses?year=2025&month=10",
            headers=auth_headers
        )
        assert response.status_code == 200
        data = response.json()
        assert len(data) == 1
        assert data[0]["default_amount"] == 800000

        response = client.get(
            "/api/v1/fixed-expenses?year=2025&month=11",
            headers=auth_headers
        )
        assert response.status_code == 200
        data = response.json()
        assert len(data) == 1
        assert data[0]["default_amount"] == 850000
