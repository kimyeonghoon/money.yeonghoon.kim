# 시스템 아키텍처

이 문서는 프로젝트의 전체 아키텍처를 설명합니다.

## 📊 전체 시스템 구조

```mermaid
graph TB
    subgraph "클라이언트"
        A[React Native App<br/>iOS/Android]
        B[웹 브라우저<br/>React Native Web]
    end

    subgraph "Docker 환경"
        subgraph "프론트엔드"
            C[Metro Server<br/>Port: 8081]
            C2[Webpack Dev Server<br/>Port: 3000]
        end

        subgraph "백엔드"
            D[FastAPI<br/>Port: 8000]
            E[Uvicorn ASGI Server]
        end

        subgraph "데이터베이스"
            F[MySQL 8.0+<br/>Port: 3306]
        end
    end

    A -->|WiFi Debugging| C
    A -->|HTTP/HTTPS| D
    B -->|HTTP| C2
    B -->|HTTP/HTTPS| D
    C -->|Hot Reload| A
    C2 -->|Hot Reload| B
    D --> E
    E -->|SQLAlchemy ORM| F

    style A fill:#61DAFB
    style B fill:#61DAFB
    style C fill:#FF6B6B
    style D fill:#009688
    style E fill:#4CAF50
    style F fill:#00758F
```

## 🔄 요청 처리 플로우

```mermaid
sequenceDiagram
    participant Client as 클라이언트
    participant FastAPI as FastAPI Router
    participant Deps as Dependencies
    participant API as API Handler
    participant ORM as SQLAlchemy ORM
    participant DB as MySQL Database

    Client->>FastAPI: HTTP Request
    FastAPI->>Deps: Inject Dependencies<br/>(DB Session, Auth)

    alt 인증 필요
        Deps->>Deps: JWT 토큰 검증
        Deps->>DB: 사용자 조회
        DB-->>Deps: User Object
    end

    Deps->>API: Call Handler<br/>(db, current_user)
    API->>API: Pydantic 스키마 검증
    API->>ORM: Query Building
    ORM->>DB: SQL Execution
    DB-->>ORM: Result Set
    ORM-->>API: Python Objects
    API->>API: Business Logic
    API->>API: Response 스키마 변환
    API-->>FastAPI: Pydantic Model
    FastAPI-->>Client: JSON Response

    Note over Client,DB: 모든 단계에서 타입 안전성 보장
```

## 🔐 인증 플로우

```mermaid
sequenceDiagram
    participant App as 앱/웹
    participant API as FastAPI
    participant Auth as Auth Handler
    participant Security as Security Utils
    participant DB as Database

    rect rgb(200, 220, 240)
        Note over App,DB: 회원가입
        App->>API: POST /api/v1/auth/register
        API->>Auth: UserCreate Schema
        Auth->>Security: get_password_hash()
        Security-->>Auth: Hashed Password
        Auth->>DB: INSERT User
        DB-->>Auth: Created User
        Auth-->>App: 201 Created
    end

    rect rgb(220, 240, 200)
        Note over App,DB: 로그인
        App->>API: POST /api/v1/auth/login
        API->>Auth: LoginRequest
        Auth->>DB: SELECT User
        DB-->>Auth: User + hashed_password
        Auth->>Security: verify_password()
        Security-->>Auth: True/False

        alt 비밀번호 일치
            Auth->>Security: create_access_token()
            Security-->>Auth: JWT Access Token
            Auth->>Security: create_refresh_token()
            Security-->>Auth: JWT Refresh Token
            Auth-->>App: 200 OK + Tokens
        else 불일치
            Auth-->>App: 401 Unauthorized
        end
    end

    rect rgb(240, 220, 200)
        Note over App,DB: 보호된 API 호출
        App->>API: GET /api/v1/users/me<br/>Authorization: Bearer {token}
        API->>Auth: get_current_user()
        Auth->>Security: jwt.decode()
        Security-->>Auth: Payload {sub: user_id}
        Auth->>DB: SELECT User WHERE id = ?
        DB-->>Auth: User Object
        Auth->>Auth: is_active 체크
        Auth-->>API: current_user
        API-->>App: 200 OK + User Data
    end
```

## 🐳 Docker 컨테이너 구조

```mermaid
graph TB
    subgraph "docker-compose.dev.yml"
        subgraph "backend 컨테이너"
            B1[Python 3.11]
            B2[FastAPI + Uvicorn]
            B3[Hot Reload]
            B4[Volume: ./backend:/app]
        end

        subgraph "frontend 컨테이너"
            F1[Node.js 18+]
            F2[React Native 0.78.3]
            F3[Metro Bundler]
            F4[Webpack Dev Server<br/>Port: 3000]
            F5[Volume: ./frontend:/app]
        end

        subgraph "mysql 컨테이너"
            M1[MySQL 8.0]
            M2[Volume: mysql_data]
            M3[my.cnf 설정]
        end
    end

    B2 -->|SQLAlchemy| M1
    F3 -->|WiFi Debugging| Device[실제 기기/에뮬레이터]
    Device -->|HTTP API| B2

    style B1 fill:#4B8BBE
    style F1 fill:#339933
    style M1 fill:#00758F
```

## 📁 프로젝트 구조

```
fullstack_template/
├── backend/                    # FastAPI 백엔드
│   ├── app/
│   │   ├── api/               # API 라우터
│   │   │   ├── deps.py        # 의존성 (인증 등)
│   │   │   └── v1/            # API v1
│   │   │       ├── auth.py    # 인증 API
│   │   │       └── users.py   # 사용자 API
│   │   ├── core/              # 핵심 기능
│   │   │   └── security.py    # JWT, 비밀번호 해싱
│   │   ├── models/            # SQLAlchemy 모델
│   │   │   └── user.py
│   │   ├── schemas/           # Pydantic 스키마
│   │   │   └── user.py
│   │   ├── config.py          # 환경 설정
│   │   ├── database.py        # DB 연결
│   │   └── main.py            # FastAPI 앱
│   ├── tests/                 # pytest 테스트
│   │   └── conftest.py        # 테스트 픽스처
│   ├── mysql/                 # MySQL 설정
│   │   └── my.cnf
│   └── requirements.txt
│
├── frontend/                   # React Native 프론트엔드
│   ├── MobileApp/             # React Native 프로젝트
│   │   ├── android/           # Android 네이티브
│   │   ├── ios/               # iOS 네이티브
│   │   ├── public/            # 웹 정적 파일
│   │   ├── __tests__/         # Jest 테스트
│   │   ├── App.tsx            # 메인 앱
│   │   ├── index.js           # RN 엔트리
│   │   ├── index.web.js       # Web 엔트리
│   │   ├── webpack.config.js  # 웹 번들러
│   │   └── package.json
│   ├── templates/             # 인증 템플릿 (참고용)
│   │   ├── api.ts
│   │   ├── authService.ts
│   │   ├── AuthContext.tsx
│   │   └── LoginScreen.tsx
│   └── Dockerfile.dev
│
├── docs/                       # 문서
│   ├── ARCHITECTURE.md        # 이 파일
│   ├── DATABASE_SCHEMA.md     # DB 스키마
│   ├── TDD.md                 # TDD 가이드
│   └── ...
│
├── scripts/                    # 유틸리티 스크립트
│   ├── backup-mysql.sh
│   └── check-secrets.sh
│
├── .claude/                    # Claude Code 설정
│   ├── settings.json
│   └── hooks/
│
├── docker-compose.yml          # 프로덕션
├── docker-compose.dev.yml      # 개발
├── CLAUDE.md                   # 협업 규칙
└── README.md
```

## 🔀 데이터 흐름

### 1. 회원가입 플로우

```mermaid
graph LR
    A[사용자 입력] --> B[Pydantic 검증]
    B --> C{검증 성공?}
    C -->|실패| D[400 에러]
    C -->|성공| E[중복 체크]
    E --> F{중복 있음?}
    F -->|있음| G[400 에러]
    F -->|없음| H[비밀번호 해싱]
    H --> I[DB 저장]
    I --> J[201 Created]

    style A fill:#E3F2FD
    style B fill:#FFF3E0
    style H fill:#F3E5F5
    style I fill:#E8F5E9
```

### 2. 보호된 API 접근

```mermaid
graph LR
    A[HTTP 요청] --> B[Authorization 헤더]
    B --> C{토큰 있음?}
    C -->|없음| D[401 에러]
    C -->|있음| E[JWT 검증]
    E --> F{유효한가?}
    F -->|무효| G[401 에러]
    F -->|유효| H[사용자 조회]
    H --> I{활성 사용자?}
    I -->|비활성| J[400 에러]
    I -->|활성| K[API 핸들러 실행]
    K --> L[200 OK]

    style A fill:#E3F2FD
    style E fill:#FFF3E0
    style H fill:#F3E5F5
    style K fill:#E8F5E9
```

## 🎯 레이어 아키텍처

```mermaid
graph TB
    subgraph "Presentation Layer"
        A[FastAPI Router]
        B[Pydantic Request/Response]
    end

    subgraph "Business Logic Layer"
        C[API Handlers]
        D[Dependencies]
        E[Security Utils]
    end

    subgraph "Data Access Layer"
        F[SQLAlchemy ORM]
        G[Database Session]
    end

    subgraph "Infrastructure Layer"
        H[MySQL Database]
        I[환경 설정]
    end

    A --> B
    B --> C
    C --> D
    C --> E
    C --> F
    D --> F
    F --> G
    G --> H
    C --> I

    style A fill:#2196F3
    style C fill:#4CAF50
    style F fill:#FF9800
    style H fill:#F44336
```

## 🔧 기술 스택

### 백엔드
- **Framework**: FastAPI 0.115.6 (Python 3.11+)
- **ORM**: SQLAlchemy 2.0.36
- **DB Driver**: PyMySQL
- **Validation**: Pydantic v2
- **Auth**: python-jose (JWT)
- **Password**: passlib + bcrypt
- **Testing**: pytest + coverage
- **Server**: Uvicorn (ASGI)

### 프론트엔드
- **Framework**: React Native 0.75+
- **Language**: TypeScript 5.3+
- **HTTP Client**: Axios
- **Navigation**: React Navigation
- **State**: (선택 가능)

### 인프라
- **Container**: Docker + Docker Compose
- **Database**: MySQL 8.0+ (utf8mb4, Asia/Seoul)
- **Development**: VS Code Dev Containers
- **VCS**: Git + Conventional Commits

## 📝 주요 설계 원칙

### 1. Docker-First Development
- 모든 개발 환경은 Docker 컨테이너
- "내 컴퓨터에선 되는데" 문제 제거
- WiFi 디버깅으로 완전한 Docker 개발

### 2. Test-Driven Development (TDD)
- 테스트 먼저 → 구현 → 리팩토링
- Given-When-Then 패턴
- 커버리지 80% 이상 필수

### 3. Type Safety
- Python: 타입 힌트 필수
- TypeScript: any 금지
- Pydantic으로 런타임 검증

### 4. Security-First
- JWT 인증 (Access 30분, Refresh 7일)
- bcrypt 비밀번호 해싱
- ORM으로 SQL Injection 방지
- 환경 변수로 민감 정보 관리

### 5. Clean Code
- 함수 최대 50줄
- Single Responsibility
- Guard Clause 패턴
- 매직 넘버 상수화

## 🚀 확장 포인트

### 추가 가능한 기능들

1. **캐싱 레이어**
   ```
   FastAPI → Redis → MySQL
   ```

2. **비동기 작업**
   ```
   Celery + Redis/RabbitMQ
   ```

3. **파일 스토리지**
   ```
   AWS S3 / MinIO
   ```

4. **실시간 통신**
   ```
   WebSocket (FastAPI)
   ```

5. **검색 엔진**
   ```
   Elasticsearch
   ```

6. **모니터링**
   ```
   Sentry (에러 추적)
   Prometheus + Grafana (메트릭)
   ```

## 📚 관련 문서

- [데이터베이스 스키마](DATABASE_SCHEMA.md)
- [TDD 가이드](TDD.md)
- [보안 가이드](SECURITY.md)
- [개발 환경 설정](DEVELOPMENT.md)
- [협업 규칙](../CLAUDE.md)

---

**마지막 업데이트**: 2025-01-17
