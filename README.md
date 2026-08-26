# lskyPai-PRO

基于 Lsky Pro 的公益版增强发行，适合个人图床、自建相册和 WebDAV/CD2 云端存储场景。

## 目录

- [功能特性](#功能特性)
- [快速部署](#快速部署)
- [完整 Docker Compose 部署](#完整-docker-compose-部署)
- [从开源版升级迁移](#从开源版升级迁移)
- [存储迁移](#存储迁移)
- [聚合 QQ/微信登录](#聚合-qq微信登录)
- [环境变量](#环境变量)
- [常用维护命令](#常用维护命令)
- [源码开发与构建](#源码开发与构建)
- [安全说明](#安全说明)

## 功能特性

### 图片与存储

- 本地、WebDAV、S3 及其他 Flysystem 存储策略。
- 图片 URL 使用稳定 pathname，物理存储与外链解耦。
- 支持本地图片迁移到 WebDAV/CD2 等远端存储，迁移后原链接保持不变。
- MD5 去重、可调并发、断点续传和重复任务互斥。
- 两阶段迁移：先复制并核验目标，再独立清理源副本。
- WebDAV 写入后延迟核验，兼容云盘最终一致性。
- 不依赖 WebDAV `MOVE`，兼容不支持 MOVE 的 WebDAV 服务。
- 本地文件不存在时按图片记录智能回源，并写入本地缓存。
- nginx/WebDAV 反代和缓存优先直出，Laravel 负责正确性兜底。

### 账号与登录

- 用户注册、登录、邮箱/手机号验证和个人资料管理。
- 聚合 CC 社会化登录，支持 QQ、微信、百度等多种方式。
- 后台可使用 `qq`、`qq/wx`、`qq/wx/baidu` 等配置。
- 登录、注册和个人资料绑定页按实际平台显示图标与中文名称。
- 1～2 个登录方式显示“图标 + 名称”，超过 2 个仅显示图标。
- QQ、微信等绑定相互独立，支持精确解绑。
- 身份安全边界为 `driver_id + type + openid`，防止覆盖和串号。

### 管理与部署

- Filament 中文后台管理。
- 队列、调度器和定时任务由 Supervisor 管理。
- Docker 自动安装，支持 SQLite 和 MySQL。
- PHP 8.4、nginx、PHP-FPM、Supervisor 一体化镜像。
- GitHub Actions 自动构建并发布 GHCR 多架构镜像。

## 快速部署

### SQLite 单容器

```bash
mkdir -p lsky-data

docker run -d \
  --name lsky-pro \
  --restart unless-stopped \
  -p 8080:80 \
  -e APP_URL=https://img.example.com \
  -e APP_NAME=lskyPai-PRO \
  -e APP_LICENSE_KEY=lskyPai-PRO-free \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_EMAIL=admin@example.com \
  -e ADMIN_PASSWORD='请替换为强密码' \
  -v "$PWD/lsky-data:/var/www/html" \
  ghcr.io/clawpai/lsky-pro:latest
```

首次启动会自动安装，访问 `http://服务器地址:8080`。生产环境请在反向代理中启用 HTTPS，并设置强管理员密码。

### 使用已存在的 WebDAV/CD2

首次安装后进入后台：

```text
系统设置 → 储存管理 → 新增储存策略 → WebDAV
```

填写 WebDAV 地址、用户名、密码和根目录。凭据只保存在应用配置中，不要写入公开仓库或镜像构建参数。

## 完整 Docker Compose 部署

下载配置：

```bash
mkdir -p lsky && cd lsky
curl -fsSLO https://raw.githubusercontent.com/clawpai/lsky-pro/main/docker-compose.yaml
```

编辑 `docker-compose.yaml`，至少修改：

- `APP_URL`；
- `DB_PASSWORD`；
- `MYSQL_ROOT_PASSWORD`；
- `ADMIN_PASSWORD`。

启动：

```bash
docker compose up -d

docker compose ps
docker compose logs -f lsky-pro
```

默认访问 `http://服务器地址:9080`。生产部署建议由 nginx、Caddy 或云负载均衡器提供 HTTPS 和域名访问。

## 从开源版升级迁移

本节适用于从 lsky-org/lsky-pro 2.x 开源版迁移到 lskyPai-PRO 2.3。迁移前请完成数据库和文件备份，并先在测试目录演练。

### 1. 记录旧系统信息

记录旧系统的：

- 数据库类型、地址、端口、数据库名、账号；
- `storage/app` 本地文件目录；
- 所有远端储存策略及其完整配置；
- 站点 URL、管理员账号和 API 使用情况。

不要直接覆盖旧站点目录。

### 2. 启动新实例

先按上面的 SQLite 或 Compose 方式启动新实例，并完成基础安装。若使用 MySQL，确保新容器能访问旧数据库或旧数据库导出的备份。

### 3. 导入开源版数据

如果镜像中包含开源版导入命令，可在新容器中执行：

```bash
docker exec -it lsky-pro php artisan app:import-open \
  --db-host=旧数据库地址 \
  --db-port=3306 \
  --db-database=旧数据库名 \
  --db-username=旧数据库账号 \
  --db-password='旧数据库密码'
```

实际执行前先运行命令帮助确认参数：

```bash
docker exec -it lsky-pro php artisan app:import-open --help
```

导入后检查用户、相册、图片、储存策略和 API Token 数量。不要在未备份时执行覆盖式导入。

### 4. 同步本地文件

如果旧图片属于本地储存，先停止写入或安排维护窗口，再同步旧站点的 `storage/app` 对应内容到新实例的数据目录：

```bash
rsync -aH --info=progress2 /旧站点/storage/app/ ./lsky-data/storage/app/
```

同步后检查文件权限并重新生成链接：

```bash
docker exec lsky-pro php artisan storage:link --force --relative
docker exec lsky-pro php artisan optimize:clear
```

远端储存不需要下载回本地，只需在新实例中准确恢复储存配置。

### 5. 升级后的验收

至少检查：

```bash
curl -I https://img.example.com/
curl -I https://img.example.com/admin/login
curl -I https://img.example.com/api/v2/configs
```

随机打开旧图片 URL、相册、用户资料和 API 外链。确认无误后再切换域名 DNS 或反向代理。

## 存储迁移

### 推荐安全流程

迁移必须拆成两阶段：

```bash
# 第一阶段：复制、目标核验、切换 storage_id，不删除源文件
docker exec lsky-pro php artisan app:storage-migrate \
  --from=1 --to=2 --concurrency=16

# 第二阶段：全部核验后，逐条清理本地冗余副本
docker exec lsky-pro php artisan app:storage-cleanup \
  --from=1 --to=2
```

第一阶段完成后，旧 pathname 不变，数据库记录切换到目标储存；第二阶段只有在 WebDAV 文件存在且大小一致时才删除本地副本。

### 并发建议

从 8 或 16 开始，用 1～5 分钟观察：

- 迁移速度；
- 失败数；
- WebDAV 响应时间；
- CPU、内存和数据库锁；
- CD2/Google Drive 是否出现排队或 405。

并发越大不一定越快。若出现超时、写后核验延迟、吞吐下降或错误增加，应降低并发。

### 中断恢复

SSH 断开不等于任务停止。先检查容器内进程、日志和数据库计数。第一阶段可以安全重跑；第二阶段也可以重跑，目标核验失败时会保留本地文件。

不要在第一阶段手动删除本地源文件，也不要在没有目标核验的情况下执行 `rm`。

## 聚合 QQ/微信登录

后台新增一个社会化登录驱动：

```text
Provider：聚合 CC 登录
登录方式：qq/wx
```

可用配置示例：

```text
qq
qq/wx
qq/wx/baidu
```

前台会按实际 `type` 展示 QQ、微信、百度。每种方式都通过以下身份键独立识别：

```text
driver_id + type + openid
```

因此：

- QQ 绑定不会覆盖微信；
- 微信绑定不会覆盖 QQ；
- 同一个第三方身份不能绑定到两个本站账号；
- 昵称和头像变化只更新资料；
- 解绑单条记录不会误删其他绑定。

聚合网关如果在回调中重复追加 `type`，后端会兼容完全相同的重复值，例如 `wx,wx`；混合值例如 `qq,wx` 会被拒绝。

## 环境变量

| 变量 | 默认值 | 说明 |
|---|---|---|
| `APP_URL` | 无 | 站点完整 URL，首次安装必填 |
| `APP_NAME` | `兰空图床` | 站点名称 |
| `APP_LICENSE_KEY` | `lskyPai-PRO-free` | 公益版标识；当前本地授权验证直接通过 |
| `ADMIN_USERNAME` | `admin` | 首次安装的管理员账号 |
| `ADMIN_EMAIL` | `admin@example.com` | 首次安装的管理员邮箱 |
| `ADMIN_PASSWORD` | `admin123` | 首次安装的管理员密码，生产必须修改 |
| `DB_CONNECTION` | `sqlite` | `sqlite`、`mysql` 等 |
| `DB_HOST` | `127.0.0.1` | MySQL 地址 |
| `DB_PORT` | `3306` | MySQL 端口 |
| `DB_DATABASE` | SQLite 路径 | 数据库名或 SQLite 文件路径 |
| `DB_USERNAME` | 无 | MySQL 用户名 |
| `DB_PASSWORD` | 无 | MySQL 密码 |

## 常用维护命令

```bash
# 查看容器状态
docker ps

# 查看应用日志
docker logs -f lsky-pro

# 清理 Laravel 缓存
docker exec lsky-pro php artisan optimize:clear

# 查看队列/调度器
docker exec lsky-pro supervisorctl status

# 进入容器
docker exec -it lsky-pro bash
```

重置安装仅适用于全新测试目录，执行前必须备份：

```bash
docker exec lsky-pro rm -f /var/www/html/installed.lock
docker restart lsky-pro
```

## 源码开发与构建

环境要求：

- PHP 8.4；
- Composer；
- Node.js 22；
- Yarn；
- Docker Buildx（本地构建时）。

前端构建：

```bash
cd web
yarn install
yarn build
```

本地镜像构建：

```bash
docker build -t lsky-pro:2.3 .
```

多架构构建：

```bash
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  -t ghcr.io/clawpai/lsky-pro:latest \
  -t ghcr.io/clawpai/lsky-pro:2.3 \
  --push .
```

GitHub Actions 会在推送 `main` 或 `v*` 标签时构建并推送 GHCR 镜像。发布后请在 Actions 页面确认测试、构建和镜像发布任务均成功。

## 安全说明

- 不要把数据库密码、WebDAV 密码、OAuth AppKey、SSH 私钥或 GitHub Token 写进仓库。
- 生产部署必须设置强管理员密码和 HTTPS。
- 不要公开上传生产数据库、日志、备份、缓存或用户头像数据。
- 图片迁移必须遵循“目标核验后才删除源”的顺序。
- 公共仓库中的域名、IP 和路径请使用示例值。

## 许可证

本项目用于个人自建和技术交流。请遵守上游项目许可证及各依赖项目的许可证要求。
