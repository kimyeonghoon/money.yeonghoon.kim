"""
Pytest 설정 및 공통 Fixtures

이 파일은 pytest 테스트에서 사용할 공통 설정과 픽스처를 정의합니다.
픽스처는 테스트 함수에 자동으로 주입되는 재사용 가능한 리소스입니다.

주요 Fixtures:
- db: 테스트용 독립 데이터베이스 세션
- client: FastAPI 테스트 클라이언트
- test_user: 미리 생성된 테스트 사용자
- test_user_token: 테스트 사용자의 JWT 토큰
- auth_headers: 인증 헤더 (Authorization: Bearer {token})

Fixture Scope:
- function: 각 테스트 함수마다 새로 생성 (기본값)
- class: 테스트 클래스마다 한 번 생성
- module: 모듈(파일)마다 한 번 생성
- session: 전체 테스트 세션에서 한 번만 생성
"""
import pytest
from typing import Generator
from fastapi.testclient import TestClient
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, Session

from app.main import app
from app.database import Base, get_db
from app.core.security import get_password_hash
from app.models.user import User

SQLALCHEMY_DATABASE_URL = "sqlite:///./test.db"

engine = create_engine(
    SQLALCHEMY_DATABASE_URL,
    connect_args={"check_same_thread": False}
)

TestingSessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)


@pytest.fixture(scope="function")
def db() -> Generator[Session, None, None]:
    """테스트용 데이터베이스 세션 픽스처

    각 테스트 함수마다 깨끗한 데이터베이스를 제공합니다.
    테스트 시작 시 테이블을 생성하고, 종료 시 모든 데이터를 삭제합니다.

    Yields:
        Session: 테스트용 SQLAlchemy 세션

    Example:
        def test_create_user(db: Session):
            # db는 자동으로 주입됨
            user = User(email="test@example.com", ...)
            db.add(user)
            db.commit()

    Note:
        - scope="function": 각 테스트마다 새로운 DB (테스트 간 격리)
        - 테스트 종료 시 모든 테이블 DROP (다음 테스트에 영향 없음)
        - Given-When-Then 패턴의 Given 단계에서 주로 사용
    """
    Base.metadata.create_all(bind=engine)
    db = TestingSessionLocal()
    try:
        yield db
    finally:
        db.close()
        Base.metadata.drop_all(bind=engine)


@pytest.fixture(scope="function")
def client(db: Session) -> Generator[TestClient, None, None]:
    """테스트용 FastAPI 클라이언트 픽스처

    FastAPI 애플리케이션을 테스트할 수 있는 HTTP 클라이언트를 제공합니다.
    실제 서버를 실행하지 않고도 API 엔드포인트를 테스트할 수 있습니다.

    Args:
        db (Session): 테스트 DB 세션 (자동 주입)

    Yields:
        TestClient: FastAPI 테스트 클라이언트

    Example:
        def test_register(client: TestClient):
            response = client.post("/api/v1/auth/register", json={...})
            assert response.status_code == 201

    How it works:
        1. app의 get_db 의존성을 테스트 DB로 오버라이드
        2. 모든 API 호출이 테스트 DB를 사용하도록 설정
        3. 테스트 종료 시 오버라이드 해제

    Note:
        - 실제 HTTP 요청이 아닌 ASGI 레벨에서 테스트 (빠름)
        - db 픽스처에 의존하므로 각 테스트마다 깨끗한 DB 사용
    """
    def override_get_db():
        try:
            yield db
        finally:
            pass

    app.dependency_overrides[get_db] = override_get_db

    with TestClient(app) as test_client:
        yield test_client

    app.dependency_overrides.clear()


@pytest.fixture(scope="function")
def test_user(db: Session) -> User:
    """테스트용 사용자 픽스처

    미리 생성된 사용자를 제공하여 인증 테스트를 간편하게 합니다.
    로그인, 프로필 조회 등 기존 사용자가 필요한 테스트에서 사용합니다.

    Args:
        db (Session): 테스트 DB 세션 (자동 주입)

    Returns:
        User: 생성된 테스트 사용자 ORM 객체

    Example:
        def test_get_profile(client: TestClient, test_user: User):
            # test_user는 이미 DB에 저장된 사용자
            # 로그인 테스트
            response = client.post("/api/v1/auth/login", json={
                "username": "testuser",
                "password": "testpassword123"
            })
            assert response.status_code == 200

    User Info:
        - email: test@example.com
        - username: testuser
        - password: testpassword123 (평문)
        - hashed_password: bcrypt 해시
        - full_name: Test User
        - is_active: True

    Note:
        - Given 단계에서 사용 (Given: 테스트 사용자가 있을 때)
        - 비밀번호는 평문이 아닌 해시로 저장 (실제 환경과 동일)
    """
    user = User(
        email="test@example.com",
        username="testuser",
        hashed_password=get_password_hash("testpassword123"),
        full_name="Test User",
        is_active=True
    )

    # RAW SQL: INSERT INTO users (...) VALUES (...)
    db.add(user)
    db.commit()
    db.refresh(user)

    return user


@pytest.fixture(scope="function")
def test_user_token(client: TestClient, test_user: User) -> str:
    """테스트용 인증 토큰 픽스처

    테스트 사용자로 로그인하여 JWT 액세스 토큰을 생성합니다.
    인증이 필요한 엔드포인트 테스트에 사용합니다.

    Args:
        client (TestClient): FastAPI 테스트 클라이언트 (자동 주입)
        test_user (User): 테스트 사용자 (자동 주입)

    Returns:
        str: JWT 액세스 토큰

    Example:
        def test_protected_route(client: TestClient, test_user_token: str):
            # 인증 헤더와 함께 요청
            headers = {"Authorization": f"Bearer {test_user_token}"}
            response = client.get("/api/v1/users/me", headers=headers)
            assert response.status_code == 200

    Note:
        - test_user 픽스처에 의존 (자동으로 사용자 생성 후 토큰 발급)
        - 실제 로그인 API를 호출하여 토큰 생성 (통합 테스트)
        - auth_headers 픽스처와 함께 사용하면 더 편리
    """
    response = client.post(
        "/api/v1/auth/login",
        json={"username": "testuser", "password": "testpassword123"}
    )
    assert response.status_code == 200
    return response.json()["access_token"]


@pytest.fixture(scope="function")
def auth_headers(test_user_token: str) -> dict:
    """인증 헤더 픽스처

    JWT 토큰을 포함한 Authorization 헤더를 제공합니다.
    인증이 필요한 API 호출을 간편하게 합니다.

    Args:
        test_user_token (str): JWT 액세스 토큰 (자동 주입)

    Returns:
        dict: Authorization 헤더
            형식: {"Authorization": "Bearer {token}"}

    Example:
        def test_get_my_profile(client: TestClient, auth_headers: dict):
            # 한 줄로 인증 요청 가능
            response = client.get("/api/v1/users/me", headers=auth_headers)
            assert response.status_code == 200

        # auth_headers 없이 사용하면:
        def test_get_my_profile(client: TestClient, test_user_token: str):
            headers = {"Authorization": f"Bearer {test_user_token}"}
            response = client.get("/api/v1/users/me", headers=headers)

    Note:
        - test_user_token 픽스처를 래핑하여 사용 편의성 향상
        - Given 단계에서 사용 (Given: 인증된 사용자로)
    """
    return {"Authorization": f"Bearer {test_user_token}"}
