from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.database import get_db
from app.models.user import User
from app.schemas.user import (
    UserCreate,
    User as UserSchema,
    Token,
    LoginRequest,
    RefreshRequest,
    TokenRefreshResponse,
)
from app.core.security import (
    create_access_token,
    create_refresh_token,
    verify_password,
    get_password_hash,
    verify_refresh_token,
)

router = APIRouter()


@router.post(
    "/register", response_model=UserSchema, status_code=status.HTTP_201_CREATED
)
def register(user_in: UserCreate, db: Session = Depends(get_db)):
    """새 사용자 등록"""
    # 이메일 중복 확인
    # RAW SQL: SELECT * FROM users WHERE email = ? LIMIT 1
    user = db.query(User).filter(User.email == user_in.email).first()
    if user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Email already registered"
        )

    # 사용자명 중복 확인
    # RAW SQL: SELECT * FROM users WHERE username = ? LIMIT 1
    user = db.query(User).filter(User.username == user_in.username).first()
    if user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Username already taken"
        )

    # 새 사용자 생성
    # RAW SQL: INSERT INTO users (email, username, hashed_password, full_name, is_active, created_at, updated_at)
    #          VALUES (?, ?, ?, ?, true, NOW(), NOW())
    user = User(
        email=user_in.email,
        username=user_in.username,
        hashed_password=get_password_hash(user_in.password),
        full_name=user_in.full_name,
    )
    db.add(user)
    db.commit()
    db.refresh(user)

    return user


@router.post("/login", response_model=Token)
def login(login_data: LoginRequest, db: Session = Depends(get_db)):
    """로그인"""
    # 사용자 찾기 (username 또는 email)
    # RAW SQL: SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1
    user = (
        db.query(User)
        .filter(
            (User.username == login_data.username) | (User.email == login_data.username)
        )
        .first()
    )

    if not user or not verify_password(login_data.password, user.hashed_password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password",
            headers={"WWW-Authenticate": "Bearer"},
        )

    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Inactive user"
        )

    # 토큰 생성
    access_token = create_access_token(subject=user.id)
    refresh_token = create_refresh_token(subject=user.id)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
    }


@router.post("/refresh", response_model=TokenRefreshResponse)
def refresh_access_token(refresh_data: RefreshRequest, db: Session = Depends(get_db)):
    """액세스 토큰 갱신

    리프레시 토큰을 사용하여 새로운 액세스 토큰을 발급받습니다.
    액세스 토큰이 만료되었을 때 재로그인 없이 계속 사용할 수 있습니다.

    Args:
        refresh_data: 리프레시 토큰이 포함된 요청 데이터
        db: 데이터베이스 세션

    Returns:
        TokenRefreshResponse: 새 액세스 토큰

    Raises:
        HTTPException 401: 리프레시 토큰이 유효하지 않거나 만료된 경우
        HTTPException 400: 사용자가 비활성 상태인 경우
        HTTPException 404: 사용자를 찾을 수 없는 경우

    Example:
        Request:
            POST /api/v1/auth/refresh
            {
                "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
            }

        Response (200):
            {
                "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
                "token_type": "bearer"
            }

    Note:
        - 리프레시 토큰 자체는 재발급하지 않음
        - 리프레시 토큰도 만료되면 재로그인 필요
        - 보안을 위해 비활성 사용자는 토큰 갱신 불가
    """
    user_id = verify_refresh_token(refresh_data.refresh_token)

    if user_id is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid or expired refresh token",
            headers={"WWW-Authenticate": "Bearer"},
        )

    # RAW SQL: SELECT * FROM users WHERE id = ? LIMIT 1
    user = db.query(User).filter(User.id == user_id).first()

    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND, detail="User not found"
        )

    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Inactive user"
        )

    new_access_token = create_access_token(subject=user.id)

    return {"access_token": new_access_token, "token_type": "bearer"}
