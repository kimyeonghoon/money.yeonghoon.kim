"""
테스트 사용자 생성 스크립트

사용법:
    docker compose -f docker-compose.dev.yml exec backend python create_test_user.py
"""

from app.database import SessionLocal
from app.models.user import User
from app.core.security import get_password_hash


def create_test_user():
    """테스트 사용자 생성"""
    db = SessionLocal()

    try:
        existing_user = db.query(User).filter(User.username == "test").first()
        if existing_user:
            print("✅ 테스트 계정이 이미 존재합니다:")
            print(f"   Username: test")
            print(f"   Password: test")
            print(f"   Email: {existing_user.email}")
            return

        test_user = User(
            username="test",
            email="test@test.com",
            hashed_password=get_password_hash("test"),
            telegram_chat_id=123456789,
            is_active=True,
        )

        db.add(test_user)
        db.commit()
        db.refresh(test_user)

        print("✅ 테스트 계정이 생성되었습니다!")
        print(f"   Username: test")
        print(f"   Password: test")
        print(f"   Email: test@test.com")
        print(f"   User ID: {test_user.id}")
        print("")
        print("⚠️  주의: 텔레그램 2FA는 실제 chat_id가 필요합니다.")
        print("   현재는 더미 값(123456789)이 설정되어 있습니다.")

    except Exception as e:
        db.rollback()
        print(f"❌ 오류 발생: {e}")
    finally:
        db.close()


if __name__ == "__main__":
    create_test_user()
