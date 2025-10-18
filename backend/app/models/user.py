"""
User 모델 정의

SQLAlchemy ORM을 사용하여 users 테이블을 정의합니다.
이 모델은 사용자 인증 및 관리를 위한 핵심 테이블입니다.

테이블 구조:
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        hashed_password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        is_superuser BOOLEAN DEFAULT FALSE,
        created_at DATETIME DEFAULT NOW(),
        updated_at DATETIME ON UPDATE NOW(),
        INDEX idx_email (email),
        INDEX idx_username (username)
    );
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime
from sqlalchemy.sql import func
from app.database import Base


class User(Base):
    """User ORM 모델

    사용자 정보를 저장하는 데이터베이스 테이블입니다.
    인증(authentication) 및 권한(authorization)을 위한 필드를 포함합니다.

    Attributes:
        id (int): 사용자 고유 식별자 (자동 증가)
        email (str): 이메일 주소 (고유값, 로그인 가능)
        username (str): 사용자명 (고유값, 로그인 가능)
        hashed_password (str): bcrypt로 해시된 비밀번호 (평문 저장 금지!)
        full_name (str, optional): 사용자 실명
        is_active (bool): 활성 상태 (비활성화 시 로그인 불가)
        is_superuser (bool): 관리자 권한 여부
        created_at (datetime): 계정 생성 시각 (자동 설정)
        updated_at (datetime): 마지막 수정 시각 (자동 갱신)

    Indexes:
        - id: PRIMARY KEY (자동 인덱스)
        - email: 로그인 시 빠른 검색을 위한 인덱스
        - username: 로그인 시 빠른 검색을 위한 인덱스

    Note:
        - email과 username은 모두 로그인 ID로 사용 가능
        - 비밀번호는 절대 평문으로 저장하지 않음 (hashed_password)
        - is_active=False로 설정하면 사용자를 삭제하지 않고 비활성화 가능
    """

    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    email = Column(String(255), unique=True, index=True, nullable=False)
    username = Column(String(100), unique=True, index=True, nullable=False)
    hashed_password = Column(String(255), nullable=False)
    full_name = Column(String(255), nullable=True)
    is_active = Column(Boolean, default=True)
    is_superuser = Column(Boolean, default=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    def __repr__(self):
        """객체의 문자열 표현

        디버깅 시 객체를 출력하면 이 메서드의 결과가 표시됩니다.

        Returns:
            str: 사용자 정보를 포함한 문자열

        Example:
            >>> user = db.query(User).first()
            >>> print(user)
            <User(id=1, email=user@example.com, username=testuser)>
        """
        return f"<User(id={self.id}, email={self.email}, username={self.username})>"
