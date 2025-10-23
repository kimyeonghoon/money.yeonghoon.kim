"""
고정지출 API 테스트
"""
import pytest
from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.models.user import User
from app.models.fixed_expense import FixedExpense, FixedExpenseRecord


class TestCreateFixedExpense:
    """고정지출 항목 생성 API 테스트"""

    def test_create_fixed_expense_success(
        self, client: TestClient, auth_headers: dict
    ):
        """고정지출 항목 생성 성공

        Given: 인증된 사용자
        When: 고정지출 항목 생성 요청
        Then: 201 Created, 항목 정보 반환
        """
        response = client.post(
            "/api/v1/fixed-expenses",
            json={
                "name": "월세",
                "default_amount": 800000,
                "is_fixed_amount": True,
                "expected_payment_day": 5
            },
            headers=auth_headers
        )

        assert response.status_code == 201
        data = response.json()
        assert data["name"] == "월세"
        assert data["default_amount"] == 800000
        assert data["is_fixed_amount"] is True
        assert data["expected_payment_day"] == 5
        assert "id" in data

    def test_create_fixed_expense_unauthorized(self, client: TestClient):
        """인증 없이 고정지출 항목 생성 시도

        Given: 인증 토큰 없음
        When: 고정지출 항목 생성 요청
        Then: 403 Forbidden
        """
        response = client.post(
            "/api/v1/fixed-expenses",
            json={"name": "월세"}
        )

        assert response.status_code == 403

    def test_create_fixed_expense_invalid_data(
        self, client: TestClient, auth_headers: dict
    ):
        """잘못된 데이터로 고정지출 항목 생성 시도

        Given: 인증된 사용자
        When: 빈 이름으로 생성 요청
        Then: 422 Unprocessable Entity
        """
        response = client.post(
            "/api/v1/fixed-expenses",
            json={"name": ""},
            headers=auth_headers
        )

        assert response.status_code == 422


class TestGetFixedExpenses:
    """고정지출 항목 목록 조회 API 테스트"""

    def test_get_fixed_expenses_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """고정지출 항목 목록 조회 성공

        Given: 사용자의 고정지출 항목이 있을 때
        When: 목록 조회 요청
        Then: 200 OK, 항목 리스트 반환
        """
        expense1 = FixedExpense(user_id=test_user.id, name="월세")
        expense2 = FixedExpense(user_id=test_user.id, name="전기세")
        db.add_all([expense1, expense2])
        db.commit()

        response = client.get("/api/v1/fixed-expenses", headers=auth_headers)

        assert response.status_code == 200
        data = response.json()
        assert len(data) == 2
        assert any(item["name"] == "월세" for item in data)
        assert any(item["name"] == "전기세" for item in data)

    def test_get_fixed_expenses_empty(
        self, client: TestClient, auth_headers: dict
    ):
        """고정지출 항목이 없을 때 조회

        Given: 고정지출 항목 없음
        When: 목록 조회 요청
        Then: 200 OK, 빈 리스트 반환
        """
        response = client.get("/api/v1/fixed-expenses", headers=auth_headers)

        assert response.status_code == 200
        assert response.json() == []


class TestGetFixedExpense:
    """고정지출 항목 상세 조회 API 테스트"""

    def test_get_fixed_expense_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """고정지출 항목 상세 조회 성공

        Given: 사용자의 고정지출 항목이 있을 때
        When: 상세 조회 요청
        Then: 200 OK, 항목 정보 반환
        """
        expense = FixedExpense(
            user_id=test_user.id,
            name="월세",
            default_amount=800000
        )
        db.add(expense)
        db.commit()
        db.refresh(expense)

        response = client.get(
            f"/api/v1/fixed-expenses/{expense.id}",
            headers=auth_headers
        )

        assert response.status_code == 200
        data = response.json()
        assert data["id"] == expense.id
        assert data["name"] == "월세"

    def test_get_fixed_expense_not_found(
        self, client: TestClient, auth_headers: dict
    ):
        """존재하지 않는 항목 조회

        Given: 존재하지 않는 항목 ID
        When: 상세 조회 요청
        Then: 404 Not Found
        """
        response = client.get(
            "/api/v1/fixed-expenses/999999",
            headers=auth_headers
        )

        assert response.status_code == 404


class TestUpdateFixedExpense:
    """고정지출 항목 수정 API 테스트"""

    def test_update_fixed_expense_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """고정지출 항목 수정 성공

        Given: 사용자의 고정지출 항목이 있을 때
        When: 수정 요청
        Then: 200 OK, 수정된 정보 반환
        """
        expense = FixedExpense(user_id=test_user.id, name="월세")
        db.add(expense)
        db.commit()
        db.refresh(expense)

        response = client.put(
            f"/api/v1/fixed-expenses/{expense.id}",
            json={"name": "수정된 월세", "default_amount": 850000},
            headers=auth_headers
        )

        assert response.status_code == 200
        data = response.json()
        assert data["name"] == "수정된 월세"
        assert data["default_amount"] == 850000


class TestDeleteFixedExpense:
    """고정지출 항목 삭제 API 테스트"""

    def test_delete_fixed_expense_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """고정지출 항목 삭제 성공

        Given: 사용자의 고정지출 항목이 있을 때
        When: 삭제 요청
        Then: 204 No Content
        """
        expense = FixedExpense(user_id=test_user.id, name="월세")
        db.add(expense)
        db.commit()
        db.refresh(expense)

        response = client.delete(
            f"/api/v1/fixed-expenses/{expense.id}",
            headers=auth_headers
        )

        assert response.status_code == 204

        deleted = db.query(FixedExpense).filter(
            FixedExpense.id == expense.id
        ).first()
        assert deleted is None


class TestCreateFixedExpenseRecord:
    """월별 기록 생성 API 테스트"""

    def test_create_record_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """월별 기록 생성 성공

        Given: 고정지출 항목이 있을 때
        When: 월별 기록 생성 요청
        Then: 201 Created
        """
        expense = FixedExpense(user_id=test_user.id, name="월세")
        db.add(expense)
        db.commit()
        db.refresh(expense)

        response = client.post(
            f"/api/v1/fixed-expenses/{expense.id}/records",
            json={
                "year": 2025,
                "month": 10,
                "amount": 800000,
                "is_paid": False
            },
            headers=auth_headers
        )

        assert response.status_code == 201
        data = response.json()
        assert data["year"] == 2025
        assert data["month"] == 10
        assert data["amount"] == 800000


class TestMarkAsPaid:
    """지출 완료 처리 API 테스트"""

    def test_mark_as_paid_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """지출 완료 처리 성공

        Given: 미지출 기록이 있을 때
        When: 지출 완료 처리 요청
        Then: 200 OK, is_paid=True
        """
        expense = FixedExpense(user_id=test_user.id, name="월세")
        db.add(expense)
        db.commit()

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

        response = client.put(
            f"/api/v1/fixed-expenses/records/{record.id}/mark-paid",
            headers=auth_headers
        )

        assert response.status_code == 200
        data = response.json()
        assert data["is_paid"] is True
        assert data["paid_at"] is not None


class TestGetMonthlySummary:
    """월별 요약 조회 API 테스트"""

    def test_get_monthly_summary_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """월별 요약 조회 성공

        Given: 월별 기록이 있을 때
        When: 월별 요약 조회 요청
        Then: 200 OK, 요약 정보 반환
        """
        expense1 = FixedExpense(user_id=test_user.id, name="월세")
        expense2 = FixedExpense(user_id=test_user.id, name="전기세")
        db.add_all([expense1, expense2])
        db.commit()

        record1 = FixedExpenseRecord(
            fixed_expense_id=expense1.id,
            year=2025,
            month=10,
            amount=800000,
            is_paid=True
        )
        record2 = FixedExpenseRecord(
            fixed_expense_id=expense2.id,
            year=2025,
            month=10,
            amount=45000,
            is_paid=False
        )
        db.add_all([record1, record2])
        db.commit()

        response = client.get(
            "/api/v1/fixed-expenses/summary/2025/10",
            headers=auth_headers
        )

        assert response.status_code == 200
        data = response.json()
        assert data["year"] == 2025
        assert data["month"] == 10
        assert data["total_expected"] == 845000
        assert data["total_paid"] == 800000
        assert data["total_unpaid"] == 45000
        assert data["paid_count"] == 1
        assert data["unpaid_count"] == 1
