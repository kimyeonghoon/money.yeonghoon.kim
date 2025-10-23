# 풀스택 개발 템플릿

> **✨ v1.0.0 - Production Ready!**
> FastAPI + React Native를 사용한 모바일 앱 개발 템플릿입니다.

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/kimyeonghoon/fullstack_template/releases/tag/v1.0.0)
[![Production Ready](https://img.shields.io/badge/status-production%20ready-green.svg)]()
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 🎯 Status: Production Ready

이 템플릿은 **프로덕션 환경에서 사용 가능**한 수준으로 개발되었습니다:

- ✅ **프로덕션 필수 인프라**: Alembic 마이그레이션 + Sentry 에러 트래킹
- ✅ **안정적인 API**: v1.x는 하위 호환성 유지 (Breaking Change는 v2.0.0에서)
- ✅ **완전한 문서화**: 14개 가이드 + 11개 확장 기능 가이드
- ✅ **실전 적용 가능**: 새 프로젝트 시작에 바로 사용 가능
- ⚠️ **피드백 환영**: 실사용 경험을 공유해주세요!

### 버전 정책 (Semantic Versioning)

```
v1.x.x - 하위 호환 유지
├─ v1.1.0: 새 기능 추가 (하위 호환)
├─ v1.0.1: 버그 수정
└─ Breaking Change 없음

v2.0.0 - Breaking Change 허용
└─ API 대규모 개편, 구조 변경
```

### 💬 피드백 및 기여

실제 프로젝트에 이 템플릿을 사용하셨나요? 여러분의 경험을 공유해주세요!

- **버그 제보**: [GitHub Issues](https://github.com/kimyeonghoon/fullstack_template/issues)
- **기능 제안**: [GitHub Issues](https://github.com/kimyeonghoon/fullstack_template/issues)
- **사용 사례 공유**: [Discussions](https://github.com/kimyeonghoon/fullstack_template/discussions)
- **질문**: [Discussions Q&A](https://github.com/kimyeonghoon/fullstack_template/discussions/categories/q-a)

> **🚀 새 프로젝트를 시작하시나요?** [시작 가이드](docs/GETTING_STARTED.md)를 먼저 읽어보세요!

## 기술 스택

### 백엔드
- **Python**: 3.11+
- **FastAPI**: 0.115.6 - Python 기반 고성능 웹 프레임워크
- **SQLAlchemy**: 2.0.36 - Python ORM
- **Alembic**: 1.14.0 - 데이터베이스 마이그레이션
- **MySQL**: 8.0+ - 관계형 데이터베이스
- **PyMySQL**: 1.1.1 - MySQL 드라이버
- **JWT**: python-jose 3.3.0 - 토큰 기반 인증
- **Sentry**: 2.19.2 - 에러 트래킹 및 모니터링
- **Uvicorn**: 0.34.0 / **Gunicorn**: 23.0.0 - ASGI 서버
- **Pydantic**: 2.10.3 - 데이터 검증
- **Docker**: 컨테이너화

### 프론트엔드
- **Node.js**: 18+
- **React**: 19.0.0 - 최신 React 메이저 버전
- **React Native**: 0.78.3 - 크로스 플랫폼 모바일 프레임워크
- **React Native Web**: 0.21.2 - 웹 지원
- **TypeScript**: 5.0.4 - 정적 타입 검사
- **Webpack**: 5.102.1 - 웹 번들러
- **Jest**: 29.6.3 - 테스트 프레임워크
- **Metro**: 0.78.3 - React Native 번들러

## 📚 문서

### 필수 문서
- **[시작하기](docs/GETTING_STARTED.md)**: 템플릿으로 새 프로젝트 시작하기 ⭐
- **[TDD 가이드](docs/TDD.md)**: 테스트 주도 개발 (Red-Green-Refactor) ⭐
- **[Docker 필수 이유](docs/WHY_DOCKER.md)**: 왜 Docker로 개발해야 하나요? 🐳
- **[WiFi 디버깅](docs/WIFI_DEBUGGING.md)**: 완전 Docker 개발 (Node.js 로컬 설치 불필요) 📱

### 참고 문서
- **[개발 환경 가이드](docs/DEVELOPMENT.md)**: Docker 개발 환경 설정
- **[시스템 아키텍처](docs/ARCHITECTURE.md)**: 전체 아키텍처 다이어그램 🏗️
- **[데이터베이스 스키마](docs/DATABASE_SCHEMA.md)**: ER 다이어그램 및 쿼리 패턴 🗄️
- **[확장 기능 가이드](docs/EXTENSIONS.md)**: 11개 확장 기능 구현 가이드 ⭐
- **[변경 내역](CHANGELOG.md)**: 버전별 변경사항 기록 📝
- **[헬퍼 스크립트](scripts/README.md)**: 개발 편의 스크립트
- **[패키지 버전](docs/VERSIONS.md)**: 사용 중인 패키지 버전
- **[협업 규칙](CLAUDE.md)**: Claude와 협업 시 지켜야 할 규칙 🤖

> **📖 전체 문서 목록**: [docs/README.md](docs/README.md)

## 🤖 Claude Code 통합

이 프로젝트는 Claude Code SessionStart 훅을 사용하여 **세션 시작 시 자동으로 협업 규칙(CLAUDE.md)을 로드**합니다.

**장점:**
- ✅ 매 세션마다 규칙을 수동으로 언급할 필요 없음
- ✅ 일관된 코드 품질 유지
- ✅ TDD, Docker, 타입 안전성 등 핵심 원칙 자동 준수

**설정 위치:** `.claude/settings.json`

**자세한 내용:** [.claude/README.md](.claude/README.md)

## 프로젝트 구조

```
fullstack_template/
├── docs/                       # 📚 모든 문서 (가이드, 튜토리얼)
│   ├── README.md              # 문서 목록 및 읽는 순서
│   ├── GETTING_STARTED.md     # 프로젝트 시작 가이드
│   ├── TDD.md                 # TDD 개발 가이드
│   ├── WHY_DOCKER.md          # Docker 필수 이유
│   ├── WIFI_DEBUGGING.md      # WiFi 디버깅 가이드
│   ├── DEVELOPMENT.md         # 개발 환경 설정
│   ├── ARCHITECTURE.md        # 시스템 아키텍처
│   ├── DATABASE_SCHEMA.md     # 데이터베이스 스키마
│   ├── VERSIONS.md            # 패키지 버전 정보
│   └── SECURITY.md            # 보안 가이드
├── scripts/                    # 🛠️ 개발 헬퍼 스크립트
│   ├── dev-start.sh           # 전체 개발 환경 시작
│   ├── dev-start.ps1          # (Windows PowerShell)
│   ├── adb-connect.sh         # WiFi adb 연결
│   ├── adb-troubleshoot.sh    # 문제 해결
│   ├── backup-mysql.sh        # MySQL 백업
│   ├── restore-mysql.sh       # MySQL 복원
│   ├── check-secrets.sh       # 민감 정보 검사
│   └── README.md              # 스크립트 가이드
├── .claude/                    # 🤖 Claude Code 설정 및 훅
│   ├── settings.json          # 프로젝트 설정
│   ├── hooks/                 # SessionStart 훅 (협업 규칙 자동 로드)
│   └── README.md
├── backups/                    # 🗄️ 데이터베이스 백업 파일
├── backend/                    # FastAPI 백엔드
│   ├── app/
│   │   ├── api/               # API 엔드포인트
│   │   │   ├── v1/
│   │   │   │   ├── auth.py    # 인증 관련 API
│   │   │   │   └── users.py   # 사용자 관련 API
│   │   │   └── deps.py        # API 의존성
│   │   ├── core/              # 핵심 기능
│   │   │   └── security.py    # 보안 (JWT, 비밀번호 해싱)
│   │   ├── models/            # 데이터베이스 모델
│   │   │   └── user.py
│   │   ├── schemas/           # Pydantic 스키마
│   │   │   └── user.py
│   │   ├── config.py          # 설정
│   │   ├── database.py        # 데이터베이스 연결
│   │   └── main.py            # FastAPI 앱 엔트리포인트
│   ├── mysql/
│   │   └── my.cnf             # MySQL 설정 (한국 환경 최적화)
│   ├── Dockerfile
│   ├── requirements.txt
│   └── .env.example
├── frontend/                   # React Native 프론트엔드
│   ├── MobileApp/             # React Native 프로젝트
│   │   ├── android/           # Android 네이티브 코드
│   │   ├── ios/               # iOS 네이티브 코드
│   │   ├── public/            # 웹 정적 파일
│   │   ├── __tests__/         # Jest 테스트
│   │   ├── App.tsx            # 메인 앱 컴포넌트
│   │   ├── index.js           # React Native 엔트리
│   │   ├── index.web.js       # React Native Web 엔트리
│   │   ├── package.json       # Node.js 패키지
│   │   ├── tsconfig.json      # TypeScript 설정
│   │   ├── webpack.config.js  # Webpack 설정 (웹)
│   │   └── metro.config.js    # Metro 번들러 설정
│   ├── templates/             # 인증 템플릿 파일 (참고용)
│   │   ├── api.ts             # API 클라이언트 예제
│   │   ├── authService.ts     # 인증 서비스 예제
│   │   ├── AuthContext.tsx    # 인증 컨텍스트 예제
│   │   ├── LoginScreen.tsx    # 로그인 화면 예제
│   │   └── App.tsx            # App 컴포넌트 예제
│   ├── Dockerfile.dev         # Metro 서버 Docker 이미지
│   ├── setup-frontend.sh      # 프론트엔드 초기화 스크립트
│   └── README.md
├── docker-compose.yml          # Docker Compose 설정
├── CHANGELOG.md                # 📝 버전별 변경 내역
├── CLAUDE.md                   # 🤖 Claude 협업 규칙
└── README.md                   # 📄 프로젝트 소개
```

## 빠른 시작

### 🚀 한 번에 시작하기 (권장)

```bash
# 개발 환경 전체 시작
bash scripts/dev-start.sh

# 또는 Android 기기 IP와 함께
bash scripts/dev-start.sh 192.168.0.100
```

이 스크립트가 자동으로:
- ✅ 백엔드 API 서버 시작
- ✅ Metro 서버 시작
- ✅ WiFi adb 연결
- ✅ 앱 빌드 및 설치 (선택)

**자세한 내용:** [scripts/README.md](scripts/README.md)

---

### 개발 환경 (Docker 필수)

**⭐ 백엔드는 무조건 Docker로 개발**

```bash
# 1. VS Code로 프로젝트 열기
code fullstack_template

# 2. Dev Containers 확장 설치 (처음 한 번만)
# Ctrl+Shift+X → "Dev Containers" 검색 및 설치

# 3. Dev Container에서 열기
# F1 → "Dev Containers: Reopen in Container" 선택

# 완료! 이제 컨테이너 안에서 개발할 수 있습니다.
```

**또는 Docker Compose로 개발**

```bash
# 개발 모드로 시작 (코드 변경 시 자동 리로드)
docker-compose -f docker-compose.dev.yml up -d

# API: http://localhost:8000
# API 문서: http://localhost:8000/docs
```

**자세한 내용은 [DEVELOPMENT.md](docs/DEVELOPMENT.md)를 참고하세요.**

---

### 프로덕션 배포

#### Docker Compose 사용

```bash
# 환경 변수 파일 생성
cp backend/.env.example backend/.env

# Docker Compose로 실행
docker-compose up -d

# 로그 확인
docker-compose logs -f backend
```

백엔드 API는 http://localhost:8000 에서 실행됩니다.
API 문서는 http://localhost:8000/docs 에서 확인할 수 있습니다.

### 2. 프론트엔드 설정 (선택)

> **🐳 Metro 서버는 Docker에서 실행됩니다!**
> 앱 실행(에뮬레이터/기기)만 로컬 환경이 필요합니다.

#### 사전 요구사항

**Metro 서버 (Docker):**
- Docker Desktop만 있으면 됨!

**앱 실행용 (선택):**
- **Android**: Android Studio, JDK 17, Android SDK
- **iOS** (macOS만): Xcode 14+, CocoaPods
- 자세한 설정: https://reactnative.dev/docs/environment-setup

#### 프로젝트 초기화

**Docker 환경:**
```bash
cd frontend
bash docker-setup.sh

# Docker 컨테이너에서 자동으로:
# 1. React Native 프로젝트 생성
# 2. 필요한 패키지 설치
```

#### 템플릿 파일 복사

프로젝트 초기화 후, 템플릿 파일들을 프로젝트에 복사합니다:

```bash
cd MobileApp  # 또는 생성한 프로젝트 이름

# 디렉토리 생성
mkdir -p src/services src/contexts src/screens

# 템플릿 파일 복사
cp ../templates/api.ts src/services/
cp ../templates/authService.ts src/services/
cp ../templates/AuthContext.tsx src/contexts/
cp ../templates/LoginScreen.tsx src/screens/
```

#### API URL 설정

`src/services/api.ts` 파일을 열고 API_BASE_URL을 설정합니다:

```typescript
// Android 에뮬레이터
const API_BASE_URL = 'http://10.0.2.2:8000';

// iOS 시뮬레이터
const API_BASE_URL = 'http://localhost:8000';

// 실제 기기 (같은 네트워크)
const API_BASE_URL = 'http://YOUR_COMPUTER_IP:8000';
```

#### 앱 실행

**Metro 서버 시작 (Docker):**
```bash
# 루트 디렉토리에서
docker-compose -f docker-compose.dev.yml up -d frontend
```

**앱 빌드 및 실행:**

**방법 1: 완전 Docker (WiFi 디버깅) - 권장**
```bash
# Android 기기를 WiFi 디버깅 모드로 설정 후
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555
docker-compose -f docker-compose.dev.yml exec frontend npm run android

# Node.js 로컬 설치 불필요!
# 자세한 내용: docs/WIFI_DEBUGGING.md
```

**방법 2: 로컬 Node.js 사용 (Node.js가 이미 설치된 경우)**
```bash
cd frontend/MobileApp

# Android
npm run android

# iOS (macOS만)
npm run ios
```

## API 엔드포인트

### 인증 (Auth)

- `POST /api/v1/auth/register` - 회원가입
- `POST /api/v1/auth/login` - 로그인

### 사용자 (Users)

- `GET /api/v1/users/me` - 현재 사용자 정보 조회
- `PUT /api/v1/users/me` - 현재 사용자 정보 수정
- `GET /api/v1/users/{user_id}` - 특정 사용자 정보 조회

### 고정지출 (Fixed Expenses)

- `POST /api/v1/fixed-expenses` - 고정지출 항목 생성
- `GET /api/v1/fixed-expenses` - 고정지출 항목 목록 조회
- `GET /api/v1/fixed-expenses/{id}` - 고정지출 항목 상세 조회
- `PUT /api/v1/fixed-expenses/{id}` - 고정지출 항목 수정
- `DELETE /api/v1/fixed-expenses/{id}` - 고정지출 항목 삭제
- `POST /api/v1/fixed-expenses/{id}/records` - 월별 기록 생성
- `PUT /api/v1/fixed-expenses/records/{id}/mark-paid` - 지출 완료 처리
- `GET /api/v1/fixed-expenses/summary/{year}/{month}` - 월별 요약 조회

자세한 API 문서는 http://localhost:8000/docs 에서 확인하세요.

## 환경 변수

### 백엔드 (.env)

```env
# 보안 설정
SECRET_KEY=your-secret-key-here
ALGORITHM=HS256
ACCESS_TOKEN_EXPIRE_MINUTES=30
REFRESH_TOKEN_EXPIRE_DAYS=7

# 데이터베이스
MYSQL_HOST=mysql
MYSQL_PORT=3306
MYSQL_USER=appuser
MYSQL_PASSWORD=apppassword
MYSQL_DATABASE=appdb

# CORS
BACKEND_CORS_ORIGINS=http://localhost:8081,http://localhost:19000
```

프로덕션 환경에서는 반드시 SECRET_KEY를 변경하세요!

```bash
# SECRET_KEY 생성
openssl rand -hex 32
```

### MySQL 설정

MySQL은 한국 환경에 최적화되어 있습니다:

**문자셋 및 Collation:**
- **문자셋**: utf8mb4 (이모지, 한글 등 4바이트 문자 완벽 지원)
- **Collation**: utf8mb4_unicode_ci (유니코드 정렬 규칙)

**타임존:**
- **Asia/Seoul** (KST, UTC+9)

**설정 파일:**
- `backend/mysql/my.cnf` - MySQL 서버 설정
- Docker 컨테이너 시작 시 자동으로 적용됨

**설정 확인:**
```bash
# MySQL 컨테이너 접속
docker-compose -f docker-compose.dev.yml exec mysql mysql -uappuser -p

# 문자셋 확인
SHOW VARIABLES LIKE 'character%';
SHOW VARIABLES LIKE 'collation%';

# 타임존 확인
SELECT @@system_time_zone, @@time_zone;
SELECT NOW();
```

**성능 최적화 (현재: 2GB RAM 기준):**
- InnoDB 버퍼 풀: 512MB
- 최대 연결 수: 200
- 슬로우 쿼리 로깅: 2초 이상 쿼리 기록
- 예상 메모리 사용: 1-1.5GB (일반적인 경우)

**다른 메모리 환경:**

`backend/mysql/my.cnf` 파일 상단에 메모리별 권장 설정이 주석으로 제공됩니다:
- **1GB RAM**: 최소 사양 (개발/테스트)
- **2GB RAM**: 현재 설정 (개발/소규모 프로덕션)
- **4GB RAM**: 중규모 프로덕션
- **8GB+ RAM**: 대규모 프로덕션

**커스터마이징:**
`backend/mysql/my.cnf` 파일을 수정하여 메모리 환경에 맞게 설정 변경 가능

## 개발 도구

### 백엔드 테스트

```bash
cd backend

# pytest 설치
pip install pytest pytest-asyncio httpx

# 테스트 실행
pytest
```

### 데이터베이스 마이그레이션

현재는 SQLAlchemy의 `create_all()`을 사용하여 자동으로 테이블을 생성합니다.
프로덕션 환경에서는 Alembic을 사용한 마이그레이션을 권장합니다.

```bash
pip install alembic
alembic init alembic
# alembic.ini와 env.py 설정 후
alembic revision --autogenerate -m "Initial migration"
alembic upgrade head
```

### 데이터베이스 백업 및 복원

Docker 컨테이너에서 실행 중인 MySQL을 단발적으로 백업/복원할 수 있습니다.

**백업:**
```bash
# 개발 환경 백업
bash scripts/backup-mysql.sh

# 프로덕션 환경 백업
bash scripts/backup-mysql.sh prod
```

**복원:**
```bash
# 개발 환경 복원
bash scripts/restore-mysql.sh backups/mysql_dev_20250117_143000.sql

# 프로덕션 환경 복원
bash scripts/restore-mysql.sh backups/mysql_prod_20250117_143000.sql prod
```

**자세한 내용:** [scripts/README.md](scripts/README.md#-데이터베이스-백업)

## 배포

### 백엔드 배포

Docker 이미지를 빌드하고 배포합니다:

```bash
cd backend
docker build -t myapp-backend .
docker run -p 8000:8000 --env-file .env myapp-backend
```

또는 Docker Compose를 사용:

```bash
docker-compose -f docker-compose.prod.yml up -d
```

### 프론트엔드 배포

#### Android

```bash
cd frontend/MobileApp
cd android
./gradlew assembleRelease
# APK: android/app/build/outputs/apk/release/app-release.apk
```

#### iOS

Xcode를 사용하여 Archive 및 배포:

1. Xcode에서 프로젝트 열기
2. Product > Archive
3. App Store Connect에 업로드

## 🔐 보안

### Pre-commit Hook (자동 검사)

민감 정보 커밋 방지:

```bash
# Git hooks 설정
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit  # Linux/macOS
```

커밋 시 자동으로 다음을 검사:
- ✅ .env 파일
- ✅ API 키, 비밀번호
- ✅ Private Key
- ✅ 하드코딩된 토큰

### 수동 검사

```bash
# 전체 프로젝트 검사
bash scripts/check-secrets.sh
```

### 보안 체크리스트

- [ ] `.env` 파일이 `.gitignore`에 있는가?
- [ ] 비밀번호는 해싱되어 저장되는가?
- [ ] API 키는 환경 변수로 관리되는가?
- [ ] SECRET_KEY가 강력한가? (`openssl rand -hex 32`)
- [ ] CORS 설정이 프로덕션에 적합한가?
- [ ] HTTPS 사용 (프로덕션)

**자세한 내용**: [docs/SECURITY.md](docs/SECURITY.md)

## 문제 해결

### 백엔드

**문제: MySQL 연결 실패**
- Docker Compose를 사용하는 경우, MySQL 컨테이너가 실행 중인지 확인
- 환경 변수가 올바르게 설정되었는지 확인

**문제: 모듈을 찾을 수 없음**
- 가상환경이 활성화되었는지 확인
- `pip install -r requirements.txt` 재실행

### 프론트엔드

**문제: API 연결 실패**
- 백엔드가 실행 중인지 확인
- API_BASE_URL이 올바르게 설정되었는지 확인
- Android 에뮬레이터: `http://10.0.2.2:8000`
- iOS 시뮬레이터: `http://localhost:8000`
- 실제 기기: 컴퓨터의 IP 주소 사용

**문제: Metro 서버 오류**
```bash
# 캐시 삭제
npm start -- --reset-cache
```

## 라이선스

MIT License

## 기여

이슈와 풀 리퀘스트를 환영합니다!

## 지원

문제가 발생하면 GitHub Issues에 등록해주세요.
