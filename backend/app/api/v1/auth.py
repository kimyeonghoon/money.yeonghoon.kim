from fastapi import APIRouter, Depends, HTTPException, status, Request
from sqlalchemy.orm import Session
from app.database import get_db
from app.models.user import User
from app.models.verification_code import VerificationCode
from app.schemas.user import (
    UserCreate,
    User as UserSchema,
    Token,
    LoginRequest,
    RefreshRequest,
    TokenRefreshResponse,
    VerifyLoginRequest,
    LoginRequestResponse,
)
from app.core.security import (
    create_access_token,
    create_refresh_token,
    verify_password,
    get_password_hash,
    verify_refresh_token,
)
from app.services.notifications.telegram import TelegramNotificationService
from app.core.constants import (
    VERIFICATION_CODE_MIN,
    VERIFICATION_CODE_MAX,
    VERIFICATION_CODE_EXPIRE_MINUTES,
)
import logging
import random
from datetime import datetime, timedelta

logger = logging.getLogger(__name__)
router = APIRouter()


async def _send_login_notification_async(user: User, request: Request) -> None:
    """로그인 알림 전송 (내부 함수)

    Telegram으로 로그인 알림을 전송합니다.
    알림 실패는 로그인 성공을 방해하지 않습니다.

    Args:
        user: 로그인한 사용자
        request: FastAPI Request 객체 (IP, User-Agent 추출용)

    Note:
        - TELEGRAM_ENABLED=false일 경우 알림 전송하지 않음
        - 알림 실패 시에도 예외를 발생시키지 않음 (로그만 기록)
    """
    try:
        notifier = TelegramNotificationService()
        if notifier.is_enabled():
            ip_address = request.client.host if request.client else "unknown"
            user_agent = request.headers.get("user-agent", "unknown")

            success = await notifier.send_login_alert(
                user_email=user.email,
                ip_address=ip_address,
                user_agent=user_agent
            )

            if success:
                logger.info(f"Login notification sent for user ID {user.id}")
            else:
                logger.warning(f"Failed to send login notification for user ID {user.id}")
    except Exception as e:
        logger.error(f"Error sending login notification: {e}")


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
async def login(login_data: LoginRequest, request: Request, db: Session = Depends(get_db)):
    """로그인

    로그인 성공 시 JWT 토큰을 발급하고, 선택적으로 Telegram 알림을 전송합니다.

    Args:
        login_data: 로그인 요청 데이터 (username/email + password)
        request: FastAPI Request 객체 (IP, User-Agent 추출용)
        db: 데이터베이스 세션

    Returns:
        Token: Access token, Refresh token, token type

    Note:
        - Telegram 알림 실패는 로그인 성공을 방해하지 않음
        - TELEGRAM_ENABLED=false일 경우 알림 전송하지 않음
    """
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

    # Telegram 로그인 알림 전송 (선택적, 비동기)
    await _send_login_notification_async(user, request)

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
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid refresh token",
            headers={"WWW-Authenticate": "Bearer"},
        )

    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Inactive user"
        )

    new_access_token = create_access_token(subject=user.id)

    return {"access_token": new_access_token, "token_type": "bearer"}


@router.post("/request-login", response_model=LoginRequestResponse)
async def request_login(login_data: LoginRequest, request: Request, db: Session = Depends(get_db)):
    """2FA 로그인 요청 - 인증 코드 발송

    사용자 인증 후 Telegram으로 6자리 인증 코드를 전송합니다.

    Args:
        login_data: 로그인 요청 데이터 (username/email + password)
        request: FastAPI Request 객체
        db: 데이터베이스 세션

    Returns:
        LoginRequestResponse: 성공 메시지

    Raises:
        HTTPException 401: 자격 증명이 올바르지 않거나 사용자가 비활성 상태인 경우

    Note:
        - 인증 코드는 5분간 유효
        - Telegram 전송 실패 시에도 코드는 DB에 저장됨
    """
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

    # 6자리 인증 코드 생성
    code = str(random.randint(VERIFICATION_CODE_MIN, VERIFICATION_CODE_MAX))
    expires_at = datetime.now() + timedelta(minutes=VERIFICATION_CODE_EXPIRE_MINUTES)

    # RAW SQL: INSERT INTO verification_codes (user_id, code, expires_at, is_used, created_at)
    #          VALUES (?, ?, ?, false, NOW())
    verification_code = VerificationCode(
        user_id=user.id,
        code=code,
        expires_at=expires_at,
        is_used=False
    )
    db.add(verification_code)
    db.commit()
    db.refresh(verification_code)

    # Telegram으로 인증 코드 전송
    try:
        notifier = TelegramNotificationService()
        if notifier.is_enabled():
            success = await notifier.send_verification_code(
                user_email=user.email,
                code=code
            )

            if success:
                logger.info(f"Verification code sent to {user.email}")
            else:
                logger.warning(f"Failed to send verification code to {user.email}")
    except Exception as e:
        logger.error(f"Error sending verification code: {e}")

    return {"message": "Verification code sent successfully"}


@router.post("/verify-login", response_model=Token)
async def verify_login(verify_data: VerifyLoginRequest, request: Request, db: Session = Depends(get_db)):
    """2FA 로그인 검증 - 인증 코드 검증 및 토큰 발급

    인증 코드를 검증하고 JWT 토큰을 발급합니다.

    Args:
        verify_data: 검증 요청 데이터 (username + code)
        request: FastAPI Request 객체
        db: 데이터베이스 세션

    Returns:
        Token: Access token, Refresh token, token type

    Raises:
        HTTPException 401: 사용자를 찾을 수 없거나 비활성 상태인 경우
        HTTPException 400: 인증 코드가 유효하지 않거나 만료된 경우

    Note:
        - 코드 검증 성공 시 해당 코드는 재사용 불가 처리
        - 로그인 알림도 함께 전송
    """
    # RAW SQL: SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1
    user = (
        db.query(User)
        .filter(
            (User.username == verify_data.username) | (User.email == verify_data.username)
        )
        .first()
    )

    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid credentials",
            headers={"WWW-Authenticate": "Bearer"},
        )

    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Inactive user"
        )

    # 최신 미사용 인증 코드 조회
    # RAW SQL: SELECT * FROM verification_codes
    #          WHERE user_id = ? AND is_used = false
    #          ORDER BY created_at DESC, id DESC LIMIT 1
    code_obj = VerificationCode.get_latest_for_user(db, user.id)

    if not code_obj or code_obj.code != verify_data.code:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Invalid verification code"
        )

    if code_obj.is_expired():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Verification code has expired"
        )

    # 코드 사용 처리
    # RAW SQL: UPDATE verification_codes SET is_used = true WHERE id = ?
    code_obj.mark_as_used()
    db.commit()

    # 토큰 생성
    access_token = create_access_token(subject=user.id)
    refresh_token = create_refresh_token(subject=user.id)

    # 로그인 알림 전송 (선택적)
    await _send_login_notification_async(user, request)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
    }
