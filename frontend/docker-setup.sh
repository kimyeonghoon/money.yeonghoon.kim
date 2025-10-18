#!/bin/bash

# React Native 프로젝트를 Docker 환경에서 초기화하는 스크립트
echo "========================================"
echo "React Native 프로젝트 Docker 초기화"
echo "========================================"

# 프로젝트 이름 입력
read -p "React Native 프로젝트 이름을 입력하세요 (기본값: MobileApp): " PROJECT_NAME
PROJECT_NAME=${PROJECT_NAME:-MobileApp}

echo ""
echo "프로젝트 이름: $PROJECT_NAME"
echo ""

# Docker 컨테이너에서 React Native 프로젝트 생성
echo "Docker 컨테이너에서 React Native 프로젝트 생성 중..."
docker run --rm -it \
  -v "$(pwd):/app" \
  -w /app \
  node:20-slim \
  bash -c "npx react-native@latest init $PROJECT_NAME --skip-install && cd $PROJECT_NAME && npm install"

if [ $? -ne 0 ]; then
    echo "Error: React Native 프로젝트 생성에 실패했습니다."
    exit 1
fi

cd $PROJECT_NAME

# package.json에 필요한 패키지 추가
echo ""
echo "필요한 패키지를 package.json에 추가 중..."

# Docker 컨테이너에서 패키지 설치
echo ""
echo "Docker 컨테이너에서 패키지 설치 중..."
docker run --rm -it \
  -v "$(pwd):/app" \
  -w /app \
  node:20-slim \
  bash -c "npm install react-native-paper@5.12.5 react-native-vector-icons@10.2.0 react-native-safe-area-context@4.14.0 @react-navigation/native@6.1.18 @react-navigation/stack@6.4.1 @react-navigation/bottom-tabs@6.6.1 react-native-screens@3.34.0 react-native-gesture-handler@2.20.2 axios@1.7.9 @react-native-async-storage/async-storage@2.1.0"

# docker-compose.dev.yml의 볼륨 경로 업데이트
echo ""
echo "docker-compose.dev.yml 업데이트 중..."
cd ..
sed -i.bak "s|./frontend/MobileApp|./frontend/$PROJECT_NAME|g" ../docker-compose.dev.yml
rm -f ../docker-compose.dev.yml.bak

echo ""
echo "========================================"
echo "✅ React Native 프로젝트 Docker 초기화 완료!"
echo "========================================"
echo ""
echo "프로젝트 디렉토리: frontend/$PROJECT_NAME"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📱 다음 단계: 완전 Docker 개발 (권장)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "1️⃣  Metro 서버 시작 (Docker)"
echo "   cd .."
echo "   docker-compose -f docker-compose.dev.yml up -d frontend"
echo ""
echo "2️⃣  Android 기기 WiFi 디버깅 설정"
echo "   기기: 설정 → 개발자 옵션 → 무선 디버깅 활성화"
echo "   IP 주소 확인 (예: 192.168.0.100:5555)"
echo ""
echo "3️⃣  adb 연결 (Docker에서)"
echo "   docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555"
echo ""
echo "4️⃣  앱 빌드 및 설치 (Docker에서)"
echo "   docker-compose -f docker-compose.dev.yml exec frontend npm run android"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ Node.js 로컬 설치 없이 완전 Docker 개발!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📖 자세한 가이드: ../docs/WIFI_DEBUGGING.md"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "대안: 로컬 Node.js 사용 (권장하지 않음)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "- Android: cd frontend/$PROJECT_NAME && npm run android"
echo "- iOS: cd frontend/$PROJECT_NAME && npm run ios"
echo ""
