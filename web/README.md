# Lsky Pro+ - 2x.nz特供离线版 前端工程

使用 Vue3 + TypeScript + Pinia + Tailwindcss + Naiveui，基于 Vite 构建。

## 说明

- 执行构建命令后，静态资源会同步复制 [../public/assets](..%2Fpublic%2Fassets) 中，同时 index.html 会复制到 [../resources/views/welcome.blade.php](..%2Fresources%2Fviews%2Fwelcome.blade.php)，由 laravel 渲染前端编译后的文件。
- 可以使用 `yarn openapi-ts` 命令通过根目录的 openapi.json 文件生成接口服务代码和枚举类。

### 构建

```sh
npm install # yarn
```

### 热重载开发

```sh
npm run dev # yarn dev
```

### 编译

```sh
npm run build # yarn build
```
