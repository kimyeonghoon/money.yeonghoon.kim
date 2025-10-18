# 패키지 버전 정보

이 문서는 템플릿에서 사용하는 모든 주요 패키지의 버전 정보를 포함합니다.

## 백엔드 (Python)

### 코어 프레임워크
- **Python**: 3.11+
- **FastAPI**: 0.115.6
- **Uvicorn**: 0.34.0
- **Gunicorn**: 23.0.0

### 데이터베이스
- **SQLAlchemy**: 2.0.36
- **PyMySQL**: 1.1.1
- **MySQL**: 8.0+
- **Alembic**: 1.14.0 (마이그레이션)

### 인증 및 보안
- **python-jose[cryptography]**: 3.3.0
- **passlib[bcrypt]**: 1.7.4
- **cryptography**: 44.0.0

### 데이터 검증
- **Pydantic**: 2.10.3
- **pydantic-settings**: 2.7.0
- **email-validator**: 2.2.0

### 기타
- **python-multipart**: 0.0.20 (파일 업로드)
- **python-dotenv**: 1.0.1 (환경 변수)
- **sentry-sdk[fastapi]**: 2.19.2 (에러 트래킹)

## 프론트엔드 (JavaScript/TypeScript)

### 코어
- **Node.js**: 18+ (LTS)
- **React**: 19.0.0
- **React Native**: 0.78.3
- **React DOM**: 19.0.0
- **React Native Web**: 0.21.2
- **TypeScript**: 5.0.4

### 빌드 도구
- **@babel/core**: 7.28.4
- **@react-native/babel-preset**: 0.78.3
- **@react-native/metro-config**: 0.78.3
- **Webpack**: 5.102.1
- **Webpack Dev Server**: 5.2.2
- **Webpack CLI**: 6.0.1
- **HTML Webpack Plugin**: 5.6.4
- **Babel Loader**: 10.0.0

### React Native 도구
- **@react-native-community/cli**: 15.0.1
- **@react-native-community/cli-platform-android**: 15.0.1
- **@react-native-community/cli-platform-ios**: 15.0.1
- **@react-native/eslint-config**: 0.78.3
- **@react-native/typescript-config**: 0.78.3

### 개발 도구
- **ESLint**: 8.19.0
- **Prettier**: 2.8.8
- **Jest**: 29.6.3
- **@types/jest**: 29.5.13
- **@types/react**: 19.0.0
- **@types/react-test-renderer**: 19.0.0
- **react-test-renderer**: 19.0.0

## 테스트 및 코드 품질

### 백엔드
- **pytest**: 8.3.4
- **pytest-cov**: 6.0.0 (커버리지)
- **pytest-asyncio**: 0.24.0 (비동기 테스트)
- **httpx**: 0.28.1 (API 테스트)
- **Black**: 자동 포맷팅 (120자)
- **Flake8**: 린트
- **Mypy**: 타입 체크
- **slowapi**: 0.1.9 (Rate Limiting)

### 프론트엔드
- **Metro**: 0.78.3 (React Native 번들러)
- **ESLint**: 8.19.0 (린트)
- **Prettier**: 2.8.8 (포맷팅)
- **Jest**: 29.6.3 (테스트)
- **Webpack**: 5.102.1 (웹 번들러)

## 인프라

### 컨테이너
- **Docker**: 20.10+
- **Docker Compose**: 2.0+

### 데이터베이스
- **MySQL**: 8.0

## 버전 업데이트 주기

이 템플릿은 다음 일정으로 업데이트됩니다:

- **주요 버전 업데이트**: 분기별 (3개월)
- **마이너 버전 업데이트**: 월별
- **보안 패치**: 즉시

## 호환성 매트릭스

### Python 버전
- Python 3.11, 3.12 호환 확인됨

### Node.js 버전
- Node.js 20 LTS 권장
- Node.js 18 LTS 호환

### React Native 버전
- 0.78.3 테스트 완료
- React 19.0.0 통합
- React Native Web 0.21.2 지원

## 중요 변경사항

### 2025년 10월 업데이트 (v0.9.7)

**프로덕션 필수 인프라 추가:**
- Alembic 1.14.0 - 데이터베이스 마이그레이션 시스템
- Sentry 2.19.2 - 에러 트래킹 및 모니터링
- 프로덕션 준비 완료 (DB 변경 관리 + 에러 모니터링)

**문서:**
- EXTENSIONS.md - 11개 확장 기능 가이드 (v0.9.6)
- backend/alembic/README.md - 마이그레이션 사용 가이드

### 2025년 10월 업데이트 (v0.9.0)

**백엔드:**
- FastAPI 0.115.6 (안정 버전)
- Pydantic 2.10.3 - `model_config` 사용
- SQLAlchemy 2.0.36
- Uvicorn 0.34.0
- Gunicorn 23.0.0
- cryptography 44.0.0
- pytest 8.3.4 + 커버리지 80% 필수
- slowapi 0.1.9 (Rate Limiting)

**프론트엔드:**
- **React Native 0.78.3** (최신)
- **React 19.0.0** (메이저 업그레이드)
- **React Native Web 0.21.2** (웹 지원 추가)
- TypeScript 5.0.4
- Webpack 5.102.1 (웹 번들링)
- Jest 29.6.3 (테스트)

### 주요 변경사항 상세

#### Pydantic v2 마이그레이션
- `Config` 클래스 → `model_config` (SettingsConfigDict)
- `@computed_field` 데코레이터 추가
- 타입 힌트 개선

#### React Native 0.78.3 주요 변경사항
- React 19.0.0 통합 (메이저 업데이트)
- React Native Web 지원 추가 (0.21.2)
- Webpack 5 기반 웹 번들링
- TypeScript 5.0.4 엄격 모드
- 성능 최적화 (`useCallback`, `useMemo`)
- Android Gradle 8.6.0, Gradle 8.12

## 다음 업데이트 예정 (v2.0.0)

### 백엔드
- [ ] Redis 캐싱 통합
- [ ] Celery 비동기 작업
- [ ] 추가 API 엔드포인트 예제

### 프론트엔드
- [ ] 인증 템플릿 완성 (templates/)
- [ ] 상태 관리 라이브러리 추가 (Zustand)
- [ ] 오프라인 지원 (AsyncStorage)
- [ ] 푸시 알림 (FCM)
- [ ] 다크 모드 지원

## 문의

버전 관련 문제나 제안사항이 있으시면 GitHub Issues에 등록해주세요.
