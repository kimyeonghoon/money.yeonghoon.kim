"""
FastAPI 애플리케이션 진입점

이 파일은 FastAPI 애플리케이션을 초기화하고 설정합니다:
- 데이터베이스 테이블 자동 생성
- CORS 미들웨어 설정
- API 라우터 등록
- 기본 헬스체크 엔드포인트 제공

실행 방법:
    uvicorn app.main:app --reload

API 문서 확인:
    http://localhost:8000/docs (Swagger UI)
    http://localhost:8000/redoc (ReDoc)
"""

from fastapi import FastAPI, Depends
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from sqlalchemy import text
from app.config import settings
from app.database import engine, Base, get_db
from app.api.v1 import auth, users, fixed_expenses
from app.core.logging import setup_logging, get_logger
import sentry_sdk
from sentry_sdk.integrations.fastapi import FastApiIntegration

setup_logging("INFO")
logger = get_logger(__name__)

if settings.SENTRY_DSN:
    sentry_sdk.init(
        dsn=settings.SENTRY_DSN,
        environment=settings.ENVIRONMENT,
        integrations=[FastApiIntegration()],
        traces_sample_rate=0.1,
        profiles_sample_rate=0.1,
    )
    logger.info("Sentry initialized", extra={"environment": settings.ENVIRONMENT})

Base.metadata.create_all(bind=engine)

logger.info(
    "Application starting",
    extra={"project": settings.PROJECT_NAME, "version": settings.VERSION},
)

app = FastAPI(
    title=settings.PROJECT_NAME,
    version=settings.VERSION,
    description="""
## FastAPI + React Native 풀스택 템플릿

Docker 기반 개발 환경을 제공하는 프로덕션 레벨 템플릿입니다.

### 주요 기능

* **인증 시스템**: JWT 기반 (Access + Refresh 토큰)
* **사용자 관리**: CRUD 작업, 프로필 관리
* **타입 안전**: Pydantic 스키마 검증
* **테스트**: TDD 기반, pytest + 80% 커버리지
* **보안**: bcrypt 해싱, ORM, 입력 검증

### 기술 스택

* **Backend**: FastAPI 0.115.6 + Python 3.11
* **Database**: MySQL 8.0+ (SQLAlchemy 2.0.36)
* **Auth**: JWT + bcrypt
* **Testing**: pytest + coverage
* **Container**: Docker + Docker Compose

### 문서

* [아키텍처 다이어그램](/docs/ARCHITECTURE.md)
* [데이터베이스 스키마](/docs/DATABASE_SCHEMA.md)
* [TDD 가이드](/docs/TDD.md)
* [보안 가이드](/docs/SECURITY.md)

### 인증 방법

대부분의 API는 JWT 인증이 필요합니다:

1. `/api/v1/auth/login`으로 로그인
2. 응답에서 `access_token` 획득
3. `Authorization: Bearer {access_token}` 헤더와 함께 요청

### 지원

* GitHub: [Repository URL]
* Issues: [Issues URL]
    """,
    openapi_url=f"{settings.API_V1_STR}/openapi.json",
    docs_url="/docs",
    redoc_url="/redoc",
    openapi_tags=[
        {
            "name": "auth",
            "description": "인증 관련 API - 회원가입, 로그인, 토큰 관리",
        },
        {
            "name": "users",
            "description": "사용자 관리 API - 프로필 조회, 수정 (인증 필요)",
        },
        {
            "name": "fixed-expenses",
            "description": "고정지출 관리 API - 항목 관리, 월별 기록, 요약 (인증 필요)",
        },
    ],
    contact={
        "name": "API Support",
        "email": "support@example.com",
    },
    license_info={
        "name": "MIT License",
        "url": "https://opensource.org/licenses/MIT",
    },
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.BACKEND_CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(auth.router, prefix=f"{settings.API_V1_STR}/auth", tags=["auth"])
app.include_router(users.router, prefix=f"{settings.API_V1_STR}/users", tags=["users"])
app.include_router(fixed_expenses.router, prefix=f"{settings.API_V1_STR}/fixed-expenses", tags=["fixed-expenses"])


@app.get("/")
async def root():
    """루트 엔드포인트

    API의 기본 정보를 반환합니다.
    서버가 정상 작동하는지 빠르게 확인할 수 있습니다.

    Returns:
        dict: API 기본 정보
            - message: 환영 메시지
            - version: API 버전
            - docs: API 문서 경로

    Example:
        GET http://localhost:8000/

        Response:
        {
            "message": "FastAPI Backend API",
            "version": "1.0.0",
            "docs": "/docs"
        }
    """
    return {
        "message": "FastAPI Backend API",
        "version": settings.VERSION,
        "docs": "/docs",
    }


@app.get("/health")
async def health_check(db: Session = Depends(get_db)):
    """헬스체크 엔드포인트

    서버 및 데이터베이스 상태를 확인하는 엔드포인트입니다.
    로드밸런서나 모니터링 도구에서 사용합니다.

    Args:
        db: 데이터베이스 세션 (자동 주입)

    Returns:
        dict: 서버 및 DB 상태
            - status: "healthy" 또는 "unhealthy"
            - database: "connected" 또는 "disconnected"
            - version: API 버전

    Example:
        GET http://localhost:8000/health

        Response (정상):
        {
            "status": "healthy",
            "database": "connected",
            "version": "1.0.0"
        }

        Response (DB 에러):
        {
            "status": "unhealthy",
            "database": "disconnected",
            "version": "1.0.0",
            "error": "Connection refused"
        }

    Note:
        - 인증이 필요 없는 공개 엔드포인트
        - 로드밸런서에서 이 엔드포인트로 서버 상태 확인 권장
        - DB 연결 실패 시에도 200 OK 반환 (상태는 unhealthy)
    """
    health_status = {"status": "healthy", "version": settings.VERSION}

    try:
        db.execute(text("SELECT 1"))
        health_status["database"] = "connected"
        logger.info("Health check passed")
    except Exception as e:
        health_status["status"] = "unhealthy"
        health_status["database"] = "disconnected"
        health_status["error"] = str(e)
        logger.error("Health check failed", extra={"error": str(e)})

    return health_status
