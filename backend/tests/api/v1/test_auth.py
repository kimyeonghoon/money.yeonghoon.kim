"""
인증 API 테스트
"""
import pytest
from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.models.user import User


class TestUserRegistration:
    """사용자 등록 테스트"""

    def test_register_new_user_success(self, client: TestClient, db: Session):
        """새 사용자 등록 성공"""
        # Given: 새로운 사용자 데이터
        user_data = {
            "email": "newuser@example.com",
            "username": "newuser",
            "password": "password123",
            "full_name": "New User"
        }

        # When: 회원가입 요청
        response = client.post("/api/v1/auth/register", json=user_data)

        # Then: 201 Created 응답
        assert response.status_code == 201
        data = response.json()
        assert data["email"] == user_data["email"]
        assert data["username"] == user_data["username"]
        assert "id" in data
        assert "hashed_password" not in data

        # DB에 저장되었는지 확인
        user = db.query(User).filter(User.email == user_data["email"]).first()
        assert user is not None
        assert user.username == user_data["username"]

    def test_register_duplicate_email(self, client: TestClient, test_user: User):
        """중복 이메일로 등록 시도 시 실패"""
        # Given: 이미 존재하는 이메일
        user_data = {
            "email": test_user.email,
            "username": "differentuser",
            "password": "password123"
        }

        # When: 회원가입 요청
        response = client.post("/api/v1/auth/register", json=user_data)

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "already registered" in response.json()["detail"].lower()

    def test_register_duplicate_username(self, client: TestClient, test_user: User):
        """중복 사용자명으로 등록 시도 시 실패"""
        # Given: 이미 존재하는 사용자명
        user_data = {
            "email": "different@example.com",
            "username": test_user.username,
            "password": "password123"
        }

        # When: 회원가입 요청
        response = client.post("/api/v1/auth/register", json=user_data)

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "already taken" in response.json()["detail"].lower()

    def test_register_invalid_email(self, client: TestClient):
        """잘못된 이메일 형식으로 등록 시도"""
        # Given: 잘못된 이메일
        user_data = {
            "email": "not-an-email",
            "username": "newuser",
            "password": "password123"
        }

        # When: 회원가입 요청
        response = client.post("/api/v1/auth/register", json=user_data)

        # Then: 422 Unprocessable Entity
        assert response.status_code == 422


class TestUserLogin:
    """사용자 로그인 테스트"""

    def test_login_success(self, client: TestClient, test_user: User):
        """로그인 성공"""
        # Given: 올바른 자격 증명
        login_data = {
            "username": "testuser",
            "password": "testpassword123"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/login", json=login_data)

        # Then: 200 OK, 토큰 반환
        assert response.status_code == 200
        data = response.json()
        assert "access_token" in data
        assert "refresh_token" in data
        assert data["token_type"] == "bearer"

    def test_login_with_email(self, client: TestClient, test_user: User):
        """이메일로 로그인 성공"""
        # Given: 이메일을 username으로 사용
        login_data = {
            "username": "test@example.com",
            "password": "testpassword123"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/login", json=login_data)

        # Then: 200 OK
        assert response.status_code == 200

    def test_login_wrong_password(self, client: TestClient, test_user: User):
        """잘못된 비밀번호로 로그인 실패"""
        # Given: 잘못된 비밀번호
        login_data = {
            "username": "testuser",
            "password": "wrongpassword"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/login", json=login_data)

        # Then: 401 Unauthorized
        assert response.status_code == 401

    def test_login_nonexistent_user(self, client: TestClient):
        """존재하지 않는 사용자로 로그인 실패"""
        # Given: 존재하지 않는 사용자
        login_data = {
            "username": "nonexistent",
            "password": "password123"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/login", json=login_data)

        # Then: 401 Unauthorized
        assert response.status_code == 401
