"""
사용자 API 테스트
"""
import pytest
from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.models.user import User


class TestGetCurrentUser:
    """현재 사용자 정보 조회 테스트"""

    def test_get_current_user_success(
        self, client: TestClient, auth_headers: dict, test_user: User
    ):
        """인증된 사용자 정보 조회 성공"""
        # Given: 인증된 사용자

        # When: 현재 사용자 정보 요청
        response = client.get("/api/v1/users/me", headers=auth_headers)

        # Then: 200 OK, 사용자 정보 반환
        assert response.status_code == 200
        data = response.json()
        assert data["email"] == test_user.email
        assert data["username"] == test_user.username
        assert "hashed_password" not in data

    def test_get_current_user_unauthorized(self, client: TestClient):
        """인증 없이 사용자 정보 조회 시도"""
        # Given: 인증 토큰 없음

        # When: 현재 사용자 정보 요청
        response = client.get("/api/v1/users/me")

        # Then: 403 Forbidden (FastAPI HTTPBearer 기본 동작)
        assert response.status_code == 403

    def test_get_current_user_invalid_token(self, client: TestClient):
        """잘못된 토큰으로 사용자 정보 조회 시도"""
        # Given: 잘못된 토큰
        headers = {"Authorization": "Bearer invalid-token"}

        # When: 현재 사용자 정보 요청
        response = client.get("/api/v1/users/me", headers=headers)

        # Then: 401 Unauthorized
        assert response.status_code == 401


class TestUpdateCurrentUser:
    """현재 사용자 정보 수정 테스트"""

    def test_update_user_success(
        self, client: TestClient, auth_headers: dict, db: Session, test_user: User
    ):
        """사용자 정보 수정 성공"""
        # Given: 수정할 데이터
        update_data = {
            "full_name": "Updated Name",
            "email": "updated@example.com"
        }

        # When: 사용자 정보 수정 요청
        response = client.put("/api/v1/users/me", headers=auth_headers, json=update_data)

        # Then: 200 OK, 수정된 정보 반환
        assert response.status_code == 200
        data = response.json()
        assert data["full_name"] == update_data["full_name"]
        assert data["email"] == update_data["email"]

        # DB에서 확인
        db.refresh(test_user)
        assert test_user.full_name == update_data["full_name"]
        assert test_user.email == update_data["email"]

    def test_update_user_duplicate_email(
        self, client: TestClient, auth_headers: dict, db: Session
    ):
        """다른 사용자의 이메일로 수정 시도"""
        # Given: 다른 사용자 생성
        from app.core.security import get_password_hash
        other_user = User(
            email="other@example.com",
            username="otheruser",
            hashed_password=get_password_hash("password")
        )
        db.add(other_user)
        db.commit()

        # When: 다른 사용자의 이메일로 수정 시도
        update_data = {"email": "other@example.com"}
        response = client.put("/api/v1/users/me", headers=auth_headers, json=update_data)

        # Then: 400 Bad Request
        assert response.status_code == 400

    def test_update_user_short_password(
        self, client: TestClient, auth_headers: dict
    ):
        """비밀번호를 8자 미만으로 변경 시도"""
        # Given: 8자 미만의 비밀번호
        update_data = {"password": "short"}

        # When: 비밀번호 변경 요청
        response = client.put("/api/v1/users/me", headers=auth_headers, json=update_data)

        # Then: 422 Unprocessable Entity
        assert response.status_code == 422
        error_detail = response.json()["detail"]
        assert any(
            "password" in str(err).lower() and "8" in str(err)
            for err in error_detail
        )


class TestGetUserById:
    """특정 사용자 정보 조회 테스트"""

    def test_get_user_by_id_success(
        self, client: TestClient, auth_headers: dict, test_user: User
    ):
        """특정 사용자 정보 조회 성공"""
        # Given: 존재하는 사용자 ID

        # When: 사용자 정보 요청
        response = client.get(f"/api/v1/users/{test_user.id}", headers=auth_headers)

        # Then: 200 OK
        assert response.status_code == 200
        data = response.json()
        assert data["id"] == test_user.id

    def test_get_user_by_id_not_found(
        self, client: TestClient, auth_headers: dict
    ):
        """존재하지 않는 사용자 조회"""
        # Given: 존재하지 않는 사용자 ID
        nonexistent_id = 99999

        # When: 사용자 정보 요청
        response = client.get(f"/api/v1/users/{nonexistent_id}", headers=auth_headers)

        # Then: 404 Not Found
        assert response.status_code == 404
