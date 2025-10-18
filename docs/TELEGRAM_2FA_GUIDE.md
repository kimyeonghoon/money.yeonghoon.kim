# 🚀 프론트엔드 2FA 로그인 구현 준비 완료

> 백엔드 API가 모두 준비되었습니다. 이 문서를 보고 바로 시작하세요!

## 📋 오늘 완료된 작업 (백엔드)

### ✅ API 엔드포인트
1. **POST /api/v1/auth/request-login** - 로그인 요청 (인증 코드 발송)
2. **POST /api/v1/auth/verify-login** - 인증 코드 검증 (토큰 발급)

### ✅ 테스트
- 전체 41개 테스트 통과
- 커버리지 80%
- 2FA 테스트 7개 모두 통과

---

## 🔑 환경 변수 설정 (필수!)

### 파일: `backend/.env`

```bash
# Telegram 2FA 설정
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=<실제_봇_토큰>      # ⚠️ 반드시 입력 필요!
TELEGRAM_CHAT_ID=<실제_채팅ID>          # ⚠️ 반드시 입력 필요!
```

**Telegram 설정 방법:**
1. **Bot Token**: @BotFather → `/newbot` → 토큰 복사
2. **Chat ID**: @userinfobot → `/start` → ID 복사

**현재 상태 확인:**
```bash
docker compose -f docker-compose.dev.yml exec backend \
  python3 -c "from app.config import settings; print(f'TELEGRAM_ENABLED: {settings.TELEGRAM_ENABLED}'); print(f'BOT_TOKEN: {settings.TELEGRAM_BOT_TOKEN[:20]}...'); print(f'CHAT_ID: {settings.TELEGRAM_CHAT_ID}')"
```

---

## 📡 API 사용법

### 1️⃣ 로그인 요청 (인증 코드 발송)

**Endpoint:** `POST /api/v1/auth/request-login`

**Request:**
```json
{
  "username": "testuser",
  "password": "testpassword123"
}
```

**Response (200 OK):**
```json
{
  "message": "Verification code sent successfully"
}
```

**Error (401 Unauthorized):**
```json
{
  "detail": "Incorrect username or password"
}
```

**동작:**
- 사용자 인증 (이메일/비밀번호)
- 6자리 코드 생성 (DB 저장)
- Telegram으로 코드 전송
- 5분간 유효

---

### 2️⃣ 인증 코드 검증 (토큰 발급)

**Endpoint:** `POST /api/v1/auth/verify-login`

**Request:**
```json
{
  "username": "testuser",
  "code": "123456"
}
```

**Response (200 OK):**
```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "bearer"
}
```

**Error (400 Bad Request):**
```json
{
  "detail": "Invalid verification code"
}
```
또는
```json
{
  "detail": "Verification code has expired"
}
```

**동작:**
- 코드 검증 (DB 조회)
- 만료 확인 (5분)
- 일회용 확인 (is_used)
- JWT 토큰 발급
- 코드 사용 처리

---

## 🧪 수동 테스트 (curl)

### Step 1: 회원가입
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "username": "testuser",
    "password": "testpassword123",
    "full_name": "Test User"
  }'
```

### Step 2: 로그인 요청 (코드 발송)
```bash
curl -X POST http://localhost:8000/api/v1/auth/request-login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "testpassword123"
  }'
```

**→ Telegram에서 코드 확인!**

### Step 3: 인증 코드 검증 (토큰 발급)
```bash
curl -X POST http://localhost:8000/api/v1/auth/verify-login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "code": "123456"
  }'
```

**→ 토큰 발급 성공!**

---

## 📱 프론트엔드 구현 체크리스트

### 1. API 클라이언트 구현
- [ ] `requestLogin(username, password)` 함수
- [ ] `verifyLogin(username, code)` 함수
- [ ] API Base URL 설정 (Android: `http://10.0.2.2:8000`)

### 2. 화면 구현
- [ ] **로그인 화면**: 이메일/비밀번호 입력 폼
- [ ] **인증 코드 화면**: 6자리 코드 입력 폼
- [ ] 에러 처리 (잘못된 비밀번호, 만료된 코드 등)
- [ ] 로딩 상태 표시

### 3. 상태 관리
- [ ] 로그인 플로우 상태 (login → verify → success)
- [ ] 사용자 정보 저장 (Context 또는 AsyncStorage)
- [ ] 토큰 저장 (access_token, refresh_token)

### 4. UX 개선
- [ ] 코드 5분 타이머 표시
- [ ] 재발송 버튼 (타이머 종료 후)
- [ ] 자동 포커스 (코드 입력 필드)

---

## 🗂️ 프론트엔드 파일 구조 (권장)

```
frontend/MobileApp/src/
├── screens/
│   ├── LoginScreen.tsx              # 이메일/비밀번호 입력
│   └── VerifyCodeScreen.tsx         # 인증 코드 입력
├── services/
│   ├── api.ts                       # Axios 설정
│   └── authService.ts               # requestLogin, verifyLogin
├── contexts/
│   └── AuthContext.tsx              # 인증 상태 관리
└── types/
    └── auth.ts                      # TypeScript 타입 정의
```

---

## 🔧 필요한 패키지 (React Native)

```bash
npm install axios @react-navigation/native @react-navigation/stack
npm install @react-native-async-storage/async-storage
```

---

## 📝 TypeScript 타입 정의 (미리 준비)

```typescript
// src/types/auth.ts

export interface LoginRequest {
  username: string;
  password: string;
}

export interface VerifyLoginRequest {
  username: string;
  code: string;
}

export interface TokenResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
}

export interface LoginRequestResponse {
  message: string;
}
```

---

## 🐛 문제 해결

### Telegram 코드가 안 옴
```bash
# 1. 환경 변수 확인
cat backend/.env | grep TELEGRAM

# 2. 백엔드 재시작
docker compose -f docker-compose.dev.yml restart backend

# 3. 로그 확인
docker compose -f docker-compose.dev.yml logs backend -f
```

### 코드 만료 (5분 경과)
- `POST /api/v1/auth/request-login` 다시 호출
- 새 코드 발급받기

### API 연결 실패
- Android 에뮬레이터: `http://10.0.2.2:8000`
- iOS 시뮬레이터: `http://localhost:8000`
- 실제 기기: `http://<컴퓨터_IP>:8000`

---

## 🎯 내일 시작할 때

1. **이 문서 확인** ✅
2. **Telegram 환경 변수 설정** (TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID)
3. **백엔드 실행 확인** (`docker compose ps`)
4. **curl로 수동 테스트** (위 예제 참고)
5. **프론트엔드 구현 시작**

---

## 📚 참고 문서
- API 문서: http://localhost:8000/docs
- 백엔드 테스트: `backend/tests/api/v1/test_auth.py`
- Telegram 서비스: `backend/app/services/notifications/telegram.py`

**준비 완료! 내일 바로 시작하세요! 🚀**
