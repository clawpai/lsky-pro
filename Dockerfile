# 优化后的 Lsky Pro 2.2.3 Dockerfile
# 基于官方 docker/Dockerfile 方案（php:8.4-fpm + nginx + supervisor）
# 优化点：
#   1. 源码内置 /var/www/lsky，挂载 /var/www/html 兼容 halcyonazure 标准（目录空则自动拷贝）
#   2. APP_LICENSE_KEY 不再必填（本地授权，直接 true）
#   3. .dockerignore 剔除源码包冗余（测试/IDE/构建缓存），镜像更小
#   4. 构建全离线（vendor 已随源码包含，无需 composer 联网）

FROM php:8.4-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Shanghai

# ---- 系统依赖：nginx/supervisor/cron + PHP 编译依赖 + 图片处理库 ----
RUN apt-get update && apt-get install -y \
    wget gnupg software-properties-common curl zip unzip git cron \
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

# ---- PHP 扩展（与官方一致）----
RUN docker-php-ext-configure gd \
        --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql pdo_sqlite mysqli gd zip bcmath opcache exif \
        intl xsl pcntl ftp ffi \
    && pecl install imagick redis vips \
    && docker-php-ext-enable imagick redis vips

# ---- 配置：php.ini / fpm / nginx / supervisor ----
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