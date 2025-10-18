"""
VerificationCode 모델 테스트

2FA 인증을 위한 일회용 인증 코드 모델을 테스트합니다.
"""
import pytest
from datetime import datetime, timedelta
from sqlalchemy.orm import Session


class TestVerificationCodeModel:
    """VerificationCode 모델 테스트 클래스"""

    def test_create_verification_code(self, db: Session, test_user):
        """인증 코드 생성 테스트"""
        # Given: VerificationCode 모델
        from app.models.verification_code import VerificationCode

        # When: 인증 코드 생성
        code = VerificationCode(
            user_id=test_user.id,
            code="123456",
            expires_at=datetime.now() + timedelta(minutes=5)
        )
        db.add(code)
        db.commit()
        db.refresh(code)

        # Then: DB에 저장되어야 함
        assert code.id is not None
        assert code.user_id == test_user.id
        assert code.code == "123456"
        assert code.is_used is False

    def test_code_expiration(self, db: Session, test_user):
        """인증 코드 만료 확인 테스트"""
        # Given: 만료된 인증 코드
        from app.models.verification_code import VerificationCode

        expired_code = VerificationCode(
            user_id=test_user.id,
            code="999999",
            expires_at=datetime.now() - timedelta(minutes=1)  # 1분 전 만료
        )
        db.add(expired_code)
        db.commit()
        db.refresh(expired_code)

        # When: is_expired() 확인
        # Then: True 반환
        assert expired_code.is_expired() is True

    def test_code_not_expired(self, db: Session, test_user):
        """인증 코드가 아직 유효한 경우"""
        # Given: 유효한 인증 코드
        from app.models.verification_code import VerificationCode

        valid_code = VerificationCode(
            user_id=test_user.id,
            code="123456",
            expires_at=datetime.now() + timedelta(minutes=5)
        )
        db.add(valid_code)
        db.commit()
        db.refresh(valid_code)

        # When: is_expired() 확인
        # Then: False 반환
        assert valid_code.is_expired() is False

    def test_mark_as_used(self, db: Session, test_user):
        """인증 코드 사용 처리"""
        # Given: 미사용 인증 코드
        from app.models.verification_code import VerificationCode

        code = VerificationCode(
            user_id=test_user.id,
            code="123456",
            expires_at=datetime.now() + timedelta(minutes=5)
        )
        db.add(code)
        db.commit()
        db.refresh(code)

        # When: 사용 처리
        code.mark_as_used()
        db.commit()
        db.refresh(code)

        # Then: is_used가 True
        assert code.is_used is True

    def test_find_latest_unused_code(self, db: Session, test_user):
        """사용자의 가장 최근 미사용 코드 조회"""
        # Given: 여러 인증 코드
        from app.models.verification_code import VerificationCode

        # 오래된 코드
        old_code = VerificationCode(
            user_id=test_user.id,
            code="111111",
            expires_at=datetime.now() + timedelta(minutes=5)
        )
        db.add(old_code)
        db.commit()

        # 새 코드
        new_code = VerificationCode(
            user_id=test_user.id,
            code="222222",
            expires_at=datetime.now() + timedelta(minutes=5)
        )
        db.add(new_code)
        db.commit()

        # When: 가장 최근 코드 조회
        latest = VerificationCode.get_latest_for_user(db, test_user.id)

        # Then: 가장 최근 코드 반환
        assert latest is not None
        assert latest.code == "222222"

    def test_cleanup_expired_codes(self, db: Session, test_user):
        """만료된 코드 자동 정리"""
        # Given: 만료된 코드와 유효한 코드
        from app.models.verification_code import VerificationCode

        expired = VerificationCode(
            user_id=test_user.id,
            code="old",
            expires_at=datetime.now() - timedelta(minutes=10)
        )
        valid = VerificationCode(
            user_id=test_user.id,
            code="new",
            expires_at=datetime.now() + timedelta(minutes=5)
        )
        db.add(expired)
        db.add(valid)
        db.commit()

        # When: 만료된 코드 정리
        VerificationCode.cleanup_expired(db)

        # Then: 만료된 코드만 삭제
        all_codes = db.query(VerificationCode).filter(
            VerificationCode.user_id == test_user.id
        ).all()
        assert len(all_codes) == 1
        assert all_codes[0].code == "new"
