# Frontend Dockerfile - PHP + Nginx for frontend files
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/cache/apk/*

# Create directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/html \
    && mkdir -p /run/nginx

# Copy nginx config for frontend
COPY docker/nginx-frontend.conf /etc/nginx/http.d/default.conf

# Copy supervisor config
COPY docker/supervisor-frontend.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 80

# Start supervisor (manages nginx + php-fpm)
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]