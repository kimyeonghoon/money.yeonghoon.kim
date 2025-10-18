"""
애플리케이션 설정 관리 모듈

이 모듈은 환경 변수(.env 파일)에서 설정을 로드하고 검증합니다.
Pydantic의 BaseSettings를 사용하여 타입 안전성과 자동 검증을 제공합니다.

사용 예시:
    from app.config import settings

    print(settings.PROJECT_NAME)
    print(settings.DATABASE_URL)  # 자동으로 생성된 DB URL
"""

from typing import List, Union
from pydantic_settings import BaseSettings, SettingsConfigDict
from pydantic import field_validator, computed_field


class Settings(BaseSettings):
    """애플리케이션 설정 클래스

    환경 변수(.env 파일)에서 설정을 자동으로 로드합니다.
    모든 설정은 타입이 검증되며, 잘못된 값이 있으면 애플리케이션 시작 시 오류가 발생합니다.

    Attributes:
        PROJECT_NAME: 프로젝트 이름 (API 문서에 표시됨)
        VERSION: API 버전 번호
        API_V1_STR: API v1 엔드포인트 프리픽스
        SECRET_KEY: JWT 토큰 서명에 사용할 비밀 키 (반드시 변경 필요!)
        ALGORITHM: JWT 암호화 알고리즘
        ACCESS_TOKEN_EXPIRE_MINUTES: 액세스 토큰 만료 시간 (분)
        REFRESH_TOKEN_EXPIRE_DAYS: 리프레시 토큰 만료 시간 (일)
        MYSQL_HOST: MySQL 서버 호스트
        MYSQL_PORT: MySQL 서버 포트
        MYSQL_USER: MySQL 사용자명
        MYSQL_PASSWORD: MySQL 비밀번호
        MYSQL_DATABASE: 사용할 데이터베이스 이름
        BACKEND_CORS_ORIGINS: CORS 허용 오리진 리스트

    Note:
        - .env 파일이 없으면 기본값이 사용됩니다
        - 프로덕션 환경에서는 반드시 SECRET_KEY를 변경해야 합니다
        - BACKEND_CORS_ORIGINS는 콤마로 구분된 문자열 또는 리스트로 설정 가능
    """

    model_config = SettingsConfigDict(
        env_file=".env", env_file_encoding="utf-8", case_sensitive=True, extra="ignore"
    )

    PROJECT_NAME: str = "FastAPI Backend"
    VERSION: str = "1.0.0"
    API_V1_STR: str = "/api/v1"

    SECRET_KEY: str = "your-secret-key-here-please-change-in-production"
    ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 30
    REFRESH_TOKEN_EXPIRE_DAYS: int = 7

    @field_validator("SECRET_KEY")
    @classmethod
    def validate_secret_key(cls, v: str) -> str:
        """SECRET_KEY 강도 및 보안 검증

        프로덕션 환경에서 약한 SECRET_KEY 사용을 방지합니다.
        최소 32자 이상의 랜덤 문자열을 사용해야 합니다.

        Args:
            v: 검증할 SECRET_KEY 값

        Returns:
            str: 검증된 SECRET_KEY

        Raises:
            ValueError: SECRET_KEY가 기본값이거나 32자 미만인 경우

        Example:
            강력한 SECRET_KEY 생성:
                openssl rand -hex 32

        Note:
            - 개발 환경에서는 경고만 출력
            - 프로덕션에서는 반드시 변경 필요
        """
        if "your-secret-key" in v.lower() or "change" in v.lower():
            import warnings

            warnings.warn(
                "⚠️  프로덕션에서는 반드시 SECRET_KEY를 변경하세요! "
                "'openssl rand -hex 32' 명령어로 생성 가능합니다.",
                UserWarning,
            )

        if len(v) < 32:
            raise ValueError(
                f"SECRET_KEY는 최소 32자 이상이어야 합니다. "
                f"현재: {len(v)}자. "
                f"'openssl rand -hex 32' 명령어로 생성하세요."
            )

        return v

    MYSQL_HOST: str = "localhost"
    MYSQL_PORT: int = 3306
    MYSQL_USER: str = "root"
    MYSQL_PASSWORD: str = "password"
    MYSQL_DATABASE: str = "appdb"

    @computed_field
    @property
    def DATABASE_URL(self) -> str:
        """데이터베이스 연결 URL을 자동으로 생성

        SQLAlchemy가 사용하는 형식의 DB 연결 문자열을 생성합니다.
        개별 설정(HOST, PORT, USER 등)을 기반으로 자동 조합됩니다.

        Returns:
            str: SQLAlchemy 연결 URL
                형식: mysql+pymysql://user:password@host:port/database

        Example:
            >>> settings.DATABASE_URL
            'mysql+pymysql://root:password@localhost:3306/appdb'

        Note:
            - pymysql 드라이버 사용 (순수 Python, Docker 환경에 적합)
            - 이 필드는 계산 속성이므로 개별 설정이 변경되면 자동으로 갱신됩니다
        """
        return (
            f"mysql+pymysql://{self.MYSQL_USER}:{self.MYSQL_PASSWORD}"
            f"@{self.MYSQL_HOST}:{self.MYSQL_PORT}/{self.MYSQL_DATABASE}"
        )

    BACKEND_CORS_ORIGINS: Union[str, List[str]] = ["*"]

    SENTRY_DSN: str = ""
    ENVIRONMENT: str = "development"

    @field_validator("BACKEND_CORS_ORIGINS", mode="before")
    @classmethod
    def assemble_cors_origins(cls, v: Union[str, List[str]]) -> Union[str, List[str]]:
        """CORS origins를 리스트로 정규화

        환경 변수에서 문자열로 받은 값을 리스트로 변환합니다.
        콤마로 구분된 문자열을 지원하여 .env 파일 작성을 편리하게 합니다.

        Args:
            v: 검증할 값 (문자열 또는 리스트)

        Returns:
            List[str]: 정규화된 오리진 리스트

        Example:
            .env 파일에서:
                BACKEND_CORS_ORIGINS="http://localhost:3000,http://localhost:8081"

            변환 후:
                ["http://localhost:3000", "http://localhost:8081"]

        Note:
            - mode="before": Pydantic이 타입 검증하기 전에 실행
            - 각 오리진의 앞뒤 공백은 자동으로 제거됩니다
        """
        if isinstance(v, str):
            return [i.strip() for i in v.split(",")]
        return v


settings = Settings()
