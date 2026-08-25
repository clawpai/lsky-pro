#!/bin/bash
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ============================================================
# Lsky Pro 2.2.3 优化版入口
# 兼容 halcyonazure/lsky-pro-docker 挂载标准：
#   -v $PWD/lsky:/var/www/html   （整个程序目录挂载）
# 首次启动时若挂载目录为空，自动从内置 /var/www/lsky 拷贝程序。
# ============================================================

mkdir -p /run/php /var/log

ln -snf /usr/share/zoneinfo/${TZ:-Asia/Shanghai} /etc/localtime && echo ${TZ:-Asia/Shanghai} > /etc/timezone

cd /var/www/html

# ---- 挂载目录初始化：空目录自动拷贝内置源码 ----
if [ ! -f "/var/www/html/public/index.php" ]; then
    echo -e "${YELLOW}挂载目录为空或无程序，从内置 /var/www/lsky 拷贝源码...${NC}"
    cp -a /var/www/lsky/. /var/www/html/
    chown -R www-data:www-data /var/www/html
    echo -e "${GREEN}✓ 源码拷贝完成${NC}"
fi

# ---- 数据库等待 ----
if [ "${DB_CONNECTION:-sqlite}" != "sqlite" ]; then
    echo -e "${YELLOW}等待数据库服务启动...${NC}"
    until nc -z "${DB_HOST:-127.0.0.1}" "${DB_PORT:-3306}"; do
        echo -e "${YELLOW}等待数据库连接 ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}...${NC}"
        sleep 2
    done
    echo -e "${GREEN}✓ 数据库连接成功${NC}"
fi

# ---- 自动安装（仅当未安装）----
if [ ! -f "/var/www/html/installed.lock" ]; then
    echo -e "${YELLOW}开始安装应用...${NC}"

    if [ -z "${APP_URL}" ]; then
        echo -e "${RED}错误: 必须提供 APP_URL 环境变量${NC}"
        echo -e "${RED}请在启动容器时设置: -e APP_URL=https://your-domain${NC}"
        exit 1
    fi

    # 开心版：许可证密钥可选，任意值均可通过（verifyLicense 直接 true）
    APP_LICENSE_KEY="${APP_LICENSE_KEY:-local-free}"

    # 首次部署请通过 ADMIN_PASSWORD 环境变量设置强密码（默认 admin123）

    php artisan app:install \
        --app-name="${APP_NAME:-兰空图床}" \
        --app-url="${APP_URL}" \
        --app-license-key="${APP_LICENSE_KEY}" \
        --db-connection="${DB_CONNECTION:-sqlite}" \
        --db-host="${DB_HOST:-127.0.0.1}" \
        --db-port="${DB_PORT:-3306}" \
        --db-database="${DB_DATABASE:-/var/www/html/database/database.sqlite}" \
        --db-username="${DB_USERNAME:-}" \
        --db-password="${DB_PASSWORD:-}" \
        --admin-username="${ADMIN_USERNAME:-admin}" \
        --admin-email="${ADMIN_EMAIL:-admin@example.com}" \
        --admin-password="${ADMIN_PASSWORD:-admin123}" \
        --force

    if [ -f "/var/www/html/installed.lock" ]; then
        echo -e "${GREEN}✓ 应用安装成功${NC}"
    else
        echo -e "${RED}✗ 应用安装失败${NC}"
        exit 1
    fi
fi

# ---- 重新生成 storage 符号链接（每次启动兜底）----
php artisan storage:link --force --relative 2>/dev/null || true
php artisan symlinks:rebuild 2>/dev/null || true

# ---- 清理缓存并预热 ----
php artisan optimize:clear >/dev/null 2>&1 || true

echo -e "${YELLOW}正在初始化...${NC}"

# 设置目录所有者和权限
chown -R www-data:www-data /var/www/html
chown -R www-data:www-data /run/php
chmod -R 775 /var/www/html
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/database
chmod -R 755 /run/php

echo -e "${GREEN}=== 应用初始化完成，启动服务... ===${NC}"
echo -e "${BLUE}数据库类型: ${DB_CONNECTION:-sqlite}${NC}"
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    echo -e "${BLUE}SQLite数据库: ${DB_DATABASE:-/var/www/html/database/database.sqlite}${NC}"
else
    echo -e "${BLUE}数据库地址: ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}${NC}"
    echo -e "${BLUE}数据库名称: ${DB_DATABASE}${NC}"
fi
echo -e "${BLUE}应用地址: ${APP_URL:-http://localhost}${NC}"

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf