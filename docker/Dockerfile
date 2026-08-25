FROM php:8.4-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Shanghai

RUN apt-get update && apt-get install -y \
    wget \
    gnupg \
    software-properties-common \
    curl \
    zip \
    unzip \
    git \
    cron \
    supervisor \
    nginx \
    sqlite3 \
    netcat-openbsd \
    procps \
    htop \
    build-essential \
    pkg-config \
    autoconf \
    automake \
    libtool \
    imagemagick \
    libmagickwand-dev \
    libmagickcore-dev \
    libvips-dev \
    libvips-tools \
    libvips42 \
    libpng-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libavif-dev \
    libtiff-dev \
    libgif-dev \
    libheif-dev \
    librsvg2-dev \
    libraw-dev \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libxslt1-dev \
    libffi-dev \
    libsqlite3-dev \
    fonts-dejavu-core \
    fontconfig \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_sqlite \
        mysqli \
        gd \
        zip \
        bcmath \
        opcache \
        exif \
        intl \
        xsl \
        pcntl \
        ftp \
        ffi

RUN pecl install imagick redis vips \
    && docker-php-ext-enable imagick redis vips

COPY docker/config/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/config/nginx.conf /etc/nginx/sites-available/default
COPY docker/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 删除默认的 Docker 配置文件，避免配置冲突
RUN rm -f /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/database

EXPOSE 80

# 添加健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

ENTRYPOINT ["/entrypoint.sh"] 