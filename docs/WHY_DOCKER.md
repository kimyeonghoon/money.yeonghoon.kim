# 왜 Docker로 개발해야 하나요?

## ❓ 자주 묻는 질문

### "로컬에서 Python 설치하면 안 되나요?"

**안 됩니다!** 이 템플릿의 핵심 원칙은 **Docker 필수 개발**입니다.

## 🚫 로컬 개발의 문제점

### 1. "제 컴퓨터에서는 되는데요?" 문제

```bash
# 개발자 A (macOS, Python 3.11)
$ python --version
Python 3.11.5
$ pip install -r requirements.txt  # 성공!
$ python app/main.py  # 잘 작동!

# 개발자 B (Windows, Python 3.10)
$ python --version
Python 3.10.8
$ pip install -r requirements.txt  # 일부 패키지 설치 실패...
$ python app/main.py  # 에러 발생!

# 서버 (Ubuntu, Python 3.11.3)
$ python --version
Python 3.11.3
$ pip install -r requirements.txt  # 성공
$ python app/main.py  # 또 다른 에러...
```

**문제:**
- Python 버전 차이
- OS 차이 (경로, 줄바꿈, 권한 등)
- 시스템 라이브러리 차이
- 환경 변수 설정 차이

### 2. 의존성 지옥

```bash
# 프로젝트 A: FastAPI 0.100.0 필요
# 프로젝트 B: FastAPI 0.95.0 필요

$ pip install fastapi==0.100.0  # 프로젝트 A 작업
$ cd ../project-b
$ pip install fastapi==0.95.0   # 프로젝트 B 작업
# → 프로젝트 A가 망가짐!

# 가상환경으로 해결?
$ python -m venv venv
$ source venv/bin/activate
# → 매번 설정, 팀원마다 다른 환경, 여전히 OS/버전 차이 존재
```

### 3. 신규 팀원 온보딩

**로컬 개발:**
```bash
1. Python 3.11 설치 (버전 확인!)
2. pip 업그레이드
3. MySQL 설치 및 설정
4. 가상환경 생성
5. requirements.txt 설치
6. .env 설정
7. 데이터베이스 생성
8. 마이그레이션 실행
9. 서버 실행

# 소요 시간: 2-3시간
# 에러 발생 확률: 80%
# "제 컴퓨터에서는 안 되는데요?" 발생 확률: 99%
```

**Docker 개발:**
```bash
1. Docker Desktop 설치
2. docker-compose up -d

# 소요 시간: 10분
# 에러 발생 확률: 5%
# 모든 개발자가 똑같은 환경
```

### 4. 프로덕션 배포 시 문제

```python
# 로컬에서 테스트
$ python app/main.py  # 잘 작동!

# 프로덕션 서버에 배포
$ ssh production-server
$ git pull
$ pip install -r requirements.txt
$ python app/main.py
# Error: ImportError: cannot import name 'something'
# 왜? 로컬과 서버의 환경이 다르기 때문!

# Docker 사용 시
$ docker build -t myapp .
$ docker run myapp  # 로컬에서 테스트
# 잘 작동!

$ docker push myapp
$ ssh production-server
$ docker pull myapp
$ docker run myapp  # 프로덕션에서 실행
# 똑같이 작동! (환경이 완전히 동일)
```

## ✅ Docker의 장점

### 1. 환경 일치 보장

```yaml
# docker-compose.dev.yml
services:
  backend:
    image: python:3.11-slim  # 정확한 버전
    environment:
      - MYSQL_HOST=mysql
      - MYSQL_PORT=3306
```

**결과:**
- ✅ 모든 개발자가 Python 3.11 사용
- ✅ 같은 OS (Debian slim)
- ✅ 같은 환경 변수
- ✅ 같은 데이터베이스 버전

### 2. 즉시 시작 가능

```bash
# 새 팀원이 합류
$ git clone repo
$ docker-compose -f docker-compose.dev.yml up -d

# 끝! 5분 안에 개발 시작 가능
```

### 3. 격리된 환경

```bash
# 프로젝트 A
$ cd project-a
$ docker-compose up
# FastAPI 0.100.0, Python 3.11

# 프로젝트 B
$ cd project-b
$ docker-compose up
# FastAPI 0.95.0, Python 3.10

# 서로 영향 없음!
```

### 4. 프로덕션과 동일

```bash
# 개발 환경
docker-compose -f docker-compose.dev.yml up

# 프로덕션 환경
docker-compose -f docker-compose.yml up

# 같은 Dockerfile, 같은 이미지, 같은 환경
# "제 컴퓨터에서는 되는데" 문제 제로!
```

## 🎯 이 템플릿의 접근

### Docker 필수 개발

```
로컬 Python 설치 ❌
    ↓
Docker Desktop만 설치 ✅
    ↓
VS Code Dev Container 또는
docker-compose.dev.yml 사용
    ↓
모든 개발자가 동일한 환경
    ↓
프로덕션과 동일한 환경
    ↓
"제 컴퓨터에서는" 문제 제로!
```

### 개발 경험도 좋음

**VS Code Dev Container 사용 시:**
- ✅ IntelliSense 작동
- ✅ 디버거 작동
- ✅ 터미널이 컨테이너 안
- ✅ 파일 저장 → 자동 리로드
- ✅ Git 그대로 사용

**로컬 개발과 차이 없음, 하지만 훨씬 안정적!**

## 🤔 그래도 궁금한 점

### "Docker가 느리지 않나요?"

**예전:** Docker for Mac/Windows가 느렸음
**지금:** Docker Desktop + WSL2/VirtioFS로 거의 네이티브 속도

**실제 성능:**
```bash
# 파일 저장 → 서버 재시작
로컬: 1-2초
Docker: 1-2초

# 차이 거의 없음!
```

### "디버깅은 어떻게 하나요?"

**VS Code Dev Container:**
- F5 눌러서 디버깅
- 중단점 설정
- 변수 확인
- 로컬 개발과 똑같음!

**Docker Compose:**
- 원격 디버거 연결 (포트 5678)
- 똑같이 디버깅 가능

### "데이터베이스는요?"

```yaml
# docker-compose.dev.yml
services:
  mysql:
    volumes:
      - mysql_dev_data:/var/lib/mysql

# 데이터는 볼륨에 저장
# 컨테이너 재시작해도 데이터 유지
```

### "패키지 설치는요?"

```bash
# 컨테이너 안에서
docker-compose exec backend pip install requests

# requirements.txt에 추가
echo "requests==2.31.0" >> backend/requirements.txt

# 컨테이너 재빌드
docker-compose up -d --build backend

# 팀원들도 같은 패키지 사용!
```

## 📊 비교

| 항목 | 로컬 개발 | Docker 개발 |
|------|----------|------------|
| 환경 일치 | ❌ 개발자마다 다름 | ✅ 모두 동일 |
| 온보딩 시간 | 2-3시간 | 10분 |
| 프로덕션 일치 | ❌ 차이 있음 | ✅ 완전 동일 |
| 의존성 충돌 | ❌ 발생 가능 | ✅ 격리됨 |
| 디버깅 | ✅ 쉬움 | ✅ 쉬움 (Dev Container) |
| 성능 | ✅ 빠름 | ✅ 빠름 (요즘) |
| "제 컴퓨터에선" | ❌ 자주 발생 | ✅ 절대 발생 안 함 |

## 🎓 결론

**Docker로 개발하면:**
1. ✅ 팀원 모두 같은 환경
2. ✅ 프로덕션과 같은 환경
3. ✅ 신규 팀원 즉시 시작
4. ✅ 의존성 충돌 제로
5. ✅ 버전 차이 문제 제로
6. ✅ "제 컴퓨터에서는" 문제 제로

**로컬 개발은:**
1. ❌ 환경 차이 문제
2. ❌ 의존성 지옥
3. ❌ 온보딩 어려움
4. ❌ 프로덕션 배포 시 예상치 못한 에러

## 🚀 시작하기

```bash
# Docker만 설치하면 됩니다
1. Docker Desktop 설치
2. VS Code + Dev Containers 확장 설치

# 개발 시작
3. code .
4. F1 → "Reopen in Container"

# 끝! 이제 개발하세요
```

---

**"로컬에 Python 설치하고 싶으세요?"**

→ 이 템플릿을 사용하지 마세요.
→ Docker 없는 다른 템플릿을 찾으세요.

**이 템플릿은 Docker 필수입니다!** 🐳

---

## 📱 React Native도 Docker인가요?

**네, Metro 서버는 Docker에서 실행됩니다!**

### Metro 서버 (Docker)

```yaml
# docker-compose.dev.yml
services:
  frontend:
    build: ./frontend
    ports:
      - "8081:8081"
```

**장점:**
- ✅ Node.js 로컬 설치 불필요
- ✅ 모든 개발자가 같은 Node 버전
- ✅ npm 의존성 충돌 제로

### 앱 빌드 및 실행

#### 완전 Docker 방식 (WiFi 디버깅) ⭐

```bash
# Docker에서 앱 빌드 후 WiFi로 기기에 전송
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555
docker-compose -f docker-compose.dev.yml exec frontend npm run android
```

**장점:**
- ✅ Node.js 로컬 설치 완전히 불필요
- ✅ 100% Docker로만 개발
- ✅ USB 케이블 불필요
- ✅ 팀원 모두 완전히 같은 환경

**자세한 가이드:** [WIFI_DEBUGGING.md](WIFI_DEBUGGING.md)

#### 로컬 방식 (선택)

Android/iOS 에뮬레이터에서 로컬 npm 사용도 가능합니다.

**하지만:**
- Node.js 로컬 설치 필요
- 환경 차이 가능성

---

**결론: 백엔드도 Docker, Metro 서버도 Docker, 앱 빌드도 Docker!** 🐳

**WiFi 디버깅으로 100% Docker 개발이 가능합니다!**
