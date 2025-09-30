#!/bin/bash
# =====================================
# 실서버 배포 스크립트
# =====================================
#
# 사용법: ./deploy-production.sh
# 실행 위치: /data/money.yeonghoon.kim
#
# 전제조건:
# 1. 기존 Nginx 서버 실행 중 (80/443 포트)
# 2. 외부 MySQL 서버 접근 가능
# 3. Docker 및 Docker Compose 설치됨
# 4. .env.production 파일 설정 완료

set -e  # 에러 발생 시 즉시 중단

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 로그 함수
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 프로젝트 루트 확인
PROJECT_ROOT="/data/money.yeonghoon.kim"
if [ ! -d "$PROJECT_ROOT" ]; then
    log_error "프로젝트 디렉토리를 찾을 수 없습니다: $PROJECT_ROOT"
    exit 1
fi

cd "$PROJECT_ROOT"
log_info "작업 디렉토리: $(pwd)"

# =====================================
# 1. Git Pull
# =====================================
log_info "최신 코드를 가져오는 중..."
git pull origin main || {
    log_error "Git pull 실패"
    exit 1
}
log_success "코드 업데이트 완료"

# =====================================
# 2. 환경 설정 확인
# =====================================
log_info "환경 설정 확인 중..."

if [ ! -f ".env.production" ]; then
    log_error ".env.production 파일이 없습니다."
    log_info "다음 명령으로 생성하세요: cp .env.example .env.production"
    exit 1
fi

# .env.production을 .env로 복사
cp .env.production .env
log_success "프로덕션 환경 설정 적용"

# =====================================
# 3. 백엔드 Docker 컨테이너 배포
# =====================================
log_info "백엔드 Docker 컨테이너 배포 중..."

# 기존 컨테이너 중지 및 제거
docker-compose -f docker/docker-compose.production.yml down 2>/dev/null || true

# 새 컨테이너 시작 (백엔드만)
docker-compose -f docker/docker-compose.production.yml up -d backend-php backend-nginx

# 컨테이너 시작 대기
log_info "컨테이너 시작 대기 중..."
sleep 5

# 컨테이너 상태 확인
if docker ps | grep -q "money_backend"; then
    log_success "백엔드 컨테이너 실행 중"
else
    log_error "백엔드 컨테이너 시작 실패"
    docker-compose -f docker/docker-compose.production.yml logs
    exit 1
fi

# =====================================
# 4. 프론트엔드 설정
# =====================================
log_info "프론트엔드 설정 중..."

# 프론트엔드는 프로젝트 디렉토리의 frontend 폴더 직접 사용
FRONTEND_TARGET="$PROJECT_ROOT/frontend"

# 권한 설정 (Nginx/PHP-FPM이 읽을 수 있도록)
log_info "파일 권한 설정 중..."

# 웹 서버 사용자 자동 감지 (CentOS/RHEL: nginx, Ubuntu/Debian: www-data)
if id "nginx" &>/dev/null; then
    WEB_USER="nginx"
    WEB_GROUP="nginx"
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
    WEB_GROUP="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
    WEB_GROUP="apache"
else
    log_error "웹 서버 사용자를 찾을 수 없습니다 (nginx, www-data, apache 중 하나 필요)"
    exit 1
fi

log_info "웹 서버 사용자: $WEB_USER:$WEB_GROUP"
sudo chown -R $WEB_USER:$WEB_GROUP "$FRONTEND_TARGET"
sudo chmod -R 755 "$FRONTEND_TARGET"

log_success "프론트엔드 설정 완료 (경로: $FRONTEND_TARGET)"

# =====================================
# 5. Nginx 설정 확인
# =====================================
log_info "Nginx 설정 확인 중..."

NGINX_CONF="/etc/nginx/conf.d/money.yeonghoon.kim.conf"

if [ ! -f "$NGINX_CONF" ]; then
    log_warning "Nginx 설정 파일이 없습니다."
    log_info "템플릿 파일을 생성합니다: nginx-production.conf"

    # 템플릿 파일을 Nginx 설정 위치로 복사
    if [ -f "nginx-production.conf" ]; then
        sudo cp nginx-production.conf "$NGINX_CONF"
        log_info "Nginx 설정 테스트 중..."
        sudo nginx -t
        log_info "Nginx 재시작 중..."
        sudo systemctl reload nginx
        log_success "Nginx 설정 완료"
    else
        log_error "nginx-production.conf 파일이 없습니다."
        exit 1
    fi
else
    log_info "기존 Nginx 설정 사용 중"
fi

# =====================================
# 6. 데이터베이스 마이그레이션 (선택적)
# =====================================
log_info "데이터베이스 확인 중..."

# all_dump.sql이 있으면 임포트 여부 확인
if [ -f "all_dump.sql" ]; then
    log_warning "all_dump.sql 파일이 발견되었습니다."
    read -p "데이터베이스를 임포트하시겠습니까? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log_info "데이터베이스 임포트 중..."

        # .env에서 DB 정보 읽기
        DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
        DB_USER=$(grep DB_USER .env | cut -d '=' -f2)
        DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)
        DB_NAME=$(grep DB_NAME .env | cut -d '=' -f2)

        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < all_dump.sql
        log_success "데이터베이스 임포트 완료"
    fi
fi

# =====================================
# 7. 배포 완료 및 상태 확인
# =====================================
echo ""
log_success "=========================================="
log_success "배포가 완료되었습니다!"
log_success "=========================================="
echo ""

log_info "서비스 상태:"
echo ""

# 백엔드 컨테이너 상태
log_info "백엔드 API 컨테이너:"
docker ps --filter "name=money_backend" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

# Nginx 상태
log_info "Nginx 상태:"
sudo systemctl status nginx --no-pager -l | grep -E "Active:|Loaded:"
echo ""

log_info "접속 정보:"
echo "  - 프론트엔드: https://money.yeonghoon.kim"
echo "  - 백엔드 API: http://localhost:8080/api"
echo ""

log_info "로그 확인:"
echo "  - 백엔드: docker-compose -f docker/docker-compose.production.yml logs -f"
echo "  - Nginx: sudo tail -f /var/log/nginx/error.log"
echo ""

log_warning "배포 후 확인 사항:"
echo "  1. 웹사이트 접속 테스트"
echo "  2. 로그인 기능 테스트"
echo "  3. API 응답 확인"
echo "  4. 브라우저 콘솔 에러 확인"