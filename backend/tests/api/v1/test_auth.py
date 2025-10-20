"""
인증 API 테스트
"""
import pytest
from fastapi.testclient import TestClient
from sqlalchemy.orm import Session

from app.models.user import User
from app.models.verification_code import VerificationCode


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

    def test_register_short_password(self, client: TestClient):
        """비밀번호 길이가 8자 미만일 때 실패"""
        # Given: 8자 미만의 비밀번호
        user_data = {
            "email": "newuser@example.com",
            "username": "newuser",
            "password": "short"
        }

        # When: 회원가입 요청
        response = client.post("/api/v1/auth/register", json=user_data)

        # Then: 422 Unprocessable Entity
        assert response.status_code == 422
        error_detail = response.json()["detail"]
        assert any(
            "password" in str(err).lower() and "8" in str(err)
            for err in error_detail
        )


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

    def test_login_inactive_user(
        self, client: TestClient, db: Session, test_user: User
    ):
        """비활성 사용자로 로그인 실패"""
        # Given: 비활성화된 사용자
        test_user.is_active = False
        db.commit()

        login_data = {
            "username": "testuser",
            "password": "testpassword123"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/login", json=login_data)

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "inactive" in response.json()["detail"].lower()

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


class TestRefreshToken:
    """리프레시 토큰 테스트"""

    def test_refresh_token_success(
        self, client: TestClient, test_user: User
    ):
        """유효한 리프레시 토큰으로 액세스 토큰 갱신 성공"""
        # Given: 유효한 리프레시 토큰
        from app.core.security import create_refresh_token

        refresh_token = create_refresh_token(subject=test_user.id)

        # When: 토큰 갱신 요청
        response = client.post(
            "/api/v1/auth/refresh",
            json={"refresh_token": refresh_token}
        )

        # Then: 200 OK, 새 액세스 토큰 반환
        assert response.status_code == 200
        data = response.json()
        assert "access_token" in data
        assert data["token_type"] == "bearer"

    def test_refresh_token_invalid(self, client: TestClient):
        """잘못된 리프레시 토큰으로 갱신 실패"""
        # Given: 잘못된 토큰
        refresh_data = {"refresh_token": "invalid-token"}

        # When: 토큰 갱신 요청
        response = client.post("/api/v1/auth/refresh", json=refresh_data)

        # Then: 401 Unauthorized
        assert response.status_code == 401

    def test_refresh_token_access_token_type(self, client: TestClient, test_user: User):
        """액세스 토큰으로 갱신 시도 (type=refresh 아님)"""
        # Given: 액세스 토큰 (type이 refresh가 아님)
        from app.core.security import create_access_token

        access_token = create_access_token(subject=test_user.id)

        # When: 토큰 갱신 요청
        response = client.post(
            "/api/v1/auth/refresh",
            json={"refresh_token": access_token}
        )

        # Then: 401 Unauthorized
        assert response.status_code == 401

    def test_refresh_token_inactive_user(
        self, client: TestClient, db: Session, test_user: User
    ):
        """비활성 사용자의 리프레시 토큰으로 갱신 실패"""
        # Given: 비활성화된 사용자의 리프레시 토큰
        from app.core.security import create_refresh_token

        refresh_token = create_refresh_token(subject=test_user.id)
        test_user.is_active = False
        db.commit()

        # When: 토큰 갱신 요청
        response = client.post(
            "/api/v1/auth/refresh",
            json={"refresh_token": refresh_token}
        )

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "inactive" in response.json()["detail"].lower()

    def test_refresh_token_nonexistent_user(self, client: TestClient):
        """존재하지 않는 사용자 ID로 갱신 실패"""
        # Given: 존재하지 않는 사용자 ID로 토큰 생성
        from app.core.security import create_refresh_token

        refresh_token = create_refresh_token(subject=999999)

        # When: 토큰 갱신 요청
        response = client.post(
            "/api/v1/auth/refresh",
            json={"refresh_token": refresh_token}
        )

        # Then: 401 Unauthorized
        assert response.status_code == 401


class TestTwoFactorLogin:
    """2FA 로그인 테스트"""

    def test_request_login_success(self, client: TestClient, test_user: User, db: Session):
        """2FA 로그인 요청 성공 - 인증 코드 발송"""
        # Given: 올바른 자격 증명
        login_data = {
            "username": "testuser",
            "password": "testpassword123"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/request-login", json=login_data)

        # Then: 200 OK, 메시지 반환
        assert response.status_code == 200
        data = response.json()
        assert "message" in data
        assert "verification code" in data["message"].lower()

        # 인증 코드가 DB에 생성되었는지 확인
        code = VerificationCode.get_latest_for_user(db, test_user.id)
        assert code is not None
        assert len(code.code) == 6
        assert code.is_used is False
        assert not code.is_expired()

    def test_request_login_wrong_password(self, client: TestClient, test_user: User):
        """2FA 로그인 요청 실패 - 잘못된 비밀번호"""
        # Given: 잘못된 비밀번호
        login_data = {
            "username": "testuser",
            "password": "wrongpassword"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/request-login", json=login_data)

        # Then: 401 Unauthorized
        assert response.status_code == 401
        assert "incorrect" in response.json()["detail"].lower()

    def test_request_login_nonexistent_user(self, client: TestClient):
        """2FA 로그인 요청 실패 - 존재하지 않는 사용자"""
        # Given: 존재하지 않는 사용자
        login_data = {
            "username": "nonexistent",
            "password": "password123"
        }

        # When: 로그인 요청
        response = client.post("/api/v1/auth/request-login", json=login_data)

        # Then: 401 Unauthorized
        assert response.status_code == 401

    def test_verify_login_success(self, client: TestClient, test_user: User, db: Session):
        """2FA 로그인 검증 성공 - 토큰 발급"""
        # Given: 인증 코드 발급
        login_data = {
            "username": "testuser",
            "password": "testpassword123"
        }
        client.post("/api/v1/auth/request-login", json=login_data)

        # 생성된 코드 조회
        code_obj = VerificationCode.get_latest_for_user(db, test_user.id)
        assert code_obj is not None

        # When: 인증 코드로 검증 요청
        verify_data = {
            "username": "testuser",
            "code": code_obj.code
        }
        response = client.post("/api/v1/auth/verify-login", json=verify_data)

        # Then: 200 OK, 토큰 반환
        assert response.status_code == 200
        data = response.json()
        assert "access_token" in data
        assert "refresh_token" in data
        assert data["token_type"] == "bearer"

        # 코드가 사용 처리되었는지 확인
        db.refresh(code_obj)
        assert code_obj.is_used is True

    def test_verify_login_wrong_code(self, client: TestClient, test_user: User, db: Session):
        """2FA 로그인 검증 실패 - 잘못된 코드"""
        # Given: 인증 코드 발급 후 잘못된 코드로 검증
        client.post("/api/v1/auth/request-login", json={
            "username": "testuser",
            "password": "testpassword123"
        })

        # When: 잘못된 코드로 검증 요청
        verify_data = {
            "username": "testuser",
            "code": "000000"
        }
        response = client.post("/api/v1/auth/verify-login", json=verify_data)

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "invalid" in response.json()["detail"].lower()

    def test_verify_login_expired_code(self, client: TestClient, test_user: User, db: Session):
        """2FA 로그인 검증 실패 - 만료된 코드"""
        # Given: 만료된 인증 코드 생성
        from datetime import datetime, timedelta
        import random
        expired_code = VerificationCode(
            user_id=test_user.id,
            code=str(random.randint(100000, 999999)),
            expires_at=datetime.now() - timedelta(minutes=1),
            is_used=False
        )
        db.add(expired_code)
        db.commit()
        db.refresh(expired_code)

        # When: 만료된 코드로 검증 요청
        verify_data = {
            "username": "testuser",
            "code": expired_code.code
        }
        response = client.post("/api/v1/auth/verify-login", json=verify_data)

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "expired" in response.json()["detail"].lower()

    def test_verify_login_already_used_code(self, client: TestClient, test_user: User, db: Session):
        """2FA 로그인 검증 실패 - 이미 사용된 코드"""
        # Given: 이미 사용된 인증 코드
        from datetime import datetime, timedelta
        import random
        used_code = VerificationCode(
            user_id=test_user.id,
            code=str(random.randint(100000, 999999)),
            expires_at=datetime.now() + timedelta(minutes=5),
            is_used=True
        )
        db.add(used_code)
        db.commit()
        db.refresh(used_code)

        # When: 이미 사용된 코드로 검증 요청
        verify_data = {
            "username": "testuser",
            "code": used_code.code
        }
        response = client.post("/api/v1/auth/verify-login", json=verify_data)

        # Then: 400 Bad Request
        assert response.status_code == 400
        assert "invalid" in response.json()["detail"].lower()
