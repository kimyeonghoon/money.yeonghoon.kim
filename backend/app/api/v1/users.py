from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.database import get_db
from app.models.user import User
from app.schemas.user import User as UserSchema, UserUpdate
from app.schemas.common import MessageResponse
from app.api.deps import get_current_active_user
from app.core.security import get_password_hash

router = APIRouter()


@router.get("/me", response_model=UserSchema)
def read_users_me(current_user: User = Depends(get_current_active_user)):
    """현재 사용자 정보 조회

    인증된 사용자의 전체 프로필 정보를 반환합니다.

    Args:
        current_user: 현재 인증된 사용자 (의존성 주입)

    Returns:
        UserSchema: 사용자 정보 (id, email, username, full_name, is_active, timestamps)

    Example:
        GET /api/v1/users/me
        Authorization: Bearer {access_token}

        Response (200):
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
    """
    return current_user


@router.put("/me", response_model=UserSchema)
def update_user_me(
    user_in: UserUpdate,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """현재 사용자 정보 업데이트"""
    update_data = user_in.model_dump(exclude_unset=True)

    # 비밀번호 변경 시 해싱
    if "password" in update_data:
        update_data["hashed_password"] = get_password_hash(update_data["password"])
        del update_data["password"]

    # 이메일 중복 확인
    if "email" in update_data:
        # RAW SQL: SELECT * FROM users WHERE email = ? AND id != ? LIMIT 1
        existing_user = (
            db.query(User)
            .filter(User.email == update_data["email"], User.id != current_user.id)
            .first()
        )
        if existing_user:
            raise HTTPException(status_code=400, detail="Email already registered")

    # 사용자명 중복 확인
    if "username" in update_data:
        # RAW SQL: SELECT * FROM users WHERE username = ? AND id != ? LIMIT 1
        existing_user = (
            db.query(User)
            .filter(
                User.username == update_data["username"], User.id != current_user.id
            )
            .first()
        )
        if existing_user:
            raise HTTPException(status_code=400, detail="Username already taken")

    # 업데이트
    # RAW SQL: UPDATE users SET field1 = ?, field2 = ?, ..., updated_at = NOW() WHERE id = ?
    for field, value in update_data.items():
        setattr(current_user, field, value)

    db.commit()
    db.refresh(current_user)

    return current_user


@router.get("/{user_id}", response_model=UserSchema)
def read_user_by_id(
    user_id: int,
    current_user: User = Depends(get_current_active_user),
    db: Session = Depends(get_db),
):
    """특정 사용자 정보 조회

    ID로 특정 사용자의 정보를 조회합니다.

    Args:
        user_id: 조회할 사용자 ID
        current_user: 현재 인증된 사용자 (의존성 주입)
        db: 데이터베이스 세션

    Returns:
        UserSchema: 사용자 정보

    Raises:
        HTTPException 404: 사용자를 찾을 수 없는 경우

    Example:
        GET /api/v1/users/123
        Authorization: Bearer {access_token}

        Response (200):
        {
            "id": 123,
            "email": "user@example.com",
            "username": "testuser",
            ...
        }
    """
    # RAW SQL: SELECT * FROM users WHERE id = ? LIMIT 1
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user


@router.delete("/me", response_model=MessageResponse)
def delete_current_user(
    current_user: User = Depends(get_current_active_user), db: Session = Depends(get_db)
):
    """현재 사용자 삭제 (소프트 삭제)

    사용자 계정을 비활성화합니다 (is_active = False).
    실제 데이터는 삭제되지 않으며, 복구 가능합니다.

    Args:
        current_user: 현재 인증된 사용자
        db: 데이터베이스 세션

    Returns:
        MessageResponse: 성공 메시지

    Raises:
        HTTPException 400: 이미 비활성화된 사용자

    Example:
        DELETE /api/v1/users/me
        Authorization: Bearer {access_token}

        Response (200):
        {
            "message": "계정이 성공적으로 비활성화되었습니다"
        }

    Note:
        - 소프트 삭제: 실제 DB에서 삭제하지 않고 is_active = False로 설정
        - 비활성화된 사용자는 로그인 불가
        - 관리자에 의한 복구 가능
        - 하드 삭제는 GDPR 등의 요구사항이 있을 때만 사용
    """
    if not current_user.is_active:
        raise HTTPException(status_code=400, detail="User is already deactivated")

    # RAW SQL: UPDATE users SET is_active = FALSE, updated_at = NOW() WHERE id = ?
    current_user.is_active = False
    db.commit()

    return {"message": "계정이 성공적으로 비활성화되었습니다"}
