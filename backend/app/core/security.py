"""
보안 관련 유틸리티 모듈

이 모듈은 애플리케이션의 보안 기능을 제공합니다:
1. JWT 토큰 생성 (액세스 토큰, 리프레시 토큰)
2. 비밀번호 해싱 및 검증 (bcrypt 알고리즘 사용)

주요 개념:
- JWT (JSON Web Token): 사용자 인증을 위한 토큰 방식
- bcrypt: 비밀번호를 안전하게 저장하기 위한 해싱 알고리즘
- Salt: bcrypt가 자동으로 생성하는 임의의 값 (같은 비밀번호도 다른 해시 생성)
"""

from datetime import datetime, timedelta
from typing import Optional
from jose import jwt, JWTError
from passlib.context import CryptContext
from app.config import settings

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")


def create_access_token(subject: int, expires_delta: Optional[timedelta] = None) -> str:
    """JWT 액세스 토큰 생성

    사용자 인증을 위한 단기 유효 토큰을 생성합니다.
    API 요청 시 Authorization 헤더에 포함하여 사용합니다.

    Args:
        subject (int): 토큰에 포함할 사용자 ID
        expires_delta (timedelta, optional): 커스텀 만료 시간
            지정하지 않으면 설정 파일의 ACCESS_TOKEN_EXPIRE_MINUTES 사용 (기본 30분)

    Returns:
        str: 인코딩된 JWT 토큰 문자열

    Example:
        >>> user_id = 123
        >>> token = create_access_token(user_id)
        >>> print(token)
        'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3MDUzMDQ0MDAsInN1YiI6IjEyMyJ9.xxxxx'

    Token Payload:
        {
            "exp": 1705304400,  # 만료 시각 (Unix timestamp)
            "sub": "123"        # 사용자 ID (문자열로 변환됨)
        }

    Note:
        - 액세스 토큰은 짧은 만료 시간(30분)을 가짐
        - 토큰 탈취 시 피해를 최소화하기 위해 짧게 유지
        - 만료되면 리프레시 토큰으로 재발급
    """
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(
            minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES
        )

    to_encode = {"exp": expire, "sub": str(subject)}
    encoded_jwt = jwt.encode(
        to_encode, settings.SECRET_KEY, algorithm=settings.ALGORITHM
    )
    return encoded_jwt


def create_refresh_token(subject: int) -> str:
    """JWT 리프레시 토큰 생성

    액세스 토큰을 재발급받기 위한 장기 유효 토큰을 생성합니다.
    액세스 토큰이 만료되었을 때 새 토큰을 발급받는 데 사용합니다.

    Args:
        subject (int): 토큰에 포함할 사용자 ID

    Returns:
        str: 인코딩된 JWT 리프레시 토큰 문자열

    Example:
        >>> user_id = 123
        >>> refresh_token = create_refresh_token(user_id)
        >>> # 클라이언트는 이 토큰을 안전하게 저장 (보통 Secure HttpOnly Cookie)

    Token Payload:
        {
            "exp": 1705909200,     # 만료 시각 (7일 후)
            "sub": "123",          # 사용자 ID
            "type": "refresh"      # 토큰 타입 (액세스 토큰과 구분)
        }

    Note:
        - 리프레시 토큰은 긴 만료 시간(7일)을 가짐
        - type="refresh"로 액세스 토큰과 구분
        - 보안을 위해 HttpOnly Cookie에 저장 권장 (XSS 공격 방지)
        - 리프레시 토큰으로는 API 호출 불가, 오직 토큰 재발급만 가능
    """
    expire = datetime.utcnow() + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS)
    to_encode = {"exp": expire, "sub": str(subject), "type": "refresh"}
    encoded_jwt = jwt.encode(
        to_encode, settings.SECRET_KEY, algorithm=settings.ALGORITHM
    )
    return encoded_jwt


def verify_password(plain_password: str, hashed_password: str) -> bool:
    """비밀번호 검증

    사용자가 입력한 평문 비밀번호와 DB에 저장된 해시값을 비교합니다.
    로그인 시 비밀번호 확인에 사용됩니다.

    Args:
        plain_password (str): 사용자가 입력한 평문 비밀번호
        hashed_password (str): DB에 저장된 해시된 비밀번호
            (예: $2b$12$abcdefg...)

    Returns:
        bool: 비밀번호가 일치하면 True, 아니면 False

    Example:
        >>> # 로그인 시
        >>> user_input = "mypassword123"
        >>> db_hash = "$2b$12$abcdefg..."
        >>> is_valid = verify_password(user_input, db_hash)
        >>> if is_valid:
        ...     print("로그인 성공")
        ... else:
        ...     print("비밀번호 불일치")

    How it works:
        1. bcrypt가 hashed_password에서 salt를 추출
        2. plain_password를 같은 salt로 해싱
        3. 두 해시값 비교
        4. 일치 여부 반환

    Note:
        - bcrypt는 의도적으로 느림 (브루트포스 공격 방지)
        - 같은 비밀번호도 매번 다른 해시 생성 (salt 때문)
        - 검증은 약 0.1초 소요 (정상적인 동작)
    """
    return pwd_context.verify(plain_password, hashed_password)


def get_password_hash(password: str) -> str:
    """비밀번호 해싱

    평문 비밀번호를 bcrypt로 해시하여 안전하게 저장 가능한 형태로 변환합니다.
    회원가입 및 비밀번호 변경 시 사용됩니다.

    Args:
        password (str): 해시할 평문 비밀번호

    Returns:
        str: bcrypt로 해시된 비밀번호 문자열
            형식: $2b$12$salt+hash (총 60자)

    Example:
        >>> password = "mysecretpassword"
        >>> hashed = get_password_hash(password)
        >>> print(hashed)
        '$2b$12$abcdefghijklmnopqrstuvwxyz...'

        >>> # 같은 비밀번호도 매번 다른 해시 생성 (salt 때문)
        >>> hashed2 = get_password_hash(password)
        >>> print(hashed != hashed2)
        True

    Hash Format:
        $2b$12$saltsaltsalt...hashhash...
         │  │  │               └─ 31자: 실제 해시값
         │  │  └─ 22자: salt (bcrypt가 자동 생성)
         │  └─ 12: cost factor (2^12 = 4096 rounds, 느림)
         └─ 2b: bcrypt 버전

    Note:
        - 절대 평문 비밀번호를 DB에 저장하지 마세요!
        - bcrypt는 자동으로 salt 생성 (매번 다른 해시)
        - cost factor 12는 보안과 성능의 균형
        - 해시 결과는 항상 60자 문자열
    """
    return pwd_context.hash(password)


def verify_refresh_token(token: str) -> Optional[int]:
    """리프레시 토큰 검증 및 사용자 ID 추출

    리프레시 토큰의 유효성을 검증하고 사용자 ID를 반환합니다.
    액세스 토큰 재발급 시 사용됩니다.

    Args:
        token (str): 검증할 리프레시 토큰

    Returns:
        Optional[int]: 유효한 경우 사용자 ID, 무효한 경우 None

    Example:
        >>> refresh_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
        >>> user_id = verify_refresh_token(refresh_token)
        >>> if user_id:
        ...     new_access_token = create_access_token(user_id)
        ... else:
        ...     # 토큰 무효 - 재로그인 필요

    Validates:
        - 토큰 서명 검증 (SECRET_KEY로 서명되었는지)
        - 만료 시간 확인
        - 토큰 타입 확인 (type="refresh"인지)

    Note:
        - 액세스 토큰과 구분하기 위해 type="refresh" 확인
        - 무효한 토큰은 None 반환 (에러 발생 안 함)
    """
    try:
        payload = jwt.decode(
            token, settings.SECRET_KEY, algorithms=[settings.ALGORITHM]
        )

        user_id_str: str = payload.get("sub")
        token_type: str = payload.get("type")

        if user_id_str is None or token_type != "refresh":
            return None

        return int(user_id_str)
    except JWTError:
        return None
