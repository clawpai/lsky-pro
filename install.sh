#!/bin/bash

export PATH=$PATH:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin:/opt/php/bin:/opt/homebrew/bin

# 定义颜色
GREEN='\033[1;32m'
YELLOW='\033[1;33m'
RED='\033[1;31m'
BLUE='\033[1;34m'
NC='\033[0m' # 无颜色

# 分割线函数
print_line() {
  echo -e "${BLUE}======================================================${NC}"
}

# 检查是否安装
print_line
echo -e "${BLUE}检查程序是否已经安装...${NC}"
if [ -f ".env" ] || [ -f "installed.lock" ]; then
  echo -e "${YELLOW}检测到程序已安装（存在 .env 或 installed.lock 文件）。${NC}"
  read -p "是否重新安装？这将删除现有配置文件并重新安装 (y/N): " confirm
  case $confirm in
    [yY][eE][sS]|[yY])
      echo -e "${YELLOW}正在删除现有配置文件...${NC}"
      rm -f .env installed.lock
      ;;
    *)
      echo -e "${GREEN}已取消重新安装。${NC}"
      exit 0
      ;;
  esac
else
  echo -e "${GREEN}程序未安装，将继续进行安装步骤。${NC}"
fi

# 检查是否已安装依赖
print_line
echo -e "${BLUE}检查项目依赖安装状态...${NC}"
if [ -d "vendor" ] && [ -f "composer.lock" ]; then
  echo -e "${GREEN}检测到依赖已正确安装，跳过 Composer 配置与安装。${NC}"
else
  echo -e "${YELLOW}依赖未正确安装，将开始安装依赖...${NC}"

  # 检查 composer.phar 是否存在
  if [ ! -f "composer.phar" ]; then
    echo -e "${YELLOW}未检测到 composer.phar，开始下载...${NC}"
    if wget https://github.com/composer/composer/releases/latest/download/composer.phar -O composer.phar; then
      echo -e "${GREEN}Composer 下载成功。${NC}"
    else
      echo -e "${RED}错误：Composer 下载失败，请检查网络设置。${NC}"
      exit 1
    fi
  fi

  # 配置 Composer 镜像
  print_line
  echo -e "${BLUE}配置 Composer 镜像：${NC}"
  echo "1) 官方默认镜像"
  echo "2) 阿里云镜像 (https://mirrors.aliyun.com/composer/)"
  echo "3) 华为云镜像 (https://mirrors.huaweicloud.com/repository/php/)"
  echo "4) 腾讯云镜像 (https://mirrors.cloud.tencent.com/composer/)"
  read -p "请选择镜像（1-4，默认 1）: " mirror_choice

  case $mirror_choice in
    2) mirror_url="https://mirrors.aliyun.com/composer/" ;;
    3) mirror_url="https://mirrors.huaweicloud.com/repository/php/" ;;
    4) mirror_url="https://mirrors.cloud.tencent.com/composer/" ;;
    *) mirror_url="https://repo.packagist.org" ;; # 默认官方镜像
  esac

  echo -e "${BLUE}正在配置 Composer 镜像为：${GREEN}${mirror_url}${NC}"
  if COMPOSER_ALLOW_SUPERUSER=1 php composer.phar config -g repos.packagist composer "$mirror_url"; then
    echo -e "${GREEN}Composer 镜像配置成功！${NC}"
  else
    echo -e "${RED}错误：配置 Composer 镜像失败，请检查。${NC}"
    exit 1
  fi

  # 安装依赖
  print_line
  echo -e "${BLUE}正在安装项目依赖...${NC}"
  if COMPOSER_ALLOW_SUPERUSER=1 php composer.phar install; then
    echo -e "${GREEN}项目依赖安装成功！${NC}"
  else
    echo -e "${RED}错误：依赖安装失败，请检查 composer.json 文件或日志信息。${NC}"
    exit 1
  fi
fi

# 执行 Artisan 安装命令
print_line
echo -e "${BLUE}运行 Artisan 安装命令...${NC}"
if php artisan app:install; then
  echo -e "${GREEN}Artisan 安装命令执行成功！${NC}"
else
  echo -e "${RED}错误：Artisan 安装命令执行失败，请检查环境配置或日志信息。${NC}"
  exit 1
fi

# 配置文件权限
print_line
echo -e "${BLUE}设置文件权限...${NC}"

# 自动检测web运行环境用户组
echo -e "${BLUE}正在自动检测Web服务器运行用户...${NC}"

# 初始化变量
WEB_USER=""
WEB_GROUP=""
DETECTED_ENV=""

# 检测系统类型
OS_TYPE=$(uname -s)
echo -e "${YELLOW}检测到系统类型: ${OS_TYPE}${NC}"

# 检测宝塔面板
BT_PANEL=false
if [ -d "/www/server" ] || [ -d "/www/wwwroot" ]; then
    echo -e "${YELLOW}检测到可能是宝塔面板环境${NC}"
    BT_PANEL=true
    
    # 检查宝塔nginx配置文件
    BT_NGINX_PATHS=(
        "/www/server/nginx/conf/nginx.conf"
        "/www/server/panel/vhost/nginx/default.conf"
    )
    
    for BT_PATH in "${BT_NGINX_PATHS[@]}"; do
        if [ -f "$BT_PATH" ]; then
            echo -e "${GREEN}找到宝塔Nginx配置文件: $BT_PATH${NC}"
            NGINX_CONF="$BT_PATH"
            NGINX_USER=$(grep -E "^[[:space:]]*user[[:space:]]+" "$NGINX_CONF" | awk '{print $2}' | sed 's/;//')
            if [ ! -z "$NGINX_USER" ]; then
                WEB_USER="$NGINX_USER"
                # 检查是否在user指令中指定了组
                NGINX_GROUP=$(grep -E "^[[:space:]]*user[[:space:]]+" "$NGINX_CONF" | awk '{print $3}' | sed 's/;//')
                if [ ! -z "$NGINX_GROUP" ]; then
                    WEB_GROUP="$NGINX_GROUP"
                else
                    WEB_GROUP="$WEB_USER"
                fi
                DETECTED_ENV="Nginx(宝塔)"
                break
            fi
        fi
    done
fi

# 如果宝塔检测未找到用户，继续常规检测
if [ -z "$WEB_USER" ]; then
    # 检测常见Web服务器进程
    if ps aux | grep -E "nginx|apache2|httpd|apache|caddy" | grep -v grep > /dev/null; then
        echo -e "${GREEN}检测到Web服务器进程正在运行${NC}"
        
        # 检测Nginx
        if ps aux | grep "nginx" | grep -v grep > /dev/null; then
            DETECTED_ENV="Nginx"
            # 获取Nginx运行用户
            if [ "$OS_TYPE" = "Darwin" ]; then
                # macOS下尝试从配置获取
                if [ -f "/usr/local/etc/nginx/nginx.conf" ]; then
                    NGINX_USER=$(grep "user" /usr/local/etc/nginx/nginx.conf | awk '{print $2}' | sed 's/;//')
                    if [ ! -z "$NGINX_USER" ]; then
                        WEB_USER="$NGINX_USER"
                    else
                        WEB_USER="_www"  # macOS默认值
                    fi
                    WEB_GROUP="${WEB_USER}"
                elif [ -f "/opt/homebrew/etc/nginx/nginx.conf" ]; then
                    NGINX_USER=$(grep "user" /opt/homebrew/etc/nginx/nginx.conf | awk '{print $2}' | sed 's/;//')
                    if [ ! -z "$NGINX_USER" ]; then
                        WEB_USER="$NGINX_USER"
                    else
                        WEB_USER="_www"  # macOS默认值
                    fi
                    WEB_GROUP="${WEB_USER}"
                else
                    WEB_USER="_www"  # macOS默认值
                    WEB_GROUP="_www"
                fi
            else
                # Linux下尝试从配置和进程获取
                NGINX_CONF_PATHS=(
                    "/etc/nginx/nginx.conf"
                    "/usr/local/nginx/conf/nginx.conf"
                    "/www/server/nginx/conf/nginx.conf"  # 宝塔路径
                )
                
                for CONF_PATH in "${NGINX_CONF_PATHS[@]}"; do
                    if [ -f "$CONF_PATH" ]; then
                        NGINX_CONF="$CONF_PATH"
                        break
                    fi
                done
                
                if [ ! -z "$NGINX_CONF" ]; then
                    NGINX_USER=$(grep -E "^[[:space:]]*user[[:space:]]+" "$NGINX_CONF" | awk '{print $2}' | sed 's/;//')
                    if [ ! -z "$NGINX_USER" ]; then
                        WEB_USER="$NGINX_USER"
                        # 检查是否在user指令中指定了组
                        NGINX_GROUP=$(grep -E "^[[:space:]]*user[[:space:]]+" "$NGINX_CONF" | awk '{print $3}' | sed 's/;//')
                        if [ ! -z "$NGINX_GROUP" ]; then
                            WEB_GROUP="$NGINX_GROUP"
                        else
                            WEB_GROUP="$WEB_USER"
                        fi
                    fi
                fi
                
                # 如果从配置未获取到用户，则尝试从进程获取
                if [ -z "$WEB_USER" ]; then
                    NGINX_PROCESSES=$(ps aux | grep "nginx" | grep -v grep | grep -v "root")
                    if [ ! -z "$NGINX_PROCESSES" ]; then
                        WEB_USER=$(echo "$NGINX_PROCESSES" | head -n 1 | awk '{print $1}')
                        WEB_GROUP="$WEB_USER"
                    fi
                fi
            fi
        fi
        
        # 检测Apache
        if [ -z "$WEB_USER" ] && (ps aux | grep -E "apache2|httpd" | grep -v grep > /dev/null); then
            DETECTED_ENV="Apache"
            if [ "$OS_TYPE" = "Darwin" ]; then
                WEB_USER="_www"
                WEB_GROUP="_www"
            else
                # 检查Apache配置
                if [ -f "/etc/apache2/envvars" ]; then
                    . /etc/apache2/envvars
                    WEB_USER="$APACHE_RUN_USER"
                    WEB_GROUP="$APACHE_RUN_GROUP"
                elif [ -f "/etc/httpd/conf/httpd.conf" ]; then
                    APACHE_USER=$(grep "^User" /etc/httpd/conf/httpd.conf | awk '{print $2}')
                    APACHE_GROUP=$(grep "^Group" /etc/httpd/conf/httpd.conf | awk '{print $2}')
                    if [ ! -z "$APACHE_USER" ]; then
                        WEB_USER="$APACHE_USER"
                        WEB_GROUP="$APACHE_GROUP"
                    fi
                # 宝塔Apache路径
                elif [ -f "/www/server/apache/conf/httpd.conf" ]; then
                    APACHE_USER=$(grep "^User" /www/server/apache/conf/httpd.conf | awk '{print $2}')
                    APACHE_GROUP=$(grep "^Group" /www/server/apache/conf/httpd.conf | awk '{print $2}')
                    if [ ! -z "$APACHE_USER" ]; then
                        WEB_USER="$APACHE_USER"
                        WEB_GROUP="$APACHE_GROUP"
                    fi
                else
                    # 从进程获取
                    APACHE_PROCESSES=$(ps aux | grep -E "apache2|httpd" | grep -v grep | grep -v "root")
                    if [ ! -z "$APACHE_PROCESSES" ]; then
                        WEB_USER=$(echo "$APACHE_PROCESSES" | head -n 1 | awk '{print $1}')
                        WEB_GROUP="$WEB_USER"
                    fi
                fi
            fi
        fi
        
        # 检测Caddy
        if [ -z "$WEB_USER" ] && (ps aux | grep "caddy" | grep -v grep > /dev/null); then
            DETECTED_ENV="Caddy"
            if [ "$OS_TYPE" = "Darwin" ]; then
                WEB_USER="_www"
                WEB_GROUP="_www"
            else
                # Caddy通常使用www-data
                WEB_USER="www-data"
                WEB_GROUP="www-data"
                
                # 从进程获取
                CADDY_PROCESSES=$(ps aux | grep "caddy" | grep -v grep | grep -v "root")
                if [ ! -z "$CADDY_PROCESSES" ]; then
                    WEB_USER=$(echo "$CADDY_PROCESSES" | head -n 1 | awk '{print $1}')
                    WEB_GROUP="$WEB_USER"
                fi
            fi
        fi
    fi

    # 检测PHP-FPM
    if [ -z "$WEB_USER" ] && (ps aux | grep "php-fpm" | grep -v grep > /dev/null); then
        DETECTED_ENV="${DETECTED_ENV:-PHP-FPM}"
        # 从PHP-FPM配置获取
        PHP_FPM_CONFS_PATHS=(
            "/etc"
            "/usr/local/etc"
            "/opt/homebrew/etc"
            "/www/server/php/*/etc"  # 宝塔PHP路径
        )
        
        for PATH_BASE in "${PHP_FPM_CONFS_PATHS[@]}"; do
            # 使用通配符展开路径
            for EXPANDED_PATH in $PATH_BASE; do
                if [ -d "$EXPANDED_PATH" ]; then
                    PHP_FPM_CONFS=$(find "$EXPANDED_PATH" -name "www.conf" -o -name "php-fpm.conf" 2>/dev/null)
                    
                    if [ ! -z "$PHP_FPM_CONFS" ]; then
                        for CONF in $PHP_FPM_CONFS; do
                            echo -e "${GREEN}检查PHP-FPM配置: $CONF${NC}"
                            FPM_USER=$(grep "^user\s*=" "$CONF" 2>/dev/null | head -n 1 | cut -d= -f2 | tr -d ' ')
                            FPM_GROUP=$(grep "^group\s*=" "$CONF" 2>/dev/null | head -n 1 | cut -d= -f2 | tr -d ' ')
                            
                            if [ ! -z "$FPM_USER" ]; then
                                WEB_USER="$FPM_USER"
                                if [ ! -z "$FPM_GROUP" ]; then
                                    WEB_GROUP="$FPM_GROUP"
                                else
                                    WEB_GROUP="$FPM_USER"
                                fi
                                DETECTED_ENV="PHP-FPM"
                                break 3  # 跳出所有循环
                            fi
                        done
                    fi
                fi
            done
        done
        
        # 如果从配置未获取到，则从进程获取
        if [ -z "$WEB_USER" ]; then
            FPM_PROCESSES=$(ps aux | grep "php-fpm" | grep -v grep | grep -v "root" | head -1)
            if [ ! -z "$FPM_PROCESSES" ]; then
                WEB_USER=$(echo "$FPM_PROCESSES" | awk '{print $1}')
                WEB_GROUP="$WEB_USER"
                echo -e "${GREEN}从PHP-FPM进程获取到用户: $WEB_USER${NC}"
            fi
        fi
    fi
fi

# 设置默认值
if [ -z "$WEB_USER" ]; then
    # 根据不同系统设置默认值
    if [ "$OS_TYPE" = "Darwin" ]; then
        WEB_USER="_www"
        WEB_GROUP="_www"
    elif [ -f "/etc/debian_version" ]; then
        WEB_USER="www-data"
        WEB_GROUP="www-data"
    elif [ -f "/etc/redhat-release" ] || [ -f "/etc/centos-release" ]; then
        WEB_USER="apache"
        WEB_GROUP="apache"
    elif [ "$BT_PANEL" = true ]; then
        # 宝塔面板通常使用www用户
        WEB_USER="www"
        WEB_GROUP="www"
    else
        WEB_USER="www-data"  # 通用默认值
        WEB_GROUP="www-data"
    fi
    echo -e "${YELLOW}未检测到Web服务器运行用户，将使用默认值: ${WEB_USER}:${WEB_GROUP}${NC}"
else
    echo -e "${GREEN}检测到Web环境: ${DETECTED_ENV:-未知}${NC}"
    echo -e "${GREEN}检测到Web运行用户/组: ${WEB_USER}:${WEB_GROUP}${NC}"
fi

# 提供修改选项
read -p "是否使用检测到的Web运行用户 ${WEB_USER}:${WEB_GROUP}？(Y/n): " confirm
if [[ "$confirm" =~ ^[Nn] ]]; then
    read -p "请输入Web运行用户: " custom_user
    read -p "请输入Web运行用户组: " custom_group
    
    if [ ! -z "$custom_user" ]; then
        WEB_USER="$custom_user"
    fi
    
    if [ ! -z "$custom_group" ]; then
        WEB_GROUP="$custom_group"
    fi
fi

# 验证用户和组是否存在
echo -e "${BLUE}验证用户和组是否存在...${NC}"
USER_EXISTS=false
GROUP_EXISTS=false

if id "$WEB_USER" &>/dev/null; then
    USER_EXISTS=true
    echo -e "${GREEN}用户 $WEB_USER 存在${NC}"
else
    echo -e "${RED}警告：用户 $WEB_USER 不存在${NC}"
fi

if grep -q "^$WEB_GROUP:" /etc/group 2>/dev/null; then
    GROUP_EXISTS=true
    echo -e "${GREEN}用户组 $WEB_GROUP 存在${NC}"
elif [ "$OS_TYPE" = "Darwin" ] && dscl . -read /Groups/"$WEB_GROUP" &>/dev/null; then
    GROUP_EXISTS=true
    echo -e "${GREEN}用户组 $WEB_GROUP 存在${NC}"
else
    echo -e "${RED}警告：用户组 $WEB_GROUP 不存在${NC}"
fi

if [ "$USER_EXISTS" = false ] || [ "$GROUP_EXISTS" = false ]; then
    echo -e "${YELLOW}用户或用户组不存在，是否继续？(y/N): ${NC}"
    read -p "" continue_confirm
    if [[ ! "$continue_confirm" =~ ^[Yy] ]]; then
        echo -e "${RED}安装已取消${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}将使用当前用户进行权限设置${NC}"
    WEB_USER=$(whoami)
    WEB_GROUP=$(id -gn)
fi

# 设置文件权限
echo -e "${GREEN}正在设置文件权限为 ${WEB_USER}:${WEB_GROUP}...${NC}"
if chown -R "$WEB_USER":"$WEB_GROUP" $(pwd); then
    echo -e "${GREEN}所有者设置成功！${NC}"
else
    echo -e "${RED}设置所有者失败，尝试使用sudo...${NC}"
    if sudo chown -R "$WEB_USER":"$WEB_GROUP" $(pwd); then
        echo -e "${GREEN}使用sudo设置所有者成功！${NC}"
    else
        echo -e "${RED}警告：无法设置文件所有者，请手动设置文件权限${NC}"
    fi
fi

# 设置目录权限
echo -e "${GREEN}正在设置目录权限...${NC}"
if chmod -R 0755 storage bootstrap/cache; then
    echo -e "${GREEN}目录权限设置成功！${NC}"
else
    echo -e "${RED}设置目录权限失败，尝试使用sudo...${NC}"
    if sudo chmod -R 0755 storage bootstrap/cache; then
        echo -e "${GREEN}使用sudo设置目录权限成功！${NC}"
    else
        echo -e "${RED}警告：无法设置目录权限，请手动设置目录权限${NC}"
    fi
fi

# 设置写入权限
echo -e "${GREEN}正在设置写入权限...${NC}"
if chmod -R ug+w storage bootstrap/cache; then
    echo -e "${GREEN}写入权限设置成功！${NC}"
else
    echo -e "${RED}设置写入权限失败，尝试使用sudo...${NC}"
    if sudo chmod -R ug+w storage bootstrap/cache; then
        echo -e "${GREEN}使用sudo设置写入权限成功！${NC}"
    else
        echo -e "${RED}警告：无法设置写入权限，请手动设置写入权限${NC}"
    fi
fi

echo -e "${GREEN}权限设置完成！${NC}"

# 安装完成提示
print_line
echo -e "${GREEN}程序安装完成！${NC}"