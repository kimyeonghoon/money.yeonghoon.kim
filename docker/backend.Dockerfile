FROM php:8.2-fpm-alpine

# 필요한 PHP 확장 설치
RUN apk add --no-cache \
    mysql-client \
    curl \
    zip \
    unzip \
    git

# PHP 확장 설치
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

# OpenSSL 확장 (이미 포함되어 있음)
RUN apk add --no-cache openssl-dev

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /var/www/html

# PHP-FPM 설정
RUN echo "php_admin_value[error_log] = /var/log/app/php_error.log" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

# PHP 설정
RUN echo "date.timezone = Asia/Seoul" > /usr/local/etc/php/conf.d/timezone.ini
RUN echo "display_errors = On" > /usr/local/etc/php/conf.d/errors.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/errors.ini

# 로그 디렉토리 생성
RUN mkdir -p /var/log/app && chown www-data:www-data /var/log/app

# 파일 권한 설정
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]