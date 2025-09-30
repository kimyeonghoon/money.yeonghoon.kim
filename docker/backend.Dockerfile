# =====================================
# 백엔드 API 서버 Dockerfile - 머니매니저 시스템
# =====================================
#
# PHP 8.2 FPM 기반의 백엔드 API 서버 컨테이너를 구성합니다.
# 경량화된 Alpine Linux를 기반으로 하여 성능과 보안을 최적화했습니다.
#
# 주요 구성요소:
# - PHP 8.2-FPM (FastCGI Process Manager)
# - MySQL 연결 확장 (PDO, MySQLi)
# - Composer (PHP 패키지 관리자)
# - 개발 도구 (curl, git 등)
#
# 용도:
# - RESTful API 엔드포인트 제공
# - 데이터베이스 연결 및 쿼리 처리
# - 비즈니스 로직 실행
# - 세션 및 인증 관리
#
# 보안 고려사항:
# - 최소 권한 원칙 (www-data 사용자)
# - 에러 로깅 활성화
# - 안전한 파일 권한 설정
#
# @base php:8.2-fpm-alpine
# @author YeongHoon Kim

# Alpine Linux 기반 PHP 8.2 FPM 이미지 사용
# 장점: 경량화(5MB), 보안성, 빠른 시작 시간
FROM php:8.2-fpm-alpine

# =====================================
# 시스템 패키지 설치
# =====================================
# 개발 도구 및 MySQL 클라이언트 설치
# Alpine 패키지 관리자(apk) 사용으로 빠른 설치와 작은 이미지 크기 확보
# - mysql-client: MySQL 명령줄 도구 (디버깅용)
# - curl: HTTP 클라이언트 (헬스체크, API 테스트)
# - zip/unzip: 압축 파일 처리 (Composer 의존성)
# - git: 버전 관리 (Composer 소스 코드 다운로드)
RUN apk add --no-cache \
    mysql-client \
    curl \
    zip \
    unzip \
    git

# =====================================
# PHP 확장 모듈 설치
# =====================================
# 데이터베이스 연결을 위한 필수 PHP 확장 설치
# - pdo: PHP Data Objects (추상 데이터베이스 레이어)
# - pdo_mysql: MySQL용 PDO 드라이버 (Prepared Statement 지원)
# - mysqli: MySQL Improved 확장 (레거시 호환성)
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

# =====================================
# 보안 및 암호화 지원
# =====================================
# OpenSSL 개발 헤더 설치 (HTTPS, 암호화 기능 지원)
RUN apk add --no-cache openssl-dev

# =====================================
# Composer 설치 (PHP 패키지 관리자)
# =====================================
# 멀티 스테이지 빌드로 Composer 바이너리만 복사
# 장점: 이미지 크기 최적화, 최신 Composer 보장
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# =====================================
# 작업 환경 설정
# =====================================
# 애플리케이션 루트 디렉토리 설정
WORKDIR /var/www/html

# =====================================
# PHP-FPM 프로세스 관리자 설정
# =====================================
# 에러 로깅 활성화 (디버깅 및 모니터링)
RUN echo "php_admin_value[error_log] = /var/log/app/php_error.log" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

# =====================================
# PHP 런타임 설정
# =====================================
# 시간대 설정 (한국 표준시)
RUN echo "date.timezone = Asia/Seoul" > /usr/local/etc/php/conf.d/timezone.ini

# 개발 환경용 에러 표시 설정
RUN echo "display_errors = On" > /usr/local/etc/php/conf.d/errors.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/errors.ini

# =====================================
# 파일 시스템 및 권한 설정
# =====================================
# 로그 디렉토리 생성 및 권한 설정
RUN mkdir -p /var/log/app && chown www-data:www-data /var/log/app

# 애플리케이션 파일 권한 설정 (보안 강화)
# www-data: 웹 서버 전용 사용자 (최소 권한 원칙)
RUN chown -R www-data:www-data /var/www/html

# =====================================
# 네트워크 설정
# =====================================
# PHP-FPM 포트 노출 (Nginx와 FastCGI 통신용)
EXPOSE 9000

# =====================================
# 컨테이너 시작 명령
# =====================================
# PHP-FPM 프로세스 관리자 실행
# 특징: 멀티프로세싱, 자동 재시작, 메모리 관리
CMD ["php-fpm"]