# 🚀 빠른 시작 가이드

## 📱 APK 파일 직접 설치 (가장 빠름)

### 방법 1: Android Studio로 빌드 후 설치

1. **Android Studio 설치**
   - https://developer.android.com/studio 에서 다운로드
   - 설치 후 Android SDK 구성 요소 다운로드 완료까지 대기

2. **프로젝트 열기**
   ```
   Android Studio 실행 > File > Open
   → android-app 폴더 선택
   ```

3. **Gradle 동기화 대기**
   - 처음 열면 자동으로 Gradle 동기화 시작
   - 하단 상태바에서 진행 상황 확인
   - 완료까지 5-10분 소요

4. **디바이스 연결**
   - USB 케이블로 안드로이드 폰 연결
   - 폰 설정에서 **개발자 옵션** 활성화
     - 설정 > 휴대전화 정보 > 빌드 번호 7번 연속 터치
   - **USB 디버깅** 활성화
     - 설정 > 개발자 옵션 > USB 디버깅 ON
   - 폰에서 "USB 디버깅 허용" 팝업 승인

5. **앱 실행**
   ```
   상단 툴바에서:
   - 디바이스 선택 (연결된 폰 이름)
   - 녹색 실행 버튼 (▶) 클릭
   ```

### 방법 2: 명령줄로 빌드 (개발자용)

```bash
cd android-app

# Debug APK 빌드
./gradlew assembleDebug

# APK 위치
ls -lh app/build/outputs/apk/debug/app-debug.apk

# ADB로 설치
adb install app/build/outputs/apk/debug/app-debug.apk
```

## 🔑 키스토어 생성 및 릴리즈 빌드

### 1. 릴리즈용 키스토어 생성
```bash
cd android-app

keytool -genkey -v -keystore money-manager.keystore \
  -alias money-manager \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000

# 입력 항목:
# - 비밀번호 (두 번)
# - 이름
# - 조직 단위
# - 조직
# - 구/군/시
# - 시/도
# - 국가 코드 (KR)
```

### 2. `app/build.gradle` 서명 설정 추가
```gradle
android {
    signingConfigs {
        release {
            storeFile file("../money-manager.keystore")
            storePassword "여기에_키스토어_비밀번호"
            keyAlias "money-manager"
            keyPassword "여기에_키_비밀번호"
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
        }
    }
}
```

### 3. 릴리즈 APK 빌드
```bash
./gradlew assembleRelease

# APK 위치
ls -lh app/build/outputs/apk/release/app-release.apk
```

## 🎯 빌드 없이 테스트하기

앱 없이 웹 브라우저로 먼저 테스트:
```
https://money.yeonghoon.kim
```

모바일 브라우저에서:
1. 위 URL 접속
2. 홈 화면에 추가 (PWA처럼 사용 가능)

## ⚠️ 문제 해결

### Gradle 동기화 실패
```bash
# 캐시 삭제
./gradlew clean

# Gradle Wrapper 재설치
./gradlew wrapper --gradle-version 8.1
```

### 디바이스가 인식 안 됨
```bash
# ADB 재시작
adb kill-server
adb start-server

# 연결된 디바이스 확인
adb devices
```

### 앱이 설치되지 않음
- "알 수 없는 출처" 허용 (설정 > 보안)
- 기존 앱 삭제 후 재설치
- USB 디버깅 재활성화

### 빌드 오류 (SDK 버전)
Android Studio에서:
```
Tools > SDK Manager
→ Android 14.0 (API 34) 설치
→ Build Tools 34.0.0 설치
```

## 📦 PlayStore 배포 (선택 사항)

1. Google Play Console 계정 생성 (25달러 일회성 등록비)
2. AAB (Android App Bundle) 빌드
   ```bash
   ./gradlew bundleRelease
   ```
3. `app/build/outputs/bundle/release/app-release.aab` 업로드
4. 앱 정보 입력 (스크린샷, 설명, 개인정보처리방침 등)
5. 심사 제출

## 🎨 앱 아이콘 변경

1. 아이콘 이미지 준비 (512x512 PNG, 투명 배경)
2. [Android Asset Studio](https://romannurik.github.io/AndroidAssetStudio/icons-launcher.html) 접속
3. 이미지 업로드 후 생성
4. 다운로드한 파일을 `app/src/main/res/` 에 덮어쓰기

## 💡 유용한 팁

### WebView 디버깅
```kotlin
// MainActivity.kt의 onCreate()에 추가
if (BuildConfig.DEBUG) {
    WebView.setWebContentsDebuggingEnabled(true)
}
```

Chrome에서 `chrome://inspect` → 디바이스 연결 → WebView 선택

### 로컬 서버 테스트
```kotlin
// MainActivity.kt
private val baseUrl = "http://192.168.1.100:8080"  // 로컬 IP로 변경

// AndroidManifest.xml (개발용만!)
android:usesCleartextTraffic="true"
```

## 📞 지원

문제 발생 시:
1. Logcat 확인: `adb logcat | grep MoneyManager`
2. 빌드 로그 확인: `./gradlew assembleDebug --info`
3. 이슈 등록
