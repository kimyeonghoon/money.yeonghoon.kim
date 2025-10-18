#!/bin/bash

# WiFi 디버깅 - adb 연결 스크립트
# 사용법: bash scripts/adb-connect.sh [IP주소]

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📱 WiFi 디버깅 - adb 연결"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# IP 주소 입력
if [ -z "$1" ]; then
    read -p "Android 기기 IP 주소를 입력하세요 (예: 192.168.0.100): " DEVICE_IP
else
    DEVICE_IP=$1
fi

# 포트 입력
if [ -z "$2" ]; then
    PORT="5555"
else
    PORT=$2
fi

echo ""
echo "연결 중: $DEVICE_IP:$PORT"
echo ""

# adb 연결
docker-compose -f docker-compose.dev.yml exec frontend adb connect $DEVICE_IP:$PORT

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ 연결 성공!"
    echo ""
    echo "연결된 기기 목록:"
    docker-compose -f docker-compose.dev.yml exec frontend adb devices
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "다음 단계:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "앱 빌드 및 설치:"
    echo "  docker-compose -f docker-compose.dev.yml exec frontend npm run android"
    echo ""
    echo "또는 헬퍼 스크립트 사용:"
    echo "  bash scripts/dev-start.sh"
    echo ""
else
    echo ""
    echo "❌ 연결 실패"
    echo ""
    echo "문제 해결:"
    echo "1. 기기와 컴퓨터가 같은 WiFi에 연결되어 있는지 확인"
    echo "2. Android 기기에서 무선 디버깅이 활성화되어 있는지 확인"
    echo "   설정 → 개발자 옵션 → 무선 디버깅"
    echo "3. 방화벽 설정 확인 (포트 5555 열려있어야 함)"
    echo ""
    echo "문제 해결 스크립트:"
    echo "  bash scripts/adb-troubleshoot.sh"
    echo ""
fi
