#!/bin/bash
#
# 매월 스냅샷 자동 생성 스크립트
#
# 사용법: ./create-monthly-snapshot.sh [YEAR] [MONTH]
# 파라미터 없이 실행 시 이전 달의 스냅샷 생성
#

# 로그 파일 경로
LOG_DIR="/home/kim-yeonghoon/workspace/money.yeonghoon.kim/logs"
LOG_FILE="$LOG_DIR/snapshot-$(date +%Y%m).log"

# 로그 디렉토리가 없으면 생성
mkdir -p "$LOG_DIR"

# 로그 함수
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "========== 스냅샷 생성 시작 =========="

# 파라미터로 년/월이 주어지지 않으면 이전 달 계산
if [ -z "$1" ] || [ -z "$2" ]; then
    # 이전 달 계산
    YEAR=$(date -d "last month" +%Y)
    MONTH=$(date -d "last month" +%-m)
    log "파라미터 없음, 이전 달로 설정: ${YEAR}년 ${MONTH}월"
else
    YEAR=$1
    MONTH=$2
    log "파라미터로 지정된 날짜: ${YEAR}년 ${MONTH}월"
fi

# API 호출
API_URL="https://money.yeonghoon.kim/api/archive/create-snapshot?year=${YEAR}&month=${MONTH}"
log "API 호출: $API_URL"

# 쿠키 파일 (임시)
COOKIE_FILE="/tmp/snapshot_session_$(date +%s).txt"

# 로그인 정보 (환경변수에서 읽기)
if [ -z "$SNAPSHOT_EMAIL" ] || [ -z "$SNAPSHOT_PASSWORD" ]; then
    log "✗ 환경변수 미설정: SNAPSHOT_EMAIL, SNAPSHOT_PASSWORD"
    log "사용법: SNAPSHOT_EMAIL=your@email SNAPSHOT_PASSWORD=yourpass $0"
    exit 1
fi

# 로그인하여 세션 쿠키 생성
log "로그인 중..."
LOGIN_RESPONSE=$(curl -X POST "https://money.yeonghoon.kim/login.php" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "email=$(printf %s "$SNAPSHOT_EMAIL" | sed 's/@/%40/g')&password=$(printf %s "$SNAPSHOT_PASSWORD" | sed 's/=/%3D/g')" \
    -c "$COOKIE_FILE" \
    --max-time 10 \
    --silent \
    --show-error 2>&1)

if [ ! -s "$COOKIE_FILE" ]; then
    log "✗ 로그인 실패"
    rm -f "$COOKIE_FILE"
    exit 1
fi

log "✓ 로그인 성공"

# API 호출 (타임아웃 30초)
RESPONSE=$(curl -X POST "$API_URL" \
    -b "$COOKIE_FILE" \
    --max-time 30 \
    --silent \
    --show-error 2>&1)

# 임시 쿠키 파일 삭제
rm -f "$COOKIE_FILE"

# 응답 확인
if echo "$RESPONSE" | grep -q '"success":true'; then
    log "✓ 스냅샷 생성 성공: ${YEAR}년 ${MONTH}월"
    log "응답: $RESPONSE"
    EXIT_CODE=0
else
    log "✗ 스냅샷 생성 실패: ${YEAR}년 ${MONTH}월"
    log "응답: $RESPONSE"
    EXIT_CODE=1
fi

log "========== 스냅샷 생성 종료 =========="
echo ""

exit $EXIT_CODE