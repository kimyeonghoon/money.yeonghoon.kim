"""
API 엔드포인트 의존성 함수

이 모듈은 FastAPI의 Depends()와 함께 사용되는 의존성 함수를 정의합니다.
주로 사용자 인증 및 권한 확인에 사용됩니다.

의존성 주입 (Dependency Injection) 개념:
- FastAPI가 엔드포인트 실행 전에 자동으로 호출하는 함수
- 인증, DB 연결, 권한 확인 등의 공통 로직을 재사용 가능
- 엔드포인트 함수에서 Depends(함수명)으로 사용

사용 예시:
    @app.get("/protected")
    def protected_route(user: User = Depends(get_current_active_user)):
        # user는 자동으로 인증된 활성 사용자 객체
        return {"user_id": user.id}
"""

from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from jose import jwt, JWTError
from sqlalchemy.orm import Session
from app.config import settings
from app.database import get_db
from app.models.user import User

security = HTTPBearer()


def get_current_user(
    db: Session = Depends(get_db),
    credentials: HTTPAuthorizationCredentials = Depends(security),
) -> User:
    """현재 인증된 사용자 가져오기

    JWT 토큰을 검증하고 해당하는 사용자 객체를 반환합니다.
    인증이 필요한 모든 엔드포인트에서 사용됩니다.

    Args:
        db (Session): 데이터베이스 세션 (get_db 의존성에서 자동 주입)
        credentials (HTTPAuthorizationCredentials): Authorization 헤더의 Bearer 토큰
            (security 의존성에서 자동 추출)

    Returns:
        User: 인증된 사용자 ORM 객체

    Raises:
        HTTPException (401): 다음의 경우 인증 실패 에러 발생
            - Authorization 헤더가 없는 경우
            - 토큰이 유효하지 않은 경우 (서명 불일치, 만료 등)
            - 토큰의 sub(사용자 ID)가 없는 경우
            - 해당 사용자가 DB에 존재하지 않는 경우

    Example:
        @app.get("/protected")
        def protected_route(current_user: User = Depends(get_current_user)):
            return {"user_id": current_user.id}

    How it works:
        1. security가 Authorization 헤더에서 토큰 추출
        2. JWT 토큰 디코딩 및 검증
        3. 토큰의 sub(사용자 ID)로 DB에서 사용자 조회
        4. 사용자 객체 반환

    Note:
        - 이 함수는 사용자가 존재하는지만 확인 (활성 상태는 미확인)
        - 활성 사용자만 허용하려면 get_current_active_user 사용
    """
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Could not validate credentials",
        headers={"WWW-Authenticate": "Bearer"},
    )

    try:
        token = credentials.credentials
        payload = jwt.decode(
            token, settings.SECRET_KEY, algorithms=[settings.ALGORITHM]
        )
        user_id: str = payload.get("sub")
        if user_id is None:
            raise credentials_exception
    except JWTError:
        raise credentials_exception

    # RAW SQL: SELECT * FROM users WHERE id = ? LIMIT 1
    user = db.query(User).filter(User.id == int(user_id)).first()

    if user is None:
        raise credentials_exception

    return user


def get_current_active_user(
    current_user: User = Depends(get_current_user),
) -> User:
    """활성화된 현재 사용자 가져오기

    인증된 사용자 중에서 활성 상태(is_active=True)인 사용자만 허용합니다.
    대부분의 보호된 엔드포인트에서 이 함수를 사용하는 것이 권장됩니다.

    Args:
        current_user (User): 인증된 사용자 객체
            (get_current_user 의존성에서 자동 주입)

    Returns:
        User: 활성화된 사용자 ORM 객체

    Raises:
        HTTPException (400): 사용자가 비활성 상태인 경우

    Example:
        @app.get("/profile")
        def get_profile(user: User = Depends(get_current_active_user)):
            # user는 인증되고 활성화된 사용자만 도달
            return {"username": user.username}

    Use Cases:
        - 일반 API 엔드포인트: get_current_active_user 사용 (활성 사용자만)
        - 관리자 전용 엔드포인트: is_superuser 추가 확인
        - 계정 활성화 엔드포인트: get_current_user 사용 (비활성 사용자도 허용)

    Note:
        - is_active=False는 소프트 삭제에 사용됨
        - 비활성 사용자는 로그인은 불가하지만 토큰은 남아있을 수 있음
        - 이 함수로 비활성 사용자의 API 사용을 차단
    """
    if not current_user.is_active:
        raise HTTPException(status_code=400, detail="Inactive user")
    return current_user
