# Lsky Pro 2.2.3 Dockerfile（php:8.4-fpm + nginx + supervisor）
# 扩展 = 官方 docker/Dockerfile 全量（保证与官方行为一致，无缺扩展风险）
# 优化点：
#   1. 源码内置 /var/www/lsky，挂载 /var/www/html 兼容 halcyonazure 标准（空目录自动拷贝）
#   2. APP_LICENSE_KEY 不再必填（本地授权，直接 true）
#   3. .dockerignore 剔除源码包冗余（测试/IDE/前端源码/日志），镜像大小可控
#   4. 构建全离线（vendor 已随源码包含，无需 composer 联网）

FROM php:8.4-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Shanghai

# ---- 系统依赖（官方全量：服务 + 图片库 + 编译工具链）----
RUN apt-get update && apt-get install -y \
    wget gnupg curl zip unzip git cron \
    supervisor nginx sqlite3 netcat-openbsd procps htop \
    build-essential pkg-config autoconf automake libtool \
    imagemagick libmagickwand-dev libmagickcore-dev \
    libvips-dev libvips-tools libvips42 \
    libpng-dev libjpeg-dev libjpeg62-turbo-dev libfreetype6-dev \
    libwebp-dev libavif-dev libtiff-dev libgif-dev libheif-dev \
    librsvg2-dev libraw-dev libzip-dev zlib1g-dev libonig-dev \
    libxml2-dev libicu-dev libxslt1-dev libffi-dev libsqlite3-dev \
    fonts-dejavu-core fontconfig \
    && rm -rf /var/lib/apt/lists/*

# ---- PHP 扩展（官方全量：数据库/GD/ZIP/BCMath/OPcache/EXIF/Intl/XSL/PCNTL/FTP/FFI + Imagick/Redis/Vips）----
RUN docker-php-ext-configure gd \
        --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql pdo_sqlite mysqli gd zip bcmath opcache exif \
        intl xsl pcntl ftp ffi \
    && pecl install imagick redis vips \
    && docker-php-ext-enable imagick redis vips

# ---- 配置：php.ini / fpm / nginx / supervisor（沿用官方 docker/config）----
COPY docker/config/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/config/nginx.conf /etc/nginx/sites-available/default
COPY docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 删除默认 Docker fpm 配置避免冲突
RUN rm -f /usr/local/etc/php-fpm.d/zz-docker.conf

# ---- 源码内置到 /var/www/lsky（拷贝源），挂载 /var/www/html 为用户数据目录 ----
WORKDIR /var/www/lsky
COPY . .

RUN chown -R www-data:www-data /var/www/lsky \
    && chmod -R 755 /var/www/lsky/storage \
    && chmod -R 755 /var/www/lsky/bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/lsky/database \
    && chown -R www-data:www-data /var/www/lsky/database

EXPOSE 80

# 健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

ENTRYPOINT ["/entrypoint.sh"]