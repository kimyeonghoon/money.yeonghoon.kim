# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned (v2.0.0)
- Redis 캐싱 통합
- Celery 비동기 작업
- 인증 템플릿 완성 (templates/)
- 상태 관리 라이브러리 (Zustand)
- 다크 모드 지원

## [1.0.0] - 2025-10-18

### 🎉 Production Ready Release

이 릴리스로 템플릿이 **프로덕션 환경에서 사용 가능한 수준**으로 완성되었습니다.

### Status
- **✅ Production Ready**: 프로덕션 필수 인프라 완성
- **✅ Stable API**: v1.x는 하위 호환성 유지, Breaking Change는 v2.0.0에서
- **✅ Complete Documentation**: 14개 가이드 + 11개 확장 기능 가이드
- **⚠️ Community Feedback Welcome**: 실제 사용 경험을 공유해주세요!

### Core Features

**백엔드:**
- FastAPI 0.115.6 + Python 3.11
- JWT 인증 (Access 30분 + Refresh 7일)
- Alembic 1.14.0 데이터베이스 마이그레이션
- Sentry 2.19.2 에러 트래킹
- SQLAlchemy 2.0.36 + MySQL 8.0
- Pydantic v2 검증
- pytest 82% 커버리지

**프론트엔드:**
- React Native 0.78.3
- React 19.0.0 (최신 메이저 버전)
- React Native Web 0.21.2 (웹 지원)
- TypeScript 5.0.4 엄격 모드
- Webpack 5.102.1 웹 번들링

**인프라:**
- Docker + Docker Compose (dev/prod)
- WiFi 디버깅 지원
- Hot Reload (백엔드/프론트엔드)

**문서:**
- 14개 상세 가이드 문서
- EXTENSIONS.md (11개 확장 기능 가이드)
- 완전한 API 문서 (Swagger UI)
- TDD, 보안, 아키텍처 가이드

### Philosophy

**"최소한의 견고한 기반"**
- 핵심 기능만 포함 (3.8MB 소스 코드)
- 프로덕션 필수 인프라 완비
- 확장 기능은 EXTENSIONS.md 참고
- 가볍고 명확한 구조

### Version Policy

```
v1.x.x - 하위 호환 유지
├─ v1.1.0: 새 기능 추가 (하위 호환)
├─ v1.0.1: 버그 수정
└─ Breaking Change 없음

v2.0.0 - Breaking Change 허용
└─ API 대규모 개편, 구조 변경
```

### Upgrade from v0.9.x

v0.9.7에서 v1.0.0으로 변경된 사항:
- 버전 번호만 변경 (기능 변경 없음)
- README.md 업데이트 (프로덕션 상태 명시)
- 버전 정책 문서화

v0.9.x 사용자는 **별도의 마이그레이션 작업 없이** v1.0.0을 사용할 수 있습니다.

### Feedback & Contributing

실제 프로젝트에서 사용하신 경험을 공유해주세요!

- 버그 제보: [GitHub Issues](https://github.com/kimyeonghoon/fullstack_template/issues)
- 기능 제안: [GitHub Issues](https://github.com/kimyeonghoon/fullstack_template/issues)
- 질문/토론: [Discussions](https://github.com/kimyeonghoon/fullstack_template/discussions)

---

## [0.9.7] - 2025-10-18

### Added
- **Alembic 1.14.0** 데이터베이스 마이그레이션 설정
- **Sentry 2.19.2** 에러 트래킹 통합
- Alembic 사용 가이드 (backend/alembic/README.md)
- 프로덕션 필수 인프라 완성

### Changed
- requirements.txt: alembic, sentry-sdk 추가
- app/config.py: SENTRY_DSN, ENVIRONMENT 설정 추가
- app/main.py: Sentry 초기화 코드 추가
- .env.example: Sentry 설정 예시 추가

### Documentation
- EXTENSIONS.md: 11개 확장 기능 가이드 추가 (v0.9.6)
- 프로덕션 준비 완료: DB 마이그레이션 + 에러 모니터링

## [0.9.6] - 2025-10-18

### Added
- **EXTENSIONS.md**: 11개 확장 기능 구현 가이드
  - 파일 업로드 (S3/MinIO)
  - 이메일 인증
  - 비밀번호 재설정
  - 소셜 로그인 (OAuth 2.0)
  - 페이지네이션 (Offset/Cursor)
  - 검색/필터링
  - 푸시 알림 (FCM)
  - Sentry 에러 트래킹
  - Admin 대시보드
  - Redis 캐싱
  - Celery 배치 작업
- TDD 단계별 예제 코드
- 우선순위 로드맵 (Phase 1/2/3)
- 예상 소요 시간 명시

### Philosophy
- "최소한의 견고한 기반" 템플릿으로 확립
- 확장 기능은 EXTENSIONS.md 참고하여 선택 추가

## [0.9.5] - 2025-10-18

### Changed
- 문서 현행화: React Native 0.78.3, React 19.0.0 반영
- VERSIONS.md, README.md, CHANGELOG.md, ARCHITECTURE.md 업데이트

## [0.9.0] - 2025-10-18

### Added
- **React Native 0.78.3** 업그레이드
- **React 19.0.0** 통합 (메이저 업데이트)
- **React Native Web 0.21.2** 지원 추가
- **Webpack 5.102.1** 웹 번들링
- TypeScript 5.0.4 엄격 모드
- Android Gradle 8.6.0, Gradle 8.12
- MobileApp 프로젝트 초기화
- 인증 템플릿 파일 (templates/)
- React Native Web 엔트리포인트 (index.web.js)
- Webpack Dev Server 설정

### Changed
- 프론트엔드 구조 개편 (templates → MobileApp)
- Node.js 최소 버전: 18+ (이전 20+)
- package.json 버전: 0.9.0

### Documentation
- VERSIONS.md: 패키지 버전 전면 업데이트
- README.md: React Native 0.78.3 반영
- 문서 현행화 완료

## [0.8.0] - 2025-01-17

### Added
- FastAPI 0.115.6 + Python 3.11 백엔드
- React Native + TypeScript 프론트엔드 (초기 버전)
- MySQL 8.0 데이터베이스
- Docker + Docker Compose 개발 환경
- JWT 인증 시스템 (Access + Refresh 토큰)
- 사용자 관리 API (회원가입, 로그인, 프로필)
- SQLAlchemy 2.0.36 ORM
- Pydantic v2 스키마 검증
- pytest 테스트 프레임워크 (커버리지 80%+)
- bcrypt 비밀번호 해싱
- WiFi 디버깅 지원
- CORS 미들웨어 설정
- 환경 변수 기반 설정 (.env)
- API 문서 자동 생성 (Swagger UI, ReDoc)

### Documentation
- README.md: 프로젝트 개요 및 시작 가이드
- CLAUDE.md: AI 협업 및 개발 규칙 (67개 규칙)
- docs/ARCHITECTURE.md: 시스템 아키텍처 다이어그램
- docs/DATABASE_SCHEMA.md: 데이터베이스 스키마 및 ER 다이어그램
- docs/TDD.md: 테스트 주도 개발 가이드
- docs/SECURITY.md: 보안 가이드
- docs/DEVELOPMENT.md: 개발 환경 설정 가이드
- docs/GETTING_STARTED.md: 신규 프로젝트 시작 가이드
- docs/WIFI_DEBUGGING.md: WiFi 디버깅 설정
- docs/WHY_DOCKER.md: Docker 개발의 필요성
- docs/VERSIONS.md: 패키지 버전 정보

### Infrastructure
- Docker 컨테이너 구성 (backend, frontend, mysql)
- Volume 마운트로 Hot Reload 지원
- MySQL 설정 파일 (my.cnf)
- pytest 테스트 환경 (SQLite)
- Pre-commit hooks 설정

### Security
- OWASP Top 10 대응
- SQL Injection 방지 (ORM 사용)
- 비밀번호 평문 저장 금지
- JWT 토큰 기반 인증
- 환경 변수로 민감 정보 관리
- 입력 검증 (Pydantic)
- CORS 설정

---

## 버전 규칙

이 프로젝트는 [Semantic Versioning](https://semver.org/)을 따릅니다:

- **MAJOR** (1.x.x): 호환되지 않는 API 변경
- **MINOR** (x.1.x): 하위 호환되는 기능 추가
- **PATCH** (x.x.1): 하위 호환되는 버그 수정

## 변경 내역 작성 규칙

### 카테고리

변경사항은 다음 카테고리로 분류합니다:

- **Added**: 새로운 기능 추가
- **Changed**: 기존 기능 변경
- **Deprecated**: 곧 제거될 기능 (하위 호환 유지)
- **Removed**: 제거된 기능 (Breaking Change)
- **Fixed**: 버그 수정
- **Security**: 보안 관련 변경

### 작성 예시

```markdown
## [1.1.0] - 2025-02-01

### Added
- 게시글 CRUD API 추가 (#12)
- 댓글 기능 구현 (#15)
- 파일 업로드 지원 (AWS S3) (#18)

### Changed
- JWT 토큰 만료 시간 변경: 30분 → 1시간 (#20)
- 사용자 프로필 응답 스키마 개선 (#22)

### Fixed
- 로그인 시 비활성 사용자 에러 메시지 개선 (#25)
- MySQL 연결 풀 설정 버그 수정 (#28)

### Security
- 비밀번호 해싱 알고리즘 강화 (bcrypt rounds: 12 → 14) (#30)
```

### Commit Message 연동

각 변경사항에는 관련 이슈 번호나 커밋을 연결합니다:
- `(#12)`: GitHub Issue 번호
- `(abc1234)`: Commit SHA

### 릴리스 날짜

모든 릴리스에는 날짜를 명시합니다 (ISO 8601: YYYY-MM-DD):
```markdown
## [1.2.0] - 2025-03-15
```

## 참고 자료

- [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
- [Semantic Versioning](https://semver.org/spec/v2.0.0.html)
- [Conventional Commits](https://www.conventionalcommits.org/)
