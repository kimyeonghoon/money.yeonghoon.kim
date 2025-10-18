#!/bin/bash

# WiFi 디버깅 문제 해결 스크립트

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔧 WiFi 디버깅 문제 해결"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. Metro 서버 상태 확인
echo "1️⃣  Metro 서버 상태 확인 중..."
echo ""
docker-compose -f docker-compose.dev.yml ps frontend

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Metro 서버가 실행 중이지 않습니다."
    echo ""
    read -p "Metro 서버를 시작하시겠습니까? (y/N): " START_METRO
    if [[ $START_METRO =~ ^[Yy]$ ]]; then
        docker-compose -f docker-compose.dev.yml up -d frontend
        echo "✅ Metro 서버 시작됨"
    fi
else
    echo "✅ Metro 서버 실행 중"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 2. adb 서버 재시작
echo ""
echo "2️⃣  adb 서버 재시작 중..."
echo ""

docker-compose -f docker-compose.dev.yml exec frontend adb kill-server
sleep 1
docker-compose -f docker-compose.dev.yml exec frontend adb start-server

echo "✅ adb 서버 재시작 완료"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 3. 연결된 기기 확인
echo ""
echo "3️⃣  연결된 기기 확인 중..."
echo ""

docker-compose -f docker-compose.dev.yml exec frontend adb devices

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 4. 네트워크 연결 확인
echo ""
echo "4️⃣  네트워크 연결 확인"
echo ""

read -p "Android 기기의 IP 주소를 입력하세요 (예: 192.168.0.100): " DEVICE_IP

if [ -n "$DEVICE_IP" ]; then
    echo ""
    echo "Ping 테스트 중: $DEVICE_IP"
    ping -c 3 $DEVICE_IP

    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ 네트워크 연결 정상"
        echo ""
        read -p "adb 연결을 시도하시겠습니까? (y/N): " RECONNECT
        if [[ $RECONNECT =~ ^[Yy]$ ]]; then
            docker-compose -f docker-compose.dev.yml exec frontend adb connect $DEVICE_IP:5555
        fi
    else
        echo ""
        echo "❌ 네트워크 연결 실패"
        echo ""
        echo "확인 사항:"
        echo "- 기기와 컴퓨터가 같은 WiFi에 연결되어 있나요?"
        echo "- IP 주소가 올바른가요?"
        echo "- 방화벽이 연결을 차단하고 있지 않나요?"
    fi
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📖 추가 도움말"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "자세한 문제 해결 가이드: docs/WIFI_DEBUGGING.md"
echo ""
echo "체크리스트:"
echo "✓ Metro 서버 실행 중 (docker-compose ps frontend)"
echo "✓ Android 무선 디버깅 활성화 (설정 → 개발자 옵션)"
echo "✓ 같은 WiFi 네트워크 연결"
echo "✓ 방화벽 포트 5555, 8081 열려있음"
echo ""
