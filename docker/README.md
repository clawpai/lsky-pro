# 兰空图床 Docker 部署

## 快速开始

## 构建镜像

### 基础构建

```bash
docker build -t lsky-pro:latest -f docker/Dockerfile .
```

### 多平台构建

```bash
docker buildx build --platform linux/amd64,linux/arm64 -t lsky-pro:latest -f docker/Dockerfile .
```

### 构建并导出

```bash
# 构建镜像
docker build -f docker/Dockerfile -t lsky-pro:latest .

# 导出镜像
docker save lsky-pro:latest | gzip > lsky-pro-docker-latest.tar.gz

# 在目标机器上导入
gunzip -c lsky-pro-docker-latest.tar.gz | docker load
```

### SQLite 版本

```bash
docker run -d \
  --name lsky-pro \
  -p 8080:80 \
  -e APP_NAME=兰空图床 \
  -e APP_URL=your-domain \
  -e APP_LICENSE_KEY=your-license-key \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_EMAIL=admin@qq.com \
  -e ADMIN_PASSWORD=123456 \
  -v lsky-storage:/var/www/html/storage \
  -v lsky-database:/var/www/html/database \
  lsky-pro:latest
```

### MySQL 版本

> 连接独立的数据库，需要提前安装并启动后创建数据库，或连接宿主机中的 mysql
> 容器内 MySQL，DB_HOST 对应 docker-compose 中的服务名
> 宿主机 MySQL，DB_HOST 通常为 host.docker.internal（如果在 Linux 系统中无法使用 host.docker.internal，可通过设置 --add-host 或将宿主机 IP 显式写入 /etc/hosts）

```bash
docker run -d \
  --name lsky-pro \
  -p 8080:80 \
  -e APP_NAME=兰空图床 \
  -e APP_URL=your-domain \
  -e APP_LICENSE_KEY=your-license-key \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_EMAIL=admin@qq.com \
  -e ADMIN_PASSWORD=123456 \
  -e APP_LICENSE_KEY=your-license-key \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=mysql-server \
  -e DB_PORT=3306 \
  -e DB_DATABASE=lsky \
  -e DB_USERNAME=lsky \
  -e DB_PASSWORD=your-password \
  -v lsky-storage:/var/www/html/storage \
  lsky-pro:latest
```

### 完整配置示例

```bash
docker run -d \
  --name lsky-pro \
  -p 8080:80 \
  -e APP_NAME="兰空图床" \
  -e APP_URL=https://img.example.com \
  -e APP_LICENSE_KEY=your-license-key \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_EMAIL=admin@example.com \
  -e ADMIN_PASSWORD=secure-password \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=mysql-server \
  -e DB_DATABASE=lsky \
  -e DB_USERNAME=lsky \
  -e DB_PASSWORD=your-password \
  -e CACHE_STORE=redis \
  -e REDIS_HOST=redis-server \
  -v lsky-storage:/var/www/html/storage \
  lsky-pro:latest
```

## 环境变量配置

### 应用基础配置

| 变量名               | 是否必须 | 默认值                 | 说明      |
|-------------------|------|---------------------|---------|
| `APP_LICENSE_KEY` | 是    | -                   | 许可证密钥   |
| `APP_URL`         | 是    | `http://localhost`  | 应用URL   |
| `ADMIN_USERNAME`  | 是    | `admin`             | 管理员用户名  |
| `ADMIN_EMAIL`     | 是    | `admin@example.com` | 管理员邮箱   |
| `ADMIN_PASSWORD`  | 是    | `admin123`          | 管理员密码   |
| `APP_NAME`        | 否    | `兰空图床`              | 应用名称    |
| `DB_CONNECTION`   | 否    | `sqlite`            | 数据库类型   |
| `DB_HOST`         | 否    | `127.0.0.1`         | 数据库主机   |
| `DB_PORT`         | 否    | `3306`              | 数据库主机   |
| `DB_DATABASE`     | 否    | -                   | 数据库名/路径 |
| `DB_USERNAME`     | 否    | -                   | 数据库用户名  |
| `DB_PASSWORD`     | 否    | -                   | 数据库密码   |

## Docker Compose 示例

### SQLite 版本

```yaml
services:
  lsky-pro:
    image: lsky-pro:latest
    container_name: lsky-pro
    ports:
      - "8080:80"
    environment:
      - APP_NAME=兰空图床
      - APP_URL=https://lsky.pro
      - APP_LICENSE_KEY=xxxx-xxxx-xxxx-xxxx
      - ADMIN_USERNAME=admin # 不设置则默认为 admin
      - ADMIN_EMAIL=admin@qq.com # 不设置则默认为 admin@example.com
      - ADMIN_PASSWORD=secure-password # 不设置则默认为 admin123
    volumes:
      - lsky-storage:/var/www/html/storage
      - lsky-database:/var/www/html/database
    restart: unless-stopped

volumes:
  lsky-storage:
  lsky-database:
```

### MySQL 版本

```yaml
services:
  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=root-password
      - MYSQL_DATABASE=lsky
      - MYSQL_USER=lsky
      - MYSQL_PASSWORD=lsky-password
    volumes:
      - mysql-data:/var/lib/mysql
    restart: unless-stopped

  lsky-pro:
    image: lsky-pro:latest
    depends_on:
      - mysql
    ports:
      - "8800:80"
    environment:
      - APP_NAME=兰空图床
      - APP_URL=https://lsky.pro
      - APP_LICENSE_KEY=xxxx-xxxx-xxxx-xxxx
      - ADMIN_USERNAME=admin # 不设置则默认为 admin
      - ADMIN_EMAIL=admin@qq.com # 不设置则默认为 admin@example.com
      - ADMIN_PASSWORD=secure-password # 不设置则默认为 admin123
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_DATABASE=lsky
      - DB_USERNAME=lsky
      - DB_PASSWORD=lsky-password
    volumes:
      - lsky-storage:/var/www/html/storage
    restart: unless-stopped

volumes:
  mysql-data:
  lsky-storage:
```

## Nginx 反向代理示例

```nginx configuration
location ~ ^/ {
    proxy_pass http://localhost:8080;
    proxy_http_version 1.1;

    proxy_set_header Host $host;
    proxy_set_header Scheme $scheme;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection $http_connection;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

    add_header X-Cache $upstream_cache_status;
    add_header Cache-Control no-cache;
}
```

## 故障排除

### 查看日志

```bash
docker logs lsky-pro
```

### 进入容器

```bash
docker exec -it lsky-pro bash
```

### 重新安装

```bash
docker exec -it lsky-pro rm /var/www/html/installed.lock /var/www/html/.env
docker restart lsky-pro
```

### 清除缓存

```bash
docker exec -it lsky-pro php artisan optimize:clear
```
