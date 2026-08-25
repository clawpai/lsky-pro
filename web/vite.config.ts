import {ConfigEnv, defineConfig, loadEnv} from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';
import viteCompression from 'vite-plugin-compression';
import { visualizer } from 'rollup-plugin-visualizer';
import viteImagemin from 'vite-plugin-imagemin';

// https://vitejs.dev/config/
export default ({ mode }: ConfigEnv) => {
  const env = loadEnv(mode, process.cwd())

  return defineConfig({
    build: {
      assetsDir: 'themes/default',
      chunkSizeWarningLimit: 1000, // 设置警告阈值为 1000 kB
      minify: 'terser', // 使用terser进行更强的压缩
      terserOptions: {
        compress: {
          drop_console: true, // 移除console语句
          drop_debugger: true, // 移除debugger语句
        },
      },
      rollupOptions: {
        output: {
          // 静态资源分类打包
          assetFileNames: 'themes/default/assets/[ext]/[name]-[hash].[ext]',
          chunkFileNames: 'themes/default/js/[name]-[hash].js',
          entryFileNames: 'themes/default/js/[name]-[hash].js',
          manualChunks: {
            'naive-ui': ['naive-ui'],
            'vue-vendors': ['vue', 'vue-router', 'pinia', 'vue-i18n'],
            'icons': [
              '@fortawesome/fontawesome-svg-core',
              '@fortawesome/free-brands-svg-icons',
              '@fortawesome/free-regular-svg-icons',
              '@fortawesome/free-solid-svg-icons',
              '@fortawesome/vue-fontawesome',
              '@vicons/antd'
            ],
            'photo-components': [
              'photoswipe',
              'vue-photo-album',
              'vue-advanced-cropper'
            ],
            'utils': [
              'crypto-js',
              'dayjs',
              'axios',
              'uuid'
            ]
          }
        }
      }
    },
    server: {
      proxy: {
        '/api/v2': {
          target: env.VITE_APP_API_URL,
          changeOrigin: true,
          rewrite: (path) => path.replace(/^\/api\/v2/, ''),
        },
      }
    },
    plugins: [
      vue(),
      viteCompression({
        verbose: true,
        disable: false,
        threshold: 10240,
        algorithm: 'gzip',
        ext: '.gz',
      }),
      viteImagemin({
        gifsicle: {
          optimizationLevel: 7,
          interlaced: false
        },
        optipng: {
          optimizationLevel: 7
        },
        mozjpeg: {
          quality: 80
        },
        pngquant: {
          quality: [0.8, 0.9],
          speed: 4
        },
        svgo: {
          plugins: [
            {
              name: 'removeViewBox'
            },
            {
              name: 'removeEmptyAttrs',
              active: false
            }
          ]
        }
      }),
      visualizer({
        open: false,
        gzipSize: true,
        brotliSize: true,
      }),
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    }
  })
}
