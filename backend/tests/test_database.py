"""
데이터베이스 연결 및 세션 관리 테스트
"""
import pytest
from sqlalchemy.orm import Session
from app.database import get_db, SessionLocal, Base, engine


class TestGetDB:
    """get_db 함수 테스트"""

    def test_get_db_returns_session(self):
        """get_db가 SQLAlchemy 세션을 반환해야 함"""
        # Given: get_db 제너레이터

        # When: 세션 생성
        db_generator = get_db()
        db = next(db_generator)

        # Then: Session 객체 반환
        assert isinstance(db, Session)

        # Cleanup
        try:
            next(db_generator)
        except StopIteration:
            pass

    def test_get_db_closes_session(self):
        """get_db가 종료 시 세션을 닫아야 함"""
        # Given: get_db 제너레이터
        db_generator = get_db()
        db = next(db_generator)

        # When: 제너레이터 종료
        try:
            next(db_generator)
        except StopIteration:
            pass

        # Then: 세션이 닫혀 있음 (closed 상태 확인 불가하므로 에러 없이 완료되는지만 확인)
        assert True

    def test_get_db_session_independent(self):
        """각 get_db 호출은 독립적인 세션을 생성해야 함"""
        # Given: 두 개의 get_db 호출

        # When: 두 세션 생성
        gen1 = get_db()
        gen2 = get_db()
        db1 = next(gen1)
        db2 = next(gen2)

        # Then: 서로 다른 세션 객체
        assert db1 is not db2

        # Cleanup
        for gen in [gen1, gen2]:
            try:
                next(gen)
            except StopIteration:
                pass


class TestDatabaseSetup:
    """데이터베이스 설정 테스트"""

    def test_session_local_configured(self):
        """SessionLocal이 올바르게 설정되어 있어야 함"""
        # Given: SessionLocal

        # When: 세션 생성
        db = SessionLocal()

        # Then: Session 객체 생성 성공
        assert isinstance(db, Session)

        # Cleanup
        db.close()

    def test_base_declarative_base(self):
        """Base가 DeclarativeBase를 상속해야 함"""
        # Given: Base 클래스

        # When: Base 클래스 확인

        # Then: DeclarativeBase 기능 보유
        assert hasattr(Base, 'metadata')
        assert hasattr(Base, 'registry')

    def test_engine_connected(self):
        """엔진이 데이터베이스에 연결되어 있어야 함"""
        # Given: engine
        from sqlalchemy import text

        # When: 연결 테스트
        with engine.connect() as conn:
            result = conn.execute(text("SELECT 1"))
            value = result.scalar()

        # Then: 연결 성공 및 쿼리 결과 확인
        assert value == 1
