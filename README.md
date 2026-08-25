# lskyPai-PRO v2.3 自部署镜像

兰空图床 Lsky Pro 2.2.3 的自部署 Docker 镜像仓库。基于官方 docker 方案（php:8.4-fpm + nginx + supervisor）优化：

- ✅ 授权完全本地化：`verifyLicense` 直接返回 true，版本检查本地短路，运行时零外部授权请求
- ✅ 镜像内置源码 `/var/www/lsky`，挂载 `/var/www/html` 兼容 `halcyonazure/lsky-pro-docker` 挂载标准（空目录自动拷贝）
- ✅ 自动安装：首次启动自动执行 `app:install`（需提供 APP_URL）
- ✅ 内置 queue / scheduler / cron（supervisor 管理）
- ✅ 健康检查 + 日志落盘

## 一键部署（MySQL，兼容 halcyonazure 挂载标准）

```bash
# 1. 克隆 compose 配置（或直接下载 docker-compose.yaml）
mkdir -p lsky && cd lsky && curl -O https://raw.githubusercontent.com/clawpai/lsky-pro/main/docker-compose.yaml

# 2. 编辑 docker-compose.yaml：改 APP_URL、数据库密码

# 3. 启动
docker compose up -d

# 4. 访问 http://<服务器IP>:9080
```

## 纯 Docker 运行（SQLite）

```bash
docker run -d \
  --name lsky-pro \
  --restart unless-stopped \
  -p 8080:80 \
  -e APP_URL=https://img.your-domain.com \
  -e APP_NAME=兰空图床 \
  -e ADMIN_PASSWORD=your-strong-password \
  -v $PWD/lsky:/var/www/html \
  ghcr.io/clawpai/lsky-pro:latest
```

## 环境变量

| 变量 | 必填 | 默认 | 说明 |
|---|---|---|---|
| `APP_URL` | ✅ | - | 站点地址（必须设置，镜像名占位） |
| `APP_NAME` | - | 兰空图床 | 站点名称 |
| `APP_LICENSE_KEY` | - | local-free | 本地授权，任意值均可 |
| `ADMIN_USERNAME` | - | admin | 管理员账号（仅首次安装） |
| `ADMIN_EMAIL` | - | admin@example.com | 管理员邮箱（仅首次安装） |
| `ADMIN_PASSWORD` | - | admin123 | 管理员密码（仅首次安装，建议设置强密码） |
| `DB_CONNECTION` | - | sqlite | 数据库类型 |
| `DB_HOST` / `DB_PORT` | MySQL时 | 127.0.0.1 / 3306 | 数据库地址 |
| `DB_DATABASE` | - | sqlite 路径 | 数据库名/路径 |
| `DB_USERNAME` / `DB_PASSWORD` | MySQL时 | - | 数据库账号 |

## 从开源版（lsky-org 2.x）迁移数据

开源版数据库结构与付费版 1.7 同源但缺少订阅/套餐/工单等表，官方 `app:import` 会因表缺失失败。
本仓库内置 `app:import-open` 命令，自动跳过不存在的表，仅迁移开源版实际数据：

```bash
# 在新容器内执行（旧库为 MySQL 时）
docker exec -it lsky-pro php artisan app:import-open \
  --db-host=旧库IP --db-port=3306 \
  --db-database=旧库名 --db-username=root --db-password=xxx
```

迁移内容：系统设置、角色组、储存策略、组策略关联、用户（含容量）、图片、相册。
图片文件需手动同步：本地储存把旧程序 `storage/app` 下对应目录拷贝到新站，远端储存（OSS/COS/S3）确认配置一致即可。

## 常见问题

- **重置安装**：`docker exec -it lsky-pro rm -f /var/www/html/installed.lock && docker restart lsky-pro`
- **日志**：`docker logs lsky-pro`；supervisor 子进程日志在容器 `/var/log/supervisor/`
- **缓存**：`docker exec -it lsky-pro php artisan optimize:clear`

## 构建

```bash
docker build -t lsky-pro:2.2.3 .
# 多架构
docker buildx build --platform linux/amd64,linux/arm64 -t ghcr.io/clawpai/lsky-pro:latest --push .
```

GitHub Actions 已配置：push 到 main 自动构建 amd64+arm64 并推送 GHCR（`ghcr.io/clawpai/lsky-pro:latest`）。

> 仅供学习与技术交流使用，请在下载后 24 小时内删除。请支持正版：https://huohuastudio.com