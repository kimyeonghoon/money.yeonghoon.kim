# Frontend Setup

React Native 모바일 앱을 위한 디렉토리입니다.

> **🐳 Docker 환경 지원**: Metro 서버를 Docker에서 실행하여 Node.js 로컬 설치 불필요!

## 사전 요구사항

### Docker 개발

**필수:**
- Docker Desktop

**선택적 (앱 실행용):**
- **Android**: Android Studio, JDK 17, Android SDK
- **iOS** (macOS만): Xcode 14+, CocoaPods

> **💡 참고**: Metro 서버는 Docker에서 실행됩니다. Node.js 로컬 설치가 필요하지 않습니다!

## 프로젝트 초기화

### Docker 환경

```bash
cd frontend
bash docker-setup.sh

# 프로젝트 이름 입력 (예: MobileApp)
# Docker 컨테이너에서 자동으로 프로젝트 생성 및 패키지 설치
```

## 실행

### Docker 환경

**Metro 서버 시작**
```bash
# 루트 디렉토리에서
docker-compose -f docker-compose.dev.yml up -d frontend

# Metro 서버 로그 확인
docker-compose -f docker-compose.dev.yml logs -f frontend

# Metro: http://localhost:8081
```

**앱 실행**

#### 방법 1: 완전 Docker 방식 (권장) - WiFi 디버깅 사용

```bash
# 1. Android 기기를 WiFi 디버깅 모드로 설정
# 기기: 설정 → 개발자 옵션 → 무선 디버깅 활성화
# 페어링 코드로 연결하거나 IP:PORT 확인 (예: 192.168.0.100:5555)

# 2. Docker 컨테이너에서 adb 연결
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555

# 3. 연결 확인
docker-compose -f docker-compose.dev.yml exec frontend adb devices

# 4. 앱 빌드 및 설치 (Docker에서)
docker-compose -f docker-compose.dev.yml exec frontend npm run android

# APK가 빌드되고 WiFi를 통해 기기에 설치 및 실행됩니다!
```

**장점:**
- ✅ Node.js 로컬 설치 불필요
- ✅ 완전히 Docker만으로 개발
- ✅ Metro 서버와 빌드 환경 모두 격리

#### 방법 2: 로컬 Node.js 사용 (Node.js가 이미 설치된 경우)

```bash
# Android
cd frontend/MobileApp
npm run android

# iOS (macOS만)
npm run ios
```

> **💡 참고**:
> - **Metro 서버**는 Docker에서 실행됩니다
> - **앱 빌드 및 설치**는 Docker 또는 로컬에서 가능합니다
> - WiFi 디버깅 사용 시 **Node.js 로컬 설치가 전혀 필요 없습니다**!

## API 연동

백엔드 API URL을 설정하려면:

1. `src/services/api.ts` 파일 생성
2. axios 인스턴스 구성
3. 인증 토큰 관리

예제 코드는 프로젝트 초기화 후 제공됩니다.
