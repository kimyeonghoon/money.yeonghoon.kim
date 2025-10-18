#!/bin/bash

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# MySQL 데이터베이스 복원 스크립트
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
#
# 백업 파일로부터 MySQL 데이터베이스를 복원합니다.
#
# 사용법:
#   bash scripts/restore-mysql.sh <백업파일> [환경]
#
# 예시:
#   bash scripts/restore-mysql.sh backups/mysql_dev_20250117_143000.sql
#   bash scripts/restore-mysql.sh backups/mysql_prod_20250117_143000.sql prod
#
# ⚠️  주의:
#   - 복원 시 기존 데이터가 모두 삭제됩니다!
#   - 반드시 현재 데이터를 백업한 후 복원하세요!
#
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

set -e

# 색상
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 백업 파일 경로
BACKUP_FILE=$1

# 파일 확인
if [ -z "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ 백업 파일을 지정해주세요.${NC}"
    echo ""
    echo "사용법:"
    echo "  bash scripts/restore-mysql.sh <백업파일> [환경]"
    echo ""
    echo "예시:"
    echo "  bash scripts/restore-mysql.sh backups/mysql_dev_20250117_143000.sql"
    echo ""

    # 사용 가능한 백업 파일 목록
    if [ -d "backups" ] && [ "$(ls -A backups/mysql_*.sql 2>/dev/null)" ]; then
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo -e "${BLUE}📋 사용 가능한 백업 파일:${NC}"
        echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
        echo ""
        ls -lht backups/mysql_*.sql | awk '{print "  "$9" ("$5")"}'
        echo ""
    fi

    exit 1
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ 백업 파일을 찾을 수 없습니다: $BACKUP_FILE${NC}"
    echo ""
    exit 1
fi

# 환경 설정 (dev 또는 prod)
ENV=${2:-dev}

# Docker Compose 파일 선택
if [ "$ENV" = "prod" ]; then
    COMPOSE_FILE="docker-compose.yml"
    CONTAINER_NAME="fullstack_mysql"
else
    COMPOSE_FILE="docker-compose.dev.yml"
    CONTAINER_NAME="fullstack_mysql_dev"
fi

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}♻️  MySQL 데이터베이스 복원${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}환경: ${ENV}${NC}"
echo -e "${YELLOW}컨테이너: ${CONTAINER_NAME}${NC}"
echo -e "${YELLOW}백업 파일: ${BACKUP_FILE}${NC}"
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

# 경고 메시지
echo -e "${RED}⚠️  경고: 복원 시 기존 데이터가 모두 삭제됩니다!${NC}"
echo ""
read -p "계속하시겠습니까? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo ""
    echo -e "${YELLOW}복원이 취소되었습니다.${NC}"
    echo ""
    exit 0
fi

echo ""
echo -e "${GREEN}♻️  복원 시작...${NC}"
echo ""

# MySQL 환경 변수 가져오기
MYSQL_USER=$(docker-compose -f "$COMPOSE_FILE" exec -T mysql sh -c 'echo $MYSQL_USER')
MYSQL_PASSWORD=$(docker-compose -f "$COMPOSE_FILE" exec -T mysql sh -c 'echo $MYSQL_PASSWORD')

# 공백 제거
MYSQL_USER=$(echo "$MYSQL_USER" | tr -d '[:space:]')
MYSQL_PASSWORD=$(echo "$MYSQL_PASSWORD" | tr -d '[:space:]')

# mysql 복원
if cat "$BACKUP_FILE" | docker-compose -f "$COMPOSE_FILE" exec -T mysql \
    mysql \
    -u"$MYSQL_USER" \
    -p"$MYSQL_PASSWORD"; then

    echo ""
    echo -e "${GREEN}✅ 복원 완료!${NC}"
    echo ""
    echo -e "${GREEN}📁 복원된 파일: ${BACKUP_FILE}${NC}"
    echo ""

    # 데이터베이스 목록 확인
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}📊 데이터베이스 확인${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""

    docker-compose -f "$COMPOSE_FILE" exec -T mysql \
        mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SHOW DATABASES;"

    echo ""
    echo -e "${GREEN}✅ 모든 작업 완료!${NC}"
    echo ""

else
    echo ""
    echo -e "${RED}❌ 복원 실패!${NC}"
    echo ""
    exit 1
fi
