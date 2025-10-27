#!/bin/bash

echo "=========================================="
echo "웹 인증 상태 진단 스크립트"
echo "=========================================="
echo ""

echo "1. 백엔드 서버 상태 확인..."
BACKEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/docs 2>/dev/null)
if [ "$BACKEND_STATUS" == "200" ]; then
    echo "   ✅ 백엔드 서버 실행 중 (http://localhost:8000)"
else
    echo "   ❌ 백엔드 서버 응답 없음 (Status: $BACKEND_STATUS)"
fi
echo ""

echo "2. 프론트엔드 서버 상태 확인..."
FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:3000 2>/dev/null)
if [ "$FRONTEND_STATUS" == "200" ]; then
    echo "   ✅ 프론트엔드 서버 실행 중 (http://localhost:3000)"
else
    echo "   ❌ 프론트엔드 서버 응답 없음 (Status: $FRONTEND_STATUS)"
fi
echo ""

echo "3. 테스트 로그인 시도..."
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test"}')

ACCESS_TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)

if [ -n "$ACCESS_TOKEN" ]; then
    echo "   ✅ 로그인 성공"
    echo "   Access Token: ${ACCESS_TOKEN:0:20}..."
    echo ""

    echo "4. 토큰으로 고정지출 목록 조회 테스트..."
    FIXED_EXPENSES=$(curl -s -X GET http://localhost:8000/api/v1/fixed-expenses \
      -H "Authorization: Bearer $ACCESS_TOKEN")

    if echo "$FIXED_EXPENSES" | grep -q "id"; then
        echo "   ✅ 인증된 API 호출 성공"
        echo "   고정지출 개수: $(echo "$FIXED_EXPENSES" | grep -o '"id":' | wc -l)"
    else
        echo "   ❌ 인증된 API 호출 실패"
        echo "   응답: $FIXED_EXPENSES"
    fi
    echo ""

    echo "5. 고정지출 생성 테스트..."
    CREATE_RESPONSE=$(curl -s -X POST http://localhost:8000/api/v1/fixed-expenses \
      -H "Authorization: Bearer $ACCESS_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{
        "name": "테스트_고정지출",
        "default_amount": 10000,
        "is_fixed_amount": true,
        "expected_payment_day": 1
      }')

    if echo "$CREATE_RESPONSE" | grep -q "id"; then
        echo "   ✅ 고정지출 생성 성공"
        CREATED_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":[0-9]*' | cut -d':' -f2)
        echo "   생성된 ID: $CREATED_ID"

        echo ""
        echo "6. 생성된 테스트 데이터 삭제..."
        DELETE_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE \
          "http://localhost:8000/api/v1/fixed-expenses/$CREATED_ID" \
          -H "Authorization: Bearer $ACCESS_TOKEN")

        if [ "$DELETE_RESPONSE" == "204" ]; then
            echo "   ✅ 테스트 데이터 삭제 성공"
        else
            echo "   ⚠️  테스트 데이터 삭제 실패 (Status: $DELETE_RESPONSE)"
        fi
    else
        echo "   ❌ 고정지출 생성 실패"
        echo "   응답: $CREATE_RESPONSE"
    fi
else
    echo "   ❌ 로그인 실패"
    echo "   응답: $LOGIN_RESPONSE"
fi

echo ""
echo "=========================================="
echo "진단 완료"
echo "=========================================="
echo ""
echo "📋 브라우저에서 확인할 사항:"
echo "1. 브라우저 개발자 도구 (F12) 열기"
echo "2. Application → Local Storage → http://localhost:3000"
echo "3. access_token과 refresh_token 키가 있는지 확인"
echo "4. Console 탭에서 401 에러 메시지 확인"
echo ""
echo "🔧 해결 방법:"
echo "- 토큰이 없으면: 로그아웃 후 다시 로그인"
echo "- 토큰이 있는데 401 에러: 페이지 새로고침 (Ctrl+R)"
echo "- 계속 실패하면: localStorage 전체 삭제 후 다시 로그인"
echo ""
