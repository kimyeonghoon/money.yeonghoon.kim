# 개발 가이드

이 문서는 Docker 컨테이너 내에서 직접 개발하는 방법을 설명합니다.

## 왜 Docker로 개발하나요?

- ✅ **환경 일치**: 로컬 개발 환경 = 프로덕션 환경
- ✅ **간편한 설정**: Python, Node.js 등 로컬 설치 불필요
- ✅ **팀 협업**: 모든 개발자가 동일한 환경 사용
- ✅ **의존성 격리**: 프로젝트별로 독립된 환경

## 개발 환경 시작하기

### 백엔드 개발

#### 방법 1: VS Code Dev Container (권장)

가장 편리한 방법입니다. VS Code가 컨테이너 안에서 실행되어 모든 개발 도구를 사용할 수 있습니다.

#### 사전 요구사항
- **VS Code** 설치
- **Docker Desktop** 설치 및 실행 중
- **Dev Containers** 확장 설치 (`ms-vscode-remote.remote-containers`)

#### 시작 방법

1. **VS Code로 프로젝트 폴더 열기**
   ```bash
   code fullstack_template
   ```

2. **Dev Container에서 다시 열기**
   - `F1` 또는 `Ctrl+Shift+P` (macOS: `Cmd+Shift+P`)
   - "Dev Containers: Reopen in Container" 선택
   - 또는 우측 하단의 "Reopen in Container" 버튼 클릭

3. **완료!**
   - 컨테이너가 빌드되고 VS Code가 컨테이너 안에서 실행됩니다
   - Python 인터프리터, 린터, 포매터 등이 자동으로 설정됩니다
   - 터미널도 컨테이너 안에서 실행됩니다

#### Dev Container 특징

- **자동 설정**: Python 확장, 린터, 포매터 자동 설치
- **디버깅**: VS Code 디버거로 컨테이너 안 코드 디버깅
- **Git 통합**: Git 명령어 그대로 사용 가능
- **포트 포워딩**: 컨테이너 포트가 자동으로 로컬에 포워딩
- **IntelliSense**: 컨테이너 안의 패키지로 자동 완성

#### 방법 2: Docker Compose로 개발

VS Code 없이 Docker Compose만으로 개발할 수 있습니다.

**개발 서버 시작**

```bash
# 백엔드만 시작
docker-compose -f docker-compose.dev.yml up backend

# 백그라운드 실행
docker-compose -f docker-compose.dev.yml up -d backend

# 로그 확인
docker-compose -f docker-compose.dev.yml logs -f backend
```

---

### 프론트엔드 개발 (React Native)

#### Metro 서버 (Docker)

**프로젝트 초기화**
```bash
cd frontend
bash docker-setup.sh
# 프로젝트 이름 입력
```

**Metro 서버 시작**
```bash
# 루트 디렉토리에서
docker-compose -f docker-compose.dev.yml up -d frontend

# 로그 확인
docker-compose -f docker-compose.dev.yml logs -f frontend

# Metro 서버: http://localhost:8081
```

#### 앱 실행

**방법 1: 완전 Docker (WiFi 디버깅) - 권장**

```bash
# 1. Android 기기를 WiFi 디버깅 모드로 설정
# 2. Docker에서 adb 연결
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555

# 3. 앱 빌드 및 설치
docker-compose -f docker-compose.dev.yml exec frontend npm run android

# Node.js 로컬 설치 불필요!
```

**방법 2: 로컬 Node.js 사용**

```bash
cd frontend/MobileApp

# Android
npm run android

# iOS (macOS만)
npm run ios
```

> **💡 참고**:
> - **Metro 서버**는 Docker에서 실행됩니다
> - **WiFi 디버깅** 사용 시 Node.js 로컬 설치가 전혀 필요 없습니다
> - 자세한 가이드: [WIFI_DEBUGGING.md](WIFI_DEBUGGING.md)

#### 컨테이너 안에서 명령 실행

```bash
# Python 셸 접속
docker-compose -f docker-compose.dev.yml exec backend python

# Bash 셸 접속
docker-compose -f docker-compose.dev.yml exec backend bash

# 테스트 실행
docker-compose -f docker-compose.dev.yml exec backend pytest

# 의존성 설치
docker-compose -f docker-compose.dev.yml exec backend pip install package-name
```

#### 데이터베이스 접속

```bash
# MySQL 셸 접속
docker-compose -f docker-compose.dev.yml exec mysql mysql -u appuser -papppassword appdb
```

## 코드 변경 시 자동 리로드

### 백엔드 (FastAPI)

코드를 수정하면 **자동으로 서버가 재시작**됩니다.

- `backend/app/` 디렉토리의 모든 `.py` 파일이 실시간 동기화
- Uvicorn의 `--reload` 옵션으로 자동 리로드
- 변경사항을 저장하면 즉시 반영

```bash
# 로그에서 리로드 확인 가능
INFO:     Uvicorn running on http://0.0.0.0:8000
INFO:     Will watch for changes in these directories: ['/app']
```

## 디버깅

### VS Code에서 디버깅 (Dev Container)

1. **중단점 설정**
   - 코드에서 왼쪽 여백 클릭하여 중단점 추가

2. **디버거 시작**
   - `F5` 또는 "Run and Debug" 패널에서 시작
   - 설정: "Python: FastAPI (Docker)"

3. **디버깅**
   - 변수 확인, 스택 추적, 표현식 평가 등 모든 기능 사용 가능

### 원격 디버깅 (일반 Docker Compose)

디버거 연결을 위한 코드를 추가합니다:

```python
# app/main.py
import debugpy

# 디버거 대기 (개발 환경에서만)
if __DEV__:
    debugpy.listen(("0.0.0.0", 5678))
    print("Debugger is waiting on port 5678...")
```

VS Code에서 "Python: Remote Attach (Docker)" 설정으로 연결합니다.

## 데이터베이스 작업

### 마이그레이션

```bash
# Alembic 초기화 (처음 한 번만)
docker-compose -f docker-compose.dev.yml exec backend alembic init alembic

# 마이그레이션 생성
docker-compose -f docker-compose.dev.yml exec backend alembic revision --autogenerate -m "description"

# 마이그레이션 적용
docker-compose -f docker-compose.dev.yml exec backend alembic upgrade head

# 롤백
docker-compose -f docker-compose.dev.yml exec backend alembic downgrade -1
```

### 데이터베이스 초기화

```bash
# 모든 테이블 삭제 및 재생성
docker-compose -f docker-compose.dev.yml down -v
docker-compose -f docker-compose.dev.yml up -d
```

## 테스트

### 테스트 실행

```bash
# 모든 테스트 실행
docker-compose -f docker-compose.dev.yml exec backend pytest

# 특정 파일 테스트
docker-compose -f docker-compose.dev.yml exec backend pytest tests/test_auth.py

# 커버리지 포함
docker-compose -f docker-compose.dev.yml exec backend pytest --cov=app --cov-report=html

# VS Code에서 테스트 (Dev Container)
# Testing 패널에서 클릭으로 실행 가능
```

## 의존성 관리

### 새 패키지 설치

```bash
# 패키지 설치
docker-compose -f docker-compose.dev.yml exec backend pip install package-name

# requirements.txt에 추가
echo "package-name==version" >> backend/requirements.txt

# 컨테이너 재빌드
docker-compose -f docker-compose.dev.yml up -d --build backend
```

### requirements.txt 업데이트 후

```bash
# 패키지 재설치
docker-compose -f docker-compose.dev.yml exec backend pip install -r requirements.txt

# 또는 컨테이너 재빌드
docker-compose -f docker-compose.dev.yml up -d --build backend
```

## 코드 품질

### 포맷팅

```bash
# Black으로 포맷팅
docker-compose -f docker-compose.dev.yml exec backend black app/

# 변경사항 확인만
docker-compose -f docker-compose.dev.yml exec backend black app/ --check
```

### 린팅

```bash
# Flake8 린팅
docker-compose -f docker-compose.dev.yml exec backend flake8 app/

# Mypy 타입 체크
docker-compose -f docker-compose.dev.yml exec backend mypy app/
```

**VS Code Dev Container에서는** 저장 시 자동으로 포맷팅됩니다!

## 유용한 명령어

```bash
# 컨테이너 상태 확인
docker-compose -f docker-compose.dev.yml ps

# 컨테이너 로그 확인
docker-compose -f docker-compose.dev.yml logs -f

# 컨테이너 중지
docker-compose -f docker-compose.dev.yml stop

# 컨테이너 삭제
docker-compose -f docker-compose.dev.yml down

# 볼륨까지 삭제 (데이터베이스 초기화)
docker-compose -f docker-compose.dev.yml down -v

# 컨테이너 재빌드
docker-compose -f docker-compose.dev.yml up -d --build

# 특정 서비스만 재시작
docker-compose -f docker-compose.dev.yml restart backend
```

## 프로덕션 vs 개발 환경

| 항목 | 개발 (`docker-compose.dev.yml`) | 프로덕션 (`docker-compose.yml`) |
|------|--------------------------------|--------------------------------|
| Dockerfile | `Dockerfile.dev` | `Dockerfile` |
| 코드 동기화 | ✅ 볼륨 마운트 | ❌ 이미지에 포함 |
| 자동 리로드 | ✅ Uvicorn --reload | ❌ |
| 디버거 | ✅ 포트 5678 | ❌ |
| 개발 도구 | ✅ pytest, black, flake8 등 | ❌ |
| 최적화 | ❌ | ✅ |

## 문제 해결

### 코드 변경이 반영되지 않아요

```bash
# 컨테이너 재시작
docker-compose -f docker-compose.dev.yml restart backend

# 볼륨 마운트 확인
docker-compose -f docker-compose.dev.yml exec backend ls -la /app/app
```

### 패키지를 찾을 수 없어요

```bash
# 패키지 재설치
docker-compose -f docker-compose.dev.yml exec backend pip install -r requirements.txt

# 또는 컨테이너 재빌드
docker-compose -f docker-compose.dev.yml up -d --build backend
```

### 데이터베이스 연결 실패

```bash
# MySQL 컨테이너 상태 확인
docker-compose -f docker-compose.dev.yml ps mysql

# MySQL 로그 확인
docker-compose -f docker-compose.dev.yml logs mysql

# 헬스체크 확인
docker inspect fullstack_mysql_dev | grep Health
```

### VS Code에서 Python 인터프리터를 찾을 수 없어요

1. Dev Container에서 열었는지 확인 (좌측 하단에 "Dev Container: Fullstack Development" 표시)
2. Python 확장이 컨테이너 안에 설치되었는지 확인
3. `Ctrl+Shift+P` → "Python: Select Interpreter" → `/usr/local/bin/python` 선택

## 다음 단계

- [ ] 첫 API 엔드포인트 작성
- [ ] 테스트 코드 작성
- [ ] 데이터베이스 모델 추가
- [ ] 프론트엔드와 연동

## 참고 자료

- [VS Code Dev Containers](https://code.visualstudio.com/docs/devcontainers/containers)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [FastAPI Documentation](https://fastapi.tiangolo.com/)
