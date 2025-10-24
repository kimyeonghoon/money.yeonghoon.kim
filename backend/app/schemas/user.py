"""
Pydantic 스키마 정의

이 모듈은 API 요청/응답 데이터의 검증 및 직렬화를 위한 Pydantic 스키마를 정의합니다.

스키마의 역할:
1. 요청 데이터 검증: 클라이언트가 보낸 데이터가 올바른 형식인지 자동 검증
2. 응답 데이터 직렬화: DB 모델을 JSON으로 변환
3. API 문서 자동 생성: FastAPI가 이 스키마를 바탕으로 OpenAPI 문서 생성

주요 차이점:
- models/user.py: SQLAlchemy ORM 모델 (DB 테이블 구조)
- schemas/user.py: Pydantic 스키마 (API 입출력 데이터 구조)
"""

from pydantic import BaseModel, EmailStr, Field, field_validator, ConfigDict
from typing import Optional
from datetime import datetime
from app.core.constants import (
    MIN_PASSWORD_LENGTH,
    MAX_PASSWORD_LENGTH,
    MIN_USERNAME_LENGTH,
    MAX_USERNAME_LENGTH,
)


class UserBase(BaseModel):
    """User 스키마 기본 클래스

    여러 스키마에서 공통으로 사용하는 필드를 정의합니다.
    상속을 통해 코드 중복을 줄입니다.

    Note:
        - 모든 필드가 Optional: 부분 업데이트 등에서 사용
        - password 필드는 제외: 보안상 일반 조회 시 노출 금지
    """

    email: Optional[EmailStr] = None
    username: Optional[str] = None
    full_name: Optional[str] = None
    is_active: Optional[bool] = True
    is_superuser: Optional[bool] = False


class UserCreate(BaseModel):
    """회원가입 요청 스키마

    새 사용자를 생성할 때 필요한 필드를 정의합니다.
    POST /api/v1/auth/register 엔드포인트에서 사용됩니다.

    Attributes:
        email (EmailStr): 이메일 주소 (형식 자동 검증)
        username (str): 사용자명 (3-100자)
        password (str): 비밀번호 (8-100자, 평문으로 전송)
        full_name (str, optional): 실명

    Validation:
        - email: 이메일 형식 검증 (예: user@example.com)
        - username: 최소 3자, 최대 100자
        - password: 최소 8자, 최대 100자 (서버에서 해시 후 저장)

    Example:
        {
            "email": "user@example.com",
            "username": "testuser",
            "password": "securepass123",
            "full_name": "홍길동"
        }

    Note:
        - password는 평문으로 전송되므로 HTTPS 필수!
        - 서버에서 bcrypt로 해시 후 hashed_password로 저장
    """

    email: EmailStr
    username: str = Field(..., min_length=MIN_USERNAME_LENGTH, max_length=MAX_USERNAME_LENGTH)
    password: str = Field(..., min_length=MIN_PASSWORD_LENGTH, max_length=MAX_PASSWORD_LENGTH)
    full_name: Optional[str] = None

    @field_validator('password')
    @classmethod
    def validate_password(cls, v: str) -> str:
        """비밀번호 추가 검증

        Args:
            v: 검증할 비밀번호

        Returns:
            str: 검증된 비밀번호

        Raises:
            ValueError: 비밀번호가 최소 길이 미만일 경우
        """
        if len(v) < MIN_PASSWORD_LENGTH:
            raise ValueError(f'Password must be at least {MIN_PASSWORD_LENGTH} characters')
        if len(v) > MAX_PASSWORD_LENGTH:
            raise ValueError(f'Password must be at most {MAX_PASSWORD_LENGTH} characters')
        return v


class UserUpdate(BaseModel):
    """사용자 정보 수정 요청 스키마

    기존 사용자의 정보를 수정할 때 사용합니다.
    PUT /api/v1/users/me 엔드포인트에서 사용됩니다.

    Attributes:
        email (EmailStr, optional): 새 이메일 주소
        username (str, optional): 새 사용자명
        password (str, optional): 새 비밀번호
        full_name (str, optional): 새 실명
        is_active (bool, optional): 활성 상태

    Note:
        - 모든 필드가 Optional: 원하는 필드만 수정 가능 (부분 업데이트)
        - exclude_unset=True와 함께 사용하여 전송된 필드만 업데이트
        - password를 변경하면 서버에서 재해시 후 저장

    Example (이메일만 변경):
        {
            "email": "newemail@example.com"
        }

    Example (비밀번호와 실명 변경):
        {
            "password": "newsecurepass456",
            "full_name": "김철수"
        }
    """

    email: Optional[EmailStr] = None
    username: Optional[str] = None
    password: Optional[str] = None
    full_name: Optional[str] = None
    is_active: Optional[bool] = None

    @field_validator('password')
    @classmethod
    def validate_password(cls, v: Optional[str]) -> Optional[str]:
        """비밀번호 업데이트 시 검증

        Args:
            v: 검증할 비밀번호 (Optional)

        Returns:
            Optional[str]: 검증된 비밀번호 또는 None

        Raises:
            ValueError: 비밀번호가 제공되었지만 최소 길이 미만일 경우
        """
        if v is not None:
            if len(v) < MIN_PASSWORD_LENGTH:
                raise ValueError(f'Password must be at least {MIN_PASSWORD_LENGTH} characters')
            if len(v) > MAX_PASSWORD_LENGTH:
                raise ValueError(f'Password must be at most {MAX_PASSWORD_LENGTH} characters')
        return v


class UserInDB(UserBase):
    """데이터베이스에서 가져온 User 스키마

    ORM 모델(User)을 JSON으로 변환할 때 사용하는 기본 스키마입니다.

    Attributes:
        id (int): 사용자 ID (DB에서 자동 생성)
        created_at (datetime): 생성 시각
        updated_at (datetime, optional): 마지막 수정 시각

    Config:
        from_attributes: True로 설정 시 ORM 모델의 속성을 자동으로 읽음
            (Pydantic v2에서 orm_mode 대신 사용)

    Note:
        - UserBase의 모든 필드 상속 (email, username, full_name 등)
        - hashed_password는 제외: 보안상 API 응답에 포함하지 않음
    """

    id: int
    created_at: datetime
    updated_at: Optional[datetime] = None

    model_config = ConfigDict(from_attributes=True)


class User(UserInDB):
    """API 응답용 User 스키마

    GET /api/v1/users/me 등의 엔드포인트에서 사용자 정보를 반환할 때 사용합니다.
    UserInDB와 동일하지만, 추후 API 응답 전용 필드 추가 가능성을 위해 분리했습니다.

    Example Response:
        {
            "id": 1,
            "email": "user@example.com",
            "username": "testuser",
            "full_name": "홍길동",
            "is_active": true,
            "is_superuser": false,
            "created_at": "2025-01-15T10:30:00+09:00",
            "updated_at": "2025-01-16T14:20:00+09:00"
        }

    Note:
        - hashed_password는 절대 포함되지 않음 (보안)
        - 추후 avatar_url, last_login 등의 필드 추가 가능
    """

    pass


class Token(BaseModel):
    """로그인 성공 시 반환하는 토큰 스키마

    POST /api/v1/auth/login 엔드포인트의 응답 스키마입니다.
    JWT 액세스 토큰과 리프레시 토큰을 함께 반환합니다.

    Attributes:
        access_token (str): JWT 액세스 토큰 (30분 유효)
        refresh_token (str): JWT 리프레시 토큰 (7일 유효)
        token_type (str): 토큰 타입 (항상 "bearer")

    Example Response:
        {
            "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
            "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
            "token_type": "bearer"
        }

    Usage:
        클라이언트는 이후 요청 시 Authorization 헤더에 토큰을 포함:
        Authorization: Bearer {access_token}

    Note:
        - access_token: 짧은 만료 시간 (30분), API 요청에 사용
        - refresh_token: 긴 만료 시간 (7일), 액세스 토큰 갱신에 사용
        - token_type: OAuth2 표준에 따라 "bearer"로 고정
    """

    access_token: str
    refresh_token: str
    token_type: str = "bearer"


class TokenPayload(BaseModel):
    """JWT 토큰의 페이로드 스키마

    JWT 토큰을 디코딩했을 때 나오는 데이터 구조입니다.
    주로 내부적으로 토큰 검증에 사용됩니다.

    Attributes:
        sub (int, optional): Subject - 사용자 ID
        exp (int, optional): Expiration - 만료 시각 (Unix timestamp)

    Example (디코딩된 토큰):
        {
            "sub": "123",
            "exp": 1705304400
        }

    Note:
        - sub: JWT 표준 클레임, 토큰의 주체(사용자 ID)를 나타냄
        - exp: JWT 표준 클레임, 토큰 만료 시각 (Unix timestamp)
        - 이 스키마는 API 요청/응답이 아닌 내부 검증용
    """

    sub: Optional[int] = None
    exp: Optional[int] = None


class LoginRequest(BaseModel):
    """로그인 요청 스키마

    POST /api/v1/auth/login 엔드포인트에서 사용합니다.
    사용자명 또는 이메일로 로그인할 수 있습니다.

    Attributes:
        username (str): 사용자명 또는 이메일 (둘 다 가능)
        password (str): 비밀번호 (평문)

    Example (사용자명으로 로그인):
        {
            "username": "testuser",
            "password": "securepass123"
        }

    Example (이메일로 로그인):
        {
            "username": "user@example.com",
            "password": "securepass123"
        }

    Note:
        - username 필드에 실제로는 이메일을 넣어도 작동
        - 서버에서 username OR email 조건으로 검색
        - password는 평문으로 전송되므로 HTTPS 필수!
    """

    username: str
    password: str


class RefreshRequest(BaseModel):
    """토큰 갱신 요청 스키마

    POST /api/v1/auth/refresh 엔드포인트에서 사용합니다.
    리프레시 토큰으로 새 액세스 토큰을 발급받습니다.

    Attributes:
        refresh_token (str): 리프레시 토큰

    Example:
        {
            "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
        }

    Note:
        - 액세스 토큰 만료 시 사용
        - 리프레시 토큰도 만료되면 재로그인 필요
    """

    refresh_token: str


class TokenRefreshResponse(BaseModel):
    """토큰 갱신 응답 스키마

    POST /api/v1/auth/refresh 엔드포인트의 응답입니다.
    새로운 액세스 토큰만 반환합니다.

    Attributes:
        access_token (str): 새 JWT 액세스 토큰
        token_type (str): 토큰 타입 (항상 "bearer")

    Example Response:
        {
            "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
            "token_type": "bearer"
        }

    Note:
        - 리프레시 토큰은 재발급하지 않음 (보안상)
        - 리프레시 토큰이 만료되면 재로그인 필요
    """

    access_token: str
    token_type: str = "bearer"


class VerifyLoginRequest(BaseModel):
    """2FA 로그인 검증 요청 스키마

    POST /api/v1/auth/verify-login 엔드포인트에서 사용합니다.
    인증 코드를 검증하여 토큰을 발급받습니다.

    Attributes:
        username (str): 사용자명 또는 이메일
        code (str): 6자리 인증 코드

    Example:
        {
            "username": "testuser",
            "code": "123456"
        }

    Note:
        - 코드는 5분간 유효
        - 코드는 일회용 (재사용 불가)
        - 잘못된 코드 입력 시 400 에러
    """

    username: str
    code: str = Field(..., min_length=6, max_length=6, pattern=r'^\d{6}$')


class LoginRequestResponse(BaseModel):
    """2FA 로그인 요청 응답 스키마

    POST /api/v1/auth/request-login 엔드포인트의 응답입니다.
    인증 코드 발송 성공 메시지를 반환합니다.

    Attributes:
        message (str): 성공 메시지

    Example Response:
        {
            "message": "Verification code sent successfully"
        }
    """

    message: str
