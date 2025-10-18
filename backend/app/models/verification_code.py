"""
VerificationCode 모델 정의

2단계 인증(2FA)을 위한 일회용 인증 코드를 관리합니다.

사용 흐름:
    1. 로그인 시도 → 인증 코드 생성 (6자리)
    2. Telegram으로 전송
    3. 5분 내 사용자가 코드 입력
    4. 검증 후 토큰 발급
    5. 사용된 코드는 재사용 불가

보안 고려사항:
    - 만료 시간: 5분
    - 일회용: is_used=True 처리
    - 정기적 정리: cleanup_expired() 호출 권장
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import Session
from datetime import datetime
from app.database import Base


class VerificationCode(Base):
    """인증 코드 ORM 모델

    2FA를 위한 임시 인증 코드를 저장하는 테이블입니다.

    Attributes:
        id (int): 인증 코드 고유 ID (자동 증가)
        user_id (int): 사용자 ID (외래 키)
        code (str): 6자리 인증 코드
        expires_at (datetime): 만료 시각
        is_used (bool): 사용 여부 (기본: False)
        created_at (datetime): 생성 시각 (자동 설정)

    Indexes:
        - user_id: 사용자별 코드 조회 최적화
        - expires_at: 만료된 코드 정리 최적화

    Note:
        - 한 사용자가 여러 코드를 가질 수 있음 (최신 것만 유효)
        - 만료되거나 사용된 코드는 재사용 불가
        - 정기적으로 cleanup_expired() 실행 권장
    """

    __tablename__ = "verification_codes"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    code = Column(String(6), nullable=False)
    expires_at = Column(DateTime(timezone=True), nullable=False, index=True)
    is_used = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())

    def is_expired(self) -> bool:
        """인증 코드가 만료되었는지 확인

        Returns:
            bool: 만료되었으면 True, 아니면 False

        Example:
            >>> code = db.query(VerificationCode).first()
            >>> if code.is_expired():
            ...     print("코드가 만료되었습니다")
        """
        return datetime.now() > self.expires_at

    def mark_as_used(self) -> None:
        """인증 코드를 사용 완료 처리

        is_used를 True로 설정합니다.
        호출 후 반드시 db.commit()을 실행해야 합니다.

        Example:
            >>> code = db.query(VerificationCode).filter(...).first()
            >>> code.mark_as_used()
            >>> db.commit()

        Note:
            - 사용된 코드는 재사용 불가
            - DB commit이 필요함
        """
        self.is_used = True

    @classmethod
    def get_latest_for_user(cls, db: Session, user_id: int):
        """사용자의 가장 최근 미사용 코드 조회

        Args:
            db (Session): 데이터베이스 세션
            user_id (int): 사용자 ID

        Returns:
            VerificationCode | None: 가장 최근 미사용 코드, 없으면 None

        Example:
            >>> latest = VerificationCode.get_latest_for_user(db, user_id=1)
            >>> if latest and not latest.is_expired():
            ...     print(f"코드: {latest.code}")

        Note:
            - is_used=False인 코드만 조회
            - created_at, id 기준 내림차순 정렬 (최신 것 우선)
        """
        return (
            db.query(cls)
            .filter(cls.user_id == user_id, cls.is_used == False)
            .order_by(cls.created_at.desc(), cls.id.desc())
            .first()
        )

    @classmethod
    def cleanup_expired(cls, db: Session) -> int:
        """만료된 인증 코드 삭제

        expires_at이 현재 시각보다 이전인 코드를 삭제합니다.
        정기적으로 실행하여 DB 정리를 권장합니다.

        Args:
            db (Session): 데이터베이스 세션

        Returns:
            int: 삭제된 코드 개수

        Example:
            >>> deleted_count = VerificationCode.cleanup_expired(db)
            >>> print(f"{deleted_count}개의 만료된 코드 삭제")

        Note:
            - Cron Job 등으로 정기 실행 권장 (예: 매 시간)
            - 트랜잭션이므로 commit 필요
        """
        now = datetime.now()
        deleted = db.query(cls).filter(cls.expires_at < now).delete()
        db.commit()
        return deleted

    def __repr__(self):
        """객체의 문자열 표현

        Returns:
            str: 디버깅용 문자열

        Example:
            >>> code = db.query(VerificationCode).first()
            >>> print(code)
            <VerificationCode(id=1, user_id=5, code=123456, expired=False)>
        """
        return (
            f"<VerificationCode(id={self.id}, user_id={self.user_id}, "
            f"code={self.code}, expired={self.is_expired()})>"
        )
