#!/bin/bash

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# MySQL 데이터베이스 백업 스크립트
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
#
# Docker 컨테이너에서 실행 중인 MySQL 데이터베이스를 백업합니다.
#
# 사용법:
#   bash scripts/backup-mysql.sh [환경]
#
# 예시:
#   bash scripts/backup-mysql.sh          # 개발 환경 백업
#   bash scripts/backup-mysql.sh dev      # 개발 환경 백업
#   bash scripts/backup-mysql.sh prod     # 프로덕션 환경 백업
#
# 백업 파일 위치:
#   backups/mysql_[환경]_YYYYMMDD_HHMMSS.sql
#
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

set -e

# 색상
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 환경 설정 (dev 또는 prod)
ENV=${1:-dev}

# Docker Compose 파일 선택
if [ "$ENV" = "prod" ]; then
    COMPOSE_FILE="docker-compose.yml"
    CONTAINER_NAME="fullstack_mysql"
else
    COMPOSE_FILE="docker-compose.dev.yml"
    CONTAINER_NAME="fullstack_mysql_dev"
fi

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🗄️  MySQL 데이터베이스 백업${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}환경: ${ENV}${NC}"
echo -e "${YELLOW}컨테이너: ${CONTAINER_NAME}${NC}"
echo ""

# 컨테이너 실행 확인
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo -e "${RED}❌ MySQL 컨테이너가 실행 중이지 않습니다.${NC}"
    echo ""
    echo "다음 명령으로 시작하세요:"
    echo "  docker-compose -f $COMPOSE_FILE up -d mysql"
    echo ""
    exit 1
fi

# 백업 디렉토리 생성
BACKUP_DIR="backups"
mkdir -p "$BACKUP_DIR"

# 백업 파일 이름 (타임스탬프 포함)
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/mysql_${ENV}_${TIMESTAMP}.sql"

echo -e "${GREEN}📦 백업 시작...${NC}"
echo ""

# MySQL 환경 변수 가져오기
MYSQL_USER=$(docker-compose -f "$COMPOSE_FILE" exec -T mysql sh -c 'echo $MYSQL_USER')
MYSQL_PASSWORD=$(docker-compose -f "$COMPOSE_FILE" exec -T mysql sh -c 'echo $MYSQL_PASSWORD')
MYSQL_DATABASE=$(docker-compose -f "$COMPOSE_FILE" exec -T mysql sh -c 'echo $MYSQL_DATABASE')

# 공백 제거
MYSQL_USER=$(echo "$MYSQL_USER" | tr -d '[:space:]')
MYSQL_PASSWORD=$(echo "$MYSQL_PASSWORD" | tr -d '[:space:]')
MYSQL_DATABASE=$(echo "$MYSQL_DATABASE" | tr -d '[:space:]')

# mysqldump 실행
if docker-compose -f "$COMPOSE_FILE" exec -T mysql \
    mysqldump \
    -u"$MYSQL_USER" \
    -p"$MYSQL_PASSWORD" \
    --databases "$MYSQL_DATABASE" \
    --add-drop-database \
    --add-drop-table \
    --routines \
    --triggers \
    --events \
    > "$BACKUP_FILE"; then

    # 백업 파일 크기
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)

    echo -e "${GREEN}✅ 백업 완료!${NC}"
    echo ""
    echo -e "${GREEN}📁 파일: ${BACKUP_FILE}${NC}"
    echo -e "${GREEN}📊 크기: ${FILE_SIZE}${NC}"
    echo ""

    # 최근 백업 목록 표시
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📋 최근 백업 파일 (최근 5개)${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""

    ls -lht "$BACKUP_DIR"/mysql_*.sql 2>/dev/null | head -5 | awk '{print "  "$9" ("$5")"}'

    echo ""
    echo -e "${YELLOW}💡 복원 방법:${NC}"
    echo "  bash scripts/restore-mysql.sh $BACKUP_FILE"
    echo ""

else
    echo -e "${RED}❌ 백업 실패!${NC}"
    echo ""
    exit 1
fi

# 오래된 백업 자동 정리 (30일 이상)
OLD_BACKUPS=$(find "$BACKUP_DIR" -name "mysql_${ENV}_*.sql" -type f -mtime +30 2>/dev/null)
if [ -n "$OLD_BACKUPS" ]; then
    echo -e "${YELLOW}🗑️  30일 이상 된 백업 파일 정리...${NC}"
    echo "$OLD_BACKUPS" | while read old_file; do
        echo "  삭제: $old_file"
        rm -f "$old_file"
    done
    echo ""
fi

echo -e "${GREEN}✅ 모든 작업 완료!${NC}"
echo ""
