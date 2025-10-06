# 머니매니저 - 안드로이드 웹뷰 앱

`money.yeonghoon.kim` 웹 애플리케이션의 안드로이드 네이티브 앱 래퍼입니다.

## 📱 앱 정보

- **앱 이름**: 머니매니저
- **패키지명**: `com.yeonghoon.moneymanager`
- **버전**: 1.0.0
- **최소 SDK**: Android 7.0 (API 24)
- **대상 SDK**: Android 14 (API 34)

## 🚀 주요 기능

### ✅ WebView 최적화
- **세션 관리**: 쿠키 및 로컬스토리지 자동 저장
- **JavaScript 활성화**: 완전한 웹 앱 기능 지원
- **캐시 관리**: 자동 캐싱으로 빠른 로딩

### ✅ 사용자 경험
- **뒤로가기 버튼**: 웹 페이지 히스토리 네비게이션
- **당겨서 새로고침**: SwipeRefreshLayout 지원
- **진행률 표시**: 상단 프로그레스바
- **세로 모드 고정**: 모바일 최적화

### ✅ 보안
- **HTTPS 강제**: Cleartext Traffic 차단
- **SSL 검증**: 인증서 오류 시 연결 차단
- **도메인 제한**: `money.yeonghoon.kim`만 허용
- **백업 제외**: 민감한 쿠키/세션 데이터 백업 안 함

### ✅ 에러 핸들링
- **네트워크 오류**: 재시도 다이얼로그
- **HTTP 에러**: 서버 오류 안내
- **SSL 에러**: 보안 경고 표시

## 🛠️ 빌드 방법

### 1. 사전 요구사항
- Android Studio (최신 버전 권장)
- JDK 17 이상
- Android SDK 34

### 2. 프로젝트 열기
```bash
# Android Studio에서 열기
File > Open > android-app 폴더 선택
```

### 3. 빌드
```bash
# Gradle 빌드 (터미널)
cd android-app
./gradlew assembleDebug

# 또는 Android Studio에서
Build > Build Bundle(s) / APK(s) > Build APK(s)
```

### 4. APK 위치
```
android-app/app/build/outputs/apk/debug/app-debug.apk
```

### 5. 디바이스 설치
```bash
# ADB로 설치
adb install app/build/outputs/apk/debug/app-debug.apk
```

## 📦 릴리즈 빌드

### 1. 키스토어 생성
```bash
keytool -genkey -v -keystore money-manager.keystore \
  -alias money-manager \
  -keyalg RSA -keysize 2048 -validity 10000
```

### 2. `app/build.gradle` 서명 설정 추가
```gradle
android {
    signingConfigs {
        release {
            storeFile file("../money-manager.keystore")
            storePassword "YOUR_STORE_PASSWORD"
            keyAlias "money-manager"
            keyPassword "YOUR_KEY_PASSWORD"
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
```

### 4. APK 위치
```
android-app/app/build/outputs/apk/release/app-release.apk
```

## 🎨 앱 아이콘 변경

기본 아이콘을 사용 중입니다. 커스텀 아이콘을 원한다면:

1. [Android Asset Studio](https://romannurik.github.io/AndroidAssetStudio/icons-launcher.html)에서 아이콘 생성
2. 생성된 파일을 `app/src/main/res/` 폴더에 복사
   - `mipmap-hdpi/ic_launcher.png`
   - `mipmap-mdpi/ic_launcher.png`
   - `mipmap-xhdpi/ic_launcher.png`
   - `mipmap-xxhdpi/ic_launcher.png`
   - `mipmap-xxxhdpi/ic_launcher.png`

## 🔧 개발자 옵션

### URL 변경
`MainActivity.kt`에서 `baseUrl` 변경:
```kotlin
private val baseUrl = "https://money.yeonghoon.kim"  // 여기를 수정
```

### 개발 환경 테스트 (로컬 서버)
```kotlin
// AndroidManifest.xml에서 cleartext traffic 허용 (개발용만!)
android:usesCleartextTraffic="true"

// MainActivity.kt
private val baseUrl = "http://192.168.1.100:8080"  // 로컬 IP
```

⚠️ **주의**: 프로덕션 빌드 시 반드시 `usesCleartextTraffic="false"`로 되돌리기

## 📱 테스트

### 디버그 모드에서 WebView 디버깅
```kotlin
// MainActivity.onCreate()에 추가
if (BuildConfig.DEBUG) {
    WebView.setWebContentsDebuggingEnabled(true)
}
```

Chrome에서 `chrome://inspect`로 디바이스 연결 후 디버깅 가능

## 🐛 문제 해결

### 1. 빌드 오류
```bash
# Gradle 캐시 삭제
./gradlew clean

# Gradle Wrapper 재설치
./gradlew wrapper --gradle-version 8.1
```

### 2. 앱이 로드되지 않음
- 네트워크 연결 확인
- HTTPS 인증서 유효성 확인
- Logcat에서 오류 확인: `adb logcat | grep MoneyManager`

### 3. 쿠키/세션이 저장 안 됨
- `CookieManager` 설정 확인 (MainActivity.kt:59-61)
- 앱 데이터 삭제 후 재시도

## 📄 라이선스

이 프로젝트는 개인용으로 사용됩니다.

## 📞 문의

문제가 발생하면 이슈를 등록해주세요.
