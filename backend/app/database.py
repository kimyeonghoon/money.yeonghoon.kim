"""
데이터베이스 연결 및 세션 관리 모듈

이 모듈은 SQLAlchemy를 사용한 데이터베이스 연결을 설정하고 관리합니다.
애플리케이션 전체에서 사용할 DB 엔진, 세션, Base 클래스를 제공합니다.

사용 예시:
    from app.database import get_db, Base
    from app.models.user import User  # Base를 상속한 모델

    # FastAPI 엔드포인트에서
    @app.get("/users")
    def get_users(db: Session = Depends(get_db)):
        users = db.query(User).all()
        return users
"""

from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, DeclarativeBase
from app.config import settings

engine = create_engine(
    settings.DATABASE_URL, pool_pre_ping=True, pool_recycle=3600, echo=True
)

SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)


class Base(DeclarativeBase):
    pass


def get_db():
    """데이터베이스 세션 의존성 함수

    FastAPI의 Depends()와 함께 사용하여 각 요청에 DB 세션을 제공합니다.
    요청이 끝나면 자동으로 세션을 닫아 리소스 누수를 방지합니다.

    Yields:
        Session: SQLAlchemy 데이터베이스 세션

    Example:
        @app.get("/users")
        def get_users(db: Session = Depends(get_db)):
            # db 세션 사용
            users = db.query(User).all()
            return users
        # 함수 종료 시 자동으로 db.close() 호출됨

    Note:
        - yield를 사용하여 세션을 제공하고, finally 블록에서 정리
        - 각 요청마다 독립적인 세션을 생성하여 트랜잭션 격리 보장
        - 에러 발생 시에도 finally 블록이 실행되어 세션이 안전하게 닫힘
    """
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
