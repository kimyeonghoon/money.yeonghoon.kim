#!/bin/bash

# 개발 환경 한 번에 시작하는 스크립트
# 사용법: bash scripts/dev-start.sh [IP주소]

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🚀 개발 환경 시작"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. 백엔드 및 Metro 서버 시작
echo "1️⃣  백엔드 및 Metro 서버 시작 중..."
echo ""

docker-compose -f docker-compose.dev.yml up -d

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ 서버 시작 완료"
    echo ""
    echo "   - 백엔드 API: http://localhost:8000"
    echo "   - API 문서: http://localhost:8000/docs"
    echo "   - Metro 서버: http://localhost:8081"
    echo ""
else
    echo ""
    echo "❌ 서버 시작 실패"
    exit 1
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 2. 서버가 완전히 시작될 때까지 대기
echo ""
echo "2️⃣  서버 초기화 대기 중..."
sleep 3
echo "✅ 준비 완료"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 3. WiFi 디버깅 연결 (선택)
echo ""
echo "3️⃣  WiFi 디버깅 연결"
echo ""

if [ -z "$1" ]; then
    read -p "Android 기기 IP 주소를 입력하세요 (건너뛰려면 Enter): " DEVICE_IP
else
    DEVICE_IP=$1
fi

if [ -n "$DEVICE_IP" ]; then
    echo ""
    echo "adb 연결 중: $DEVICE_IP:5555"
    docker-compose -f docker-compose.dev.yml exec frontend adb connect $DEVICE_IP:5555

    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ adb 연결 성공"
        echo ""

        # 4. 앱 빌드 및 설치 (선택)
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        read -p "앱을 빌드하고 설치하시겠습니까? (y/N): " BUILD_APP

        if [[ $BUILD_APP =~ ^[Yy]$ ]]; then
            echo ""
            echo "4️⃣  앱 빌드 및 설치 중..."
            echo ""
            docker-compose -f docker-compose.dev.yml exec frontend npm run android

            if [ $? -eq 0 ]; then
                echo ""
                echo "✅ 앱 설치 완료"
            else
                echo ""
                echo "❌ 앱 설치 실패"
            fi
        fi
    else
        echo ""
        echo "❌ adb 연결 실패"
        echo ""
        echo "문제 해결:"
        echo "  bash scripts/adb-troubleshoot.sh"
    fi
else
    echo "건너뛰기 - 나중에 수동으로 연결하세요:"
    echo "  bash scripts/adb-connect.sh [IP주소]"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ 개발 환경 준비 완료!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "실행 중인 서비스:"
docker-compose -f docker-compose.dev.yml ps
echo ""
echo "로그 확인:"
echo "  docker-compose -f docker-compose.dev.yml logs -f"
echo ""
echo "개발 시작하세요! 🎉"
echo ""
