# WiFi 디버깅으로 완전 Docker 개발

이 가이드는 WiFi 디버깅을 사용하여 **Node.js 로컬 설치 없이** 완전히 Docker만으로 React Native 앱을 개발하는 방법을 설명합니다.

## 🎯 목표

```
Metro 서버 (Docker) ✅
    ↓
앱 빌드 (Docker) ✅
    ↓
WiFi 디버깅 ✅
    ↓
실기기/에뮬레이터 ✅

→ Node.js 로컬 설치 불필요! 🎉
```

## 📱 Android 실기기 설정

### 1. 무선 디버깅 활성화

**Android 11 이상:**
```
설정 → 개발자 옵션 → 무선 디버깅 → ON
```

**개발자 옵션이 안 보인다면:**
```
설정 → 휴대전화 정보 → 빌드 번호를 7번 탭
→ "개발자 모드가 활성화되었습니다" 메시지 확인
```

### 2. 기기 연결 (두 가지 방법)

#### 방법 A: 페어링 코드 사용 (처음 한 번만)

**기기에서:**
```
무선 디버깅 → 페어링 코드로 기기 페어링
→ 6자리 코드와 IP:PORT 표시
예: 123456, 192.168.0.100:37893
```

**Docker에서:**
```bash
docker-compose -f docker-compose.dev.yml exec frontend adb pair 192.168.0.100:37893
# 페어링 코드 입력: 123456
```

#### 방법 B: IP:PORT 직접 연결

**기기에서:**
```
무선 디버깅 → IP 주소 및 포트 확인
예: 192.168.0.100:5555
```

**Docker에서:**
```bash
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555
```

### 3. 연결 확인

```bash
docker-compose -f docker-compose.dev.yml exec frontend adb devices

# 출력 예시:
# List of devices attached
# 192.168.0.100:5555    device
```

## 💻 Android 에뮬레이터 설정

### 1. 에뮬레이터 시작 (로컬)

```bash
# Android Studio에서 에뮬레이터 실행
# 또는 명령어로:
emulator -avd Pixel_5_API_31
```

### 2. 에뮬레이터 네트워크 설정

에뮬레이터는 기본적으로 adb over TCP가 활성화되어 있습니다:

```bash
# 로컬 adb로 에뮬레이터의 TCP 포트 확인
adb devices
# emulator-5554    device

# TCP 포트 활성화 (이미 활성화되어 있을 수 있음)
adb -s emulator-5554 tcpip 5555
```

### 3. Docker에서 연결

```bash
# 호스트 머신의 IP 확인
# Windows: ipconfig
# Mac/Linux: ifconfig

# Docker에서 연결 (호스트 IP 사용)
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555
```

**또는 host.docker.internal 사용:**
```bash
docker-compose -f docker-compose.dev.yml exec frontend adb connect host.docker.internal:5555
```

## 🚀 앱 실행

### 1. Metro 서버 시작

```bash
docker-compose -f docker-compose.dev.yml up -d frontend

# 로그 확인
docker-compose -f docker-compose.dev.yml logs -f frontend
```

### 2. 앱 빌드 및 설치

```bash
# Docker 컨테이너에서 빌드
docker-compose -f docker-compose.dev.yml exec frontend npm run android

# 내부적으로 실행되는 과정:
# 1. Gradle로 Android 프로젝트 빌드
# 2. APK 생성
# 3. adb로 WiFi를 통해 기기에 설치
# 4. 앱 실행
# 5. Metro 서버에 연결 (http://[컴퓨터IP]:8081)
```

### 3. 개발 중

코드를 수정하면:
- ✅ 자동으로 Hot Reload
- ✅ Fast Refresh 작동
- ✅ Metro 서버가 새 번들 전송

## 🔧 문제 해결

### adb 연결이 안 될 때

```bash
# 1. 기기와 컴퓨터가 같은 WiFi에 연결되어 있는지 확인
# 2. 방화벽 확인 (포트 5555, 8081 열려있어야 함)

# 3. adb 서버 재시작
docker-compose -f docker-compose.dev.yml exec frontend adb kill-server
docker-compose -f docker-compose.dev.yml exec frontend adb start-server

# 4. 다시 연결
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555
```

### Metro 서버 연결 실패

**증상:** 앱이 실행되지만 "Could not connect to development server" 에러

**원인:** 앱이 localhost:8081로 연결 시도

**해결:**

1. **개발 설정에서 수동으로 Metro 서버 주소 설정**

   앱에서: 흔들기 → Settings → Debug server host & port
   ```
   192.168.0.100:8081
   ```

2. **또는 코드에서 설정**

   `android/app/src/main/java/com/yourapp/MainActivity.java` (또는 Kotlin):
   ```java
   @Override
   protected String getJSMainModuleName() {
       return "index";
   }

   @Override
   protected String getJSBundleFile() {
       return "http://192.168.0.100:8081/index.bundle?platform=android";
   }
   ```

### 빌드가 느릴 때

```bash
# Gradle 데몬 사용 (첫 빌드는 느림)
# 두 번째 빌드부터 빠름

# 캐시 정리가 필요한 경우
docker-compose -f docker-compose.dev.yml exec frontend bash
cd android
./gradlew clean
```

## 📊 완전 Docker 방식 vs 로컬 방식 비교

| 항목 | 완전 Docker (WiFi) | 로컬 npm |
|------|-------------------|---------|
| Metro 서버 | ✅ Docker | ✅ Docker |
| 앱 빌드 | ✅ Docker | ❌ 로컬 |
| Node.js 로컬 설치 | ✅ 불필요 | ❌ 필요 |
| 환경 일치 | ✅ 100% | ⚠️ Metro만 |
| 설정 복잡도 | ⚠️ WiFi 설정 필요 | ✅ 간단 |
| USB 케이블 | ✅ 불필요 | ⚠️ 필요 (선택) |

## 🎓 추천 워크플로우

### 초기 설정 (한 번만)

```bash
# 1. Docker 컨테이너 시작
docker-compose -f docker-compose.dev.yml up -d frontend

# 2. Android 기기를 WiFi 디버깅 모드로 설정
# 3. 페어링 (처음 한 번만)
docker-compose -f docker-compose.dev.yml exec frontend adb pair 192.168.0.100:37893

# 4. 연결
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555

# 5. 첫 빌드 (시간 소요)
docker-compose -f docker-compose.dev.yml exec frontend npm run android
```

### 일상적인 개발

```bash
# 1. Metro 서버 시작 (이미 실행 중이면 생략)
docker-compose -f docker-compose.dev.yml up -d frontend

# 2. 코드 수정
# → 자동 Hot Reload!

# 3. 네이티브 코드 변경 시에만 재빌드
docker-compose -f docker-compose.dev.yml exec frontend npm run android
```

### 종료

```bash
# adb 연결 해제
docker-compose -f docker-compose.dev.yml exec frontend adb disconnect

# Metro 서버 중지
docker-compose -f docker-compose.dev.yml stop frontend
```

## 💡 팁

### 1. 여러 기기 동시 연결

```bash
# 기기 1
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555

# 기기 2
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.101:5555

# 특정 기기에만 설치
docker-compose -f docker-compose.dev.yml exec frontend adb -s 192.168.0.100:5555 install app-debug.apk
```

### 2. 자동 재연결 스크립트

```bash
# frontend/reconnect-adb.sh
#!/bin/bash
DEVICE_IP=${1:-192.168.0.100}
docker-compose -f docker-compose.dev.yml exec frontend adb connect $DEVICE_IP:5555
docker-compose -f docker-compose.dev.yml exec frontend adb devices
```

### 3. Metro 서버 주소 자동 감지

`.env` 파일에 추가:
```env
METRO_HOST=192.168.0.100
```

## 🎉 결론

WiFi 디버깅을 사용하면:

✅ **Node.js 로컬 설치 불필요**
✅ **완전히 Docker만으로 개발**
✅ **환경 일치 100% 보장**
✅ **USB 케이블 불필요**
✅ **팀원 모두 같은 개발 환경**

초기 설정은 약간 번거롭지만, 한 번 설정하면 **"제 컴퓨터에서는 되는데요?"** 문제가 완전히 사라집니다! 🚀

---

**관련 문서:**
- [WHY_DOCKER.md](WHY_DOCKER.md) - Docker 개발의 이유
- [DEVELOPMENT.md](DEVELOPMENT.md) - 개발 환경 가이드
- [frontend/README.md](../frontend/README.md) - 프론트엔드 설정
