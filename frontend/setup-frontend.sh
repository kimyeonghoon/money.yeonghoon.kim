#!/bin/bash

# React Native 프로젝트 초기화 스크립트 (로컬 환경 - 비권장)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⚠️  경고: 로컬 개발 환경 (권장하지 않음)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "이 스크립트는 로컬에 Node.js 설치가 필요합니다."
echo ""
echo "🐳 Docker 환경을 강력히 권장합니다!"
echo ""
echo "Docker 환경의 장점:"
echo "  ✅ Node.js 로컬 설치 불필요"
echo "  ✅ 팀원 모두 같은 개발 환경"
echo "  ✅ WiFi 디버깅으로 완전 Docker 개발 가능"
echo "  ✅ '제 컴퓨터에서는' 문제 제로"
echo ""
echo "Docker 환경 사용: bash docker-setup.sh"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
read -p "그래도 로컬 환경으로 계속하시겠습니까? (y/N): " CONTINUE
if [[ ! $CONTINUE =~ ^[Yy]$ ]]; then
    echo ""
    echo "✅ 좋은 선택입니다! docker-setup.sh를 사용하세요."
    exit 0
fi
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "로컬 환경으로 React Native 프로젝트 초기화 시작"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 현재 디렉토리 확인
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Node.js와 npm이 설치되어 있는지 확인
if ! command -v node &> /dev/null; then
    echo "Error: Node.js가 설치되어 있지 않습니다."
    echo "https://nodejs.org 에서 Node.js를 설치해주세요."
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "Error: npm이 설치되어 있지 않습니다."
    exit 1
fi

echo "Node.js 버전: $(node -v)"
echo "npm 버전: $(npm -v)"
echo ""

# React Native CLI가 설치되어 있는지 확인
if ! command -v npx &> /dev/null; then
    echo "npx가 설치되어 있지 않습니다. npm을 업데이트하세요."
    exit 1
fi

# 프로젝트 이름 입력 받기
read -p "React Native 프로젝트 이름을 입력하세요 (기본값: MobileApp): " PROJECT_NAME
PROJECT_NAME=${PROJECT_NAME:-MobileApp}

echo ""
echo "프로젝트 이름: $PROJECT_NAME"
echo ""

# React Native 프로젝트 생성
echo "React Native 프로젝트 생성 중..."
npx react-native@latest init $PROJECT_NAME --skip-install

if [ $? -ne 0 ]; then
    echo "Error: React Native 프로젝트 생성에 실패했습니다."
    exit 1
fi

cd $PROJECT_NAME

# 의존성 설치
echo ""
echo "의존성 설치 중..."
npm install

# React Native Paper 및 필수 의존성 설치
echo ""
echo "React Native Paper 설치 중..."
npm install react-native-paper@5.12.5 react-native-vector-icons@10.2.0 react-native-safe-area-context@4.14.0

# 네비게이션 라이브러리 설치
echo ""
echo "React Navigation 설치 중..."
npm install @react-navigation/native@6.1.18 @react-navigation/stack@6.4.1 @react-navigation/bottom-tabs@6.6.1
npm install react-native-screens@3.34.0 react-native-gesture-handler@2.20.2

# HTTP 클라이언트 및 상태 관리
echo ""
echo "추가 라이브러리 설치 중..."
npm install axios@1.7.9 @react-native-async-storage/async-storage@2.1.0

# iOS 의존성 설치 (macOS에서만)
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo ""
    echo "iOS CocoaPods 의존성 설치 중..."
    cd ios
    pod install
    cd ..
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ React Native 프로젝트 초기화 완료!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "프로젝트 디렉토리: $SCRIPT_DIR/$PROJECT_NAME"
echo ""
echo "다음 단계 (로컬 환경):"
echo "1. cd $PROJECT_NAME"
echo "2. 백엔드 API URL 설정 (src/services/api.ts)"
echo "3. Metro 서버 시작: npm start"
echo "4. Android: npm run android"
echo "5. iOS (macOS only): npm run ios"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⚠️  주의: 로컬 환경은 팀원 간 환경 차이가 발생할 수 있습니다"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🐳 더 나은 방법: Docker 환경으로 전환"
echo "   1. 이 프로젝트 삭제"
echo "   2. bash docker-setup.sh 실행"
echo "   3. WiFi 디버깅으로 완전 Docker 개발"
echo ""
echo "📖 자세한 가이드:"
echo "   - ../docs/WIFI_DEBUGGING.md"
echo "   - ../docs/WHY_DOCKER.md"
echo ""
