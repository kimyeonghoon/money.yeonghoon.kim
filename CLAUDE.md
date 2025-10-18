# Claude 협업 가이드

> 🤖 이 문서는 SessionStart 훅으로 자동 로드됨 (`.claude/settings.json`)

## 핵심 원칙 (절대 준수)

### 1. TDD (Red → Green → Refactor)
- 모든 기능은 테스트부터 작성
- 커버리지 80% 이상 필수
- Given-When-Then 패턴 사용
- 예외 없음

### 2. Docker 필수
- 백엔드/프론트엔드 모두 Docker에서 개발
- Python/Node.js 로컬 설치 금지
- WiFi 디버깅 사용 ([docs/WIFI_DEBUGGING.md](docs/WIFI_DEBUGGING.md))

**작업 환경 규칙:**
- ✅ **Docker 사용 (원칙)**: Metro 서버, npm install, React Native Web, 모든 Node.js 작업, 개발 서버 실행
- ⚠️ **호스트 사용 (예외)**: Android APK 빌드만 (`./gradlew assembleDebug`) - Docker에서 OOM 문제로 불가피

### 3. 타입 안전성
```python
# Python: 타입 힌트 필수
def get_user(user_id: int) -> Optional[User]: ...

# TypeScript: any 금지
const error = err as AxiosError;  # ✅
```

### 4. 보안 (Security-First)

**필수 규칙:**
1. **입력 검증**: Pydantic 스키마로 자동 검증, 추가 비즈니스 로직은 함수에서 명시적 검증
2. **SQL Injection 방지**: ORM 필수, Raw SQL 금지, 파라미터 바인딩만 사용
3. **인증/인가**: 모든 보호 API는 `Depends(get_current_active_user)` 사용
4. **비밀번호**: bcrypt 해싱, 평문 저장 절대 금지, 최소 8자
5. **토큰 관리**: Access(30분), Refresh(7일), HTTPS 전송만
6. **환경 변수**: SECRET_KEY, DB 비밀번호 등 .env에만, Git 제외
7. **에러 메시지**: 민감 정보 노출 금지 (예: "Invalid credentials" O, "User not found" X)
8. **CORS**: 프로덕션에서는 특정 도메인만 (["*"] 금지)
9. **로깅**: 비밀번호, 토큰, 개인정보 로그 금지
10. **의존성**: 정기 `pip list --outdated` 체크, 취약점 스캔

**금지 사항:**
- ❌ Raw SQL 쿼리 (`db.execute(f"SELECT...")`)
- ❌ 평문 비밀번호 저장
- ❌ GET 요청으로 상태 변경 (POST/PUT/DELETE 사용)
- ❌ 토큰/비밀번호 URL 파라미터 전송
- ❌ 프로덕션에서 `echo=True` (SQL 로깅)
- ❌ 에러 메시지에 스택 트레이스 노출 (개발환경 제외)

## 개발 워크플로우

**새 기능**: 테스트 작성 → 실패 확인 → 최소 구현 → 통과 → 리팩토링 → 추가 테스트 → 문서 → 커밋
**버그 수정**: 재현 테스트 → 실패 확인 → 수정 → 통과 → 커밋
**리팩토링**: 테스트 통과 확인 → 리팩토링 → 재확인 → 커밋

## 코딩 컨벤션

### Python
1. **타입 힌트**: 모든 함수 필수
2. **Docstring**: 모든 함수/클래스 상단에 상세 설명 (Args, Returns, Raises, Example)
3. **주석 금지**: 코드 내부 인라인 주석 작성 금지 (docstring에만 설명)
4. **Pydantic v2**: `model_config = ConfigDict()`
5. **ORM + RAW SQL 주석**: 모든 쿼리 상단에 동등 SQL 주석 추가 (예외적으로 허용)
   ```python
   # RAW SQL: SELECT * FROM users WHERE email = ? LIMIT 1
   user = db.query(User).filter(User.email == email).first()
   ```
6. **보안 필수**:
   - 비밀번호: `get_password_hash()` 사용, 평문 저장 금지
   - 인증: `Depends(get_current_active_user)` 모든 보호 API에 적용
   - 검증: Pydantic 스키마 + 추가 비즈니스 로직 검증
   - 에러: HTTPException에 민감 정보 노출 금지
7. **포맷**: Black (120자), Flake8, Mypy

### TypeScript
1. **any 금지**: `unknown` 사용 후 타입 단언
2. **Hooks 최적화**: useCallback, useMemo 적극 사용

### 에러 처리
1. **일관된 형식**: 모든 에러는 HTTPException 사용
2. **상태 코드**: 400(잘못된 요청), 401(인증 실패), 403(권한 없음), 404(없음), 500(서버 오류)
3. **에러 메시지**: 사용자 친화적, 민감 정보 제외, 영문으로
   ```python
   # ✅ 좋은 예
   raise HTTPException(status_code=400, detail="Invalid email format")

   # ❌ 나쁜 예
   raise HTTPException(status_code=400, detail=f"User {email} not found in database users table")
   ```
4. **에러 로깅**: 모든 500 에러는 로그 기록 (스택 트레이스 포함)
5. **try-except**: 예상 가능한 에러만 catch, 예상 못한 에러는 전역 핸들러에서 처리

### API 설계
1. **RESTful**: 리소스 중심 URL (`/users/{id}` O, `/getUserById` X)
2. **HTTP 메서드**:
   - GET: 조회 (상태 변경 X, 멱등성 O)
   - POST: 생성 (멱등성 X)
   - PUT: 전체 업데이트 (멱등성 O)
   - PATCH: 부분 업데이트
   - DELETE: 삭제 (멱등성 O)
3. **URL 규칙**: 소문자, 복수형, 하이픈 구분 (`/api/v1/user-profiles`)
4. **응답 형식**: 일관된 JSON 구조
   ```python
   # 단일 리소스
   {"id": 1, "username": "user"}

   # 리스트 (페이지네이션)
   {"items": [...], "total": 100, "page": 1, "size": 20}
   ```
5. **버전 관리**: URL에 버전 명시 (`/api/v1/`, `/api/v2/`)

### 성능 최적화
1. **N+1 방지**: `joinedload()` 또는 `selectinload()` 사용
   ```python
   # ❌ N+1 문제
   users = db.query(User).all()
   for user in users:
       print(user.posts)  # 매번 쿼리 실행

   # ✅ 해결
   from sqlalchemy.orm import joinedload
   users = db.query(User).options(joinedload(User.posts)).all()
   ```
2. **페이지네이션**: 리스트 API는 필수 (`limit`, `offset` 또는 `page`, `size`)
3. **Select 최적화**: 필요한 컬럼만 조회 (특히 큰 TEXT 필드)
4. **인덱스**: WHERE, JOIN, ORDER BY 컬럼에 인덱스 필수
5. **캐싱**: 자주 조회되고 변경 적은 데이터는 캐싱 고려

### 로깅
1. **구조화**: JSON 형식, 타임스탬프 포함
2. **레벨**:
   - DEBUG: 개발 중 상세 정보
   - INFO: 정상 작동 (API 호출, 비즈니스 이벤트)
   - WARNING: 주의 필요 (deprecated 사용, 느린 쿼리)
   - ERROR: 에러 발생 (500, 예외)
   - CRITICAL: 시스템 중단 수준
3. **필수 정보**: 요청 ID, 사용자 ID, 엔드포인트, 메서드, 상태 코드, 실행 시간
4. **제외**: 비밀번호, 토큰, 개인정보 (이메일, 전화번호, 주민번호)
5. **요청/응답**: 개발 환경에서만, 프로덕션은 요약만

### 데이터베이스
1. **마이그레이션**: Alembic 필수, 직접 테이블 생성 금지
2. **트랜잭션**: 복수 작업은 트랜잭션으로 묶기
   ```python
   try:
       db.add(user)
       db.add(profile)
       db.commit()
   except:
       db.rollback()
       raise
   ```
3. **외래 키**: 관계는 반드시 FK 제약 조건 설정
4. **인덱스 명명**: `idx_{table}_{column}` (예: `idx_users_email`)
5. **NULL 처리**: nullable 명시적 지정, 기본값 설정
6. **Soft Delete**: 삭제는 `deleted_at` 또는 `is_active=False`

### 코드 품질
1. **함수 길이**: 최대 50줄 (docstring 제외)
2. **매직 넘버**: 상수화 필수
   ```python
   # ❌ 나쁜 예
   if len(password) < 8:

   # ✅ 좋은 예
   MIN_PASSWORD_LENGTH = 8
   if len(password) < MIN_PASSWORD_LENGTH:
   ```
3. **조기 반환**: Guard Clause 패턴 사용
   ```python
   # ✅ 좋은 예
   if not user:
       raise HTTPException(404)
   if not user.is_active:
       raise HTTPException(400)
   return user

   # ❌ 나쁜 예 (중첩 if)
   if user:
       if user.is_active:
           return user
   ```
4. **함수 책임**: 하나의 함수는 하나의 일만 (Single Responsibility)
5. **DRY**: 3번 이상 반복되면 함수화
6. **네이밍**: 명확하고 구체적 (`get_active_users` O, `get_data` X)

### Git 커밋
형식: `<type>(<scope>): <subject>`

| Type | 예시 |
|------|------|
| feat | feat(auth): JWT 리프레시 토큰 추가 |
| fix | fix(api): 중복 이메일 검증 수정 |
| test | test(users): 사용자 생성 테스트 추가 |
| refactor | refactor(db): 연결 풀 최적화 |
| docs | docs(api): 엔드포인트 설명 추가 |
| chore | chore(deps): FastAPI 업데이트 |
| perf | perf(query): N+1 쿼리 최적화 |

## Claude와 협업하기

### 효과적인 요청
✅ **좋은 예**: "User 모델에 profile_image 필드 추가. nullable String(500자), S3 URL 저장. TDD로 진행."
❌ **나쁜 예**: "프로필 이미지 기능 추가해줘"

**에러 보고**: 환경 정보 + 에러 메시지 + 로그 + 시도한 것 + 관련 파일
**기능 요청**: 요구사항 + 구체적 스펙 + 관련 파일 경로 + "TDD로"

### Claude 작업 순서
요구사항 확인 → 테스트 작성(Red) → 구현(Green) → 리팩토링(Refactor) → 추가 테스트 → 문서 → 요약

### Claude 금지 사항
테스트 없는 코드 | 로컬 설치 안내 | any 타입 | Raw SQL | 평문 비밀번호 | 민감 정보 로깅 | 인증 없는 보호 API | 민감 정보 에러 메시지 | GET 상태 변경 | 50줄 초과 함수 | 매직 넘버 | N+1 쿼리 | 페이지네이션 없는 리스트 | 직접 테이블 생성 (Alembic 필수) | 트랜잭션 없는 복수 작업

## 빠른 참조

### 커밋 전 체크
```bash
docker-compose -f docker-compose.dev.yml exec backend pytest -v  # 테스트
docker-compose -f docker-compose.dev.yml exec backend pytest --cov=app  # 커버리지 80%+
docker-compose -f docker-compose.dev.yml exec backend black app/  # 포맷
docker-compose -f docker-compose.dev.yml exec backend flake8 app/  # 린트
docker-compose -f docker-compose.dev.yml exec backend mypy app/  # 타입 체크
bash scripts/check-secrets.sh  # 보안 검사
```

**필수 체크리스트:**
- [ ] 테스트 작성 및 통과 (커버리지 80%+)
- [ ] 타입 힌트 모든 함수에 적용
- [ ] Docstring 작성 (Args, Returns, Raises)
- [ ] 함수 길이 50줄 이하
- [ ] 매직 넘버 상수화
- [ ] N+1 쿼리 없음 (joinedload 사용)
- [ ] 리스트 API 페이지네이션 적용
- [ ] RESTful URL 설계 (복수형, 하이픈)
- [ ] HTTP 메서드 적절히 사용
- [ ] 에러 메시지 민감 정보 없음
- [ ] 보호 API 인증 적용
- [ ] ORM 사용, Raw SQL 없음
- [ ] 비밀번호 bcrypt 해싱
- [ ] 환경 변수로 비밀 정보 관리
- [ ] 로그에 민감 정보 없음

### 문제 해결
**테스트**: `pytest -v -s` (상세) | `pytest --pdb` (디버그)
**Docker**: `logs -f backend` (로그) | `down -v && up -d` (초기화)
**WiFi 디버깅**: `bash scripts/adb-troubleshoot.sh`
**N+1 디버깅**: `echo=True` 설정 후 쿼리 개수 확인

## 추가 문서
[시작](docs/GETTING_STARTED.md) | [TDD](docs/TDD.md) | [Docker](docs/WHY_DOCKER.md) | [WiFi](docs/WIFI_DEBUGGING.md) | [개발환경](docs/DEVELOPMENT.md) | [전체](docs/README.md)
