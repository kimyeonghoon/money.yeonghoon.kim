# 실서버 배포 가이드

## 📋 사전 준비사항

### 1. 서버 환경
- ✅ Ubuntu 20.04 LTS 이상
- ✅ Nginx 설치됨 (80/443 포트)
- ✅ Docker 및 Docker Compose 설치됨
- ✅ PHP 8.2-FPM 설치됨 (프론트엔드용)
- ✅ 외부 MySQL 서버 접근 가능
- ✅ SSL 인증서 (Let's Encrypt 권장)

### 2. 확인 명령어
```bash
# Docker 설치 확인
docker --version
docker-compose --version

# Nginx 설치 확인
nginx -v

# PHP-FPM 설치 확인
php-fpm8.2 -v

# MySQL 연결 확인
mysql -h <DB_HOST> -u <DB_USER> -p
```

## 🚀 배포 절차

### Step 1: 코드 가져오기
```bash
# 프로젝트 디렉토리로 이동
cd /data

# Git 저장소 클론 (최초 1회)
git clone https://github.com/kimyeonghoon/money.yeonghoon.kim.git
cd money.yeonghoon.kim

# 또는 기존 저장소 업데이트
cd /data/money.yeonghoon.kim
git pull origin main
```

### Step 2: 환경 설정
```bash
# 프로덕션 환경변수 파일 생성
cp .env.production.example .env.production

# 환경변수 편집
nano .env.production
```

**필수 설정 항목:**
```bash
# 외부 DB 정보
PROD_DB_HOST=your_mysql_host
PROD_DB_USER=money_user
PROD_DB_PASSWORD=your_secure_password
PROD_DB_NAME=money_management

# 암호화 키 생성
openssl rand -hex 32

# 비밀번호 해시 생성
php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"

# 텔레그램 봇 토큰 및 Chat ID
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
```

### Step 3: 데이터베이스 초기화
```bash
# MySQL 서버에 접속
mysql -h <PROD_DB_HOST> -u <PROD_DB_USER> -p

# 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS money_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 사용자 권한 부여
GRANT ALL PRIVILEGES ON money_management.* TO 'money_user'@'%';
FLUSH PRIVILEGES;
EXIT;

# 스키마 및 데이터 임포트
mysql -h <PROD_DB_HOST> -u <PROD_DB_USER> -p money_management < all_dump.sql
```

### Step 4: Nginx 설정
```bash
# Nginx 설정 파일 복사
sudo cp nginx-production.conf /etc/nginx/conf.d/money.yeonghoon.kim.conf

# SSL 인증서 발급 (Let's Encrypt)
sudo certbot --nginx -d money.yeonghoon.kim

# 설정 파일 편집 (필요시)
sudo nano /etc/nginx/conf.d/money.yeonghoon.kim.conf

# 주요 수정 사항:
# - SSL 인증서 경로
# - PHP-FPM 소켓 경로 확인
# - 프론트엔드 루트 디렉토리 확인

# 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl reload nginx
```

### Step 5: 배포 스크립트 실행
```bash
# 실행 권한 부여
chmod +x deploy-production.sh

# 배포 실행
./deploy-production.sh
```

**배포 스크립트가 수행하는 작업:**
1. ✅ Git pull (최신 코드)
2. ✅ 환경변수 복사 (.env.production → .env)
3. ✅ 백엔드 Docker 컨테이너 시작
4. ✅ 프론트엔드 파일 배포 (/var/www/money.yeonghoon.kim)
5. ✅ Nginx 설정 확인
6. ✅ 서비스 상태 확인

## 🔍 배포 후 확인

### 1. 서비스 상태 확인
```bash
# 백엔드 컨테이너 확인
docker ps --filter "name=money_backend"

# Nginx 상태
sudo systemctl status nginx

# PHP-FPM 상태
sudo systemctl status php8.2-fpm
```

### 2. 웹사이트 접속 테스트
```bash
# 프론트엔드 접속
curl -I https://money.yeonghoon.kim

# 백엔드 API 테스트
curl https://money.yeonghoon.kim/api/health
```

### 3. 로그 확인
```bash
# 백엔드 로그
docker-compose -f docker/docker-compose.production.yml logs -f backend-php

# Nginx 에러 로그
sudo tail -f /var/log/nginx/money.yeonghoon.kim-error.log

# PHP-FPM 로그
sudo tail -f /var/log/php8.2-fpm.log
```

## 🔧 트러블슈팅

### 문제 1: 백엔드 API 연결 실패 (502 Bad Gateway)
```bash
# 원인: Docker 컨테이너 미실행 또는 포트 충돌
# 해결:
docker ps  # 컨테이너 실행 확인
docker-compose -f docker/docker-compose.production.yml logs backend-nginx
netstat -tulpn | grep 8080  # 포트 8080 사용 확인
```

### 문제 2: 프론트엔드 PHP 파일 실행 안 됨
```bash
# 원인: PHP-FPM 소켓 경로 불일치
# 해결:
ls -la /var/run/php/  # 소켓 파일 확인
sudo nano /etc/nginx/sites-available/money.yeonghoon.kim
# fastcgi_pass 경로를 실제 소켓 경로로 수정
sudo systemctl restart php8.2-fpm
sudo nginx -t && sudo systemctl reload nginx
```

### 문제 3: 데이터베이스 연결 실패
```bash
# 원인: 외부 DB 접근 권한 또는 방화벽
# 해결:
# 1. MySQL 서버에서 원격 접속 허용 확인
# 2. 방화벽 규칙 확인
# 3. .env 파일의 DB 정보 확인
docker exec -it money_backend_php php -r "var_dump(getenv('PROD_DB_HOST'));"
```

### 문제 4: SSL 인증서 오류
```bash
# 원인: 인증서 만료 또는 경로 오류
# 해결:
sudo certbot renew  # 인증서 갱신
sudo nginx -t  # 설정 파일 경로 확인
```

## 🔄 업데이트 및 롤백

### 일반 업데이트
```bash
cd /data/money.yeonghoon.kim
git pull origin main
./deploy-production.sh
```

### 긴급 롤백
```bash
# 이전 커밋으로 되돌리기
git log --oneline -5  # 최근 커밋 확인
git reset --hard <commit-hash>
./deploy-production.sh
```

### 백엔드만 재시작
```bash
docker-compose -f docker/docker-compose.production.yml restart backend-php backend-nginx
```

### 프론트엔드만 업데이트
```bash
sudo rsync -av --delete frontend/ /var/www/money.yeonghoon.kim/
sudo chown -R www-data:www-data /var/www/money.yeonghoon.kim
```

## 📊 모니터링

### 로그 모니터링
```bash
# 실시간 로그 확인
docker-compose -f docker/docker-compose.production.yml logs -f --tail=100

# Nginx 액세스 로그
sudo tail -f /var/log/nginx/money.yeonghoon.kim-access.log

# 애플리케이션 로그
tail -f logs/app.log
```

### 리소스 사용량
```bash
# Docker 컨테이너 리소스
docker stats money_backend_php money_backend_nginx

# 서버 전체 리소스
htop
df -h
```

## 🔐 보안 체크리스트

- [ ] SSL 인증서 설정 완료
- [ ] .env 파일 권한 (chmod 600)
- [ ] 강력한 비밀번호 사용
- [ ] 방화벽 규칙 설정 (필요한 포트만 오픈)
- [ ] 정기 백업 설정
- [ ] 로그 모니터링 설정
- [ ] 보안 업데이트 자동화

## 📞 지원

문제 발생 시:
1. 로그 파일 확인
2. GitHub Issues에 문의
3. 상세한 에러 메시지 첨부