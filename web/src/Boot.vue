<script lang="ts" setup>
import {RouterView} from 'vue-router'
import {
  NSpin,
  NButton,
  useNotification,
  useModal,
} from 'naive-ui';
import {useLayoutStore} from "@/stores/layout";
import {useDeviceStore} from "@/stores/device";
import {useUserStore} from "@/stores/user";
import {h, onMounted, ref} from "vue";
import {useConfigStore} from "@/stores/config";
import {getConfigs, getGroup, getPages} from "@/api";
import { MD5 } from 'crypto-js';
import { client } from "@/api/client.gen"
import {useI18n} from "vue-i18n";
import { preloadComponents, isSlowConnection } from '@/utils/lazyload';

// 预加载关键组件
const preloadMainComponents = async () => {
  try {
    // 检测是否慢网络连接，如果是，只预加载首页组件
    if (isSlowConnection()) {
      return import('@/views/HomeView.vue');
    }
    
    // 正常网络，预加载更多组件
    return preloadComponents([
      () => import('@/views/HomeView.vue'),
      () => import('@/views/PageView.vue'),
      () => import('@/views/ShareView.vue')
    ]);
  } catch (error) {
    console.error('组件预加载失败', error);
    // 即使预加载失败，也不影响应用使用，静默失败
    return Promise.resolve();
  }
}

// 是否已经初始化完成
const initialized = ref<Boolean>(false)
const configStore = useConfigStore()
const deviceStore = useDeviceStore()
const layoutStore = useLayoutStore()
const userStore = useUserStore()
const notification = useNotification()
const modal = useModal()
const { t } = useI18n()

const initialize = async () => {
  // 配置请求库
  client.instance.defaults.headers.common['Accept'] = 'application/json'

  client.setConfig({
    baseURL: '/api/v2',
    withCredentials: true,
    withXSRFToken: true,
  })

  client.instance.interceptors.request.use(
    function (config) {

      const token = userStore.token
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }

      // 可以在这里添加请求的其他配置
      return config
    },
    function (error) {
      return Promise.reject(error)
    }
  )

  client.instance.interceptors.response.use(
    function (response) {
      return response
    },
    function (error) {
      // 400 请求参数错误
      // 401 认证失败
      // 403 没有权限
      // 404 资源不存在
      // 422 验证错误
      // 429 请求频繁
      // 500 服务器错误
      // 502 网关错误
      // 503 服务不可用
      // 504 网关超时
      const status = error.response?.status

      if (status === 401) {
        window.location.replace('/login')
        return
      }

      if (status === 404) {
        // TODO

        // window.location.replace('/404')
        return
      }

      if (status === 404 || status >= 500) {
        notification.create({
          type: 'error',
          title: t('Service is currently unavailable. Please try again later.'),
          content: error.response?.data?.message || error.toString(),
        })
      } else {
        error = {...error, ...{
            status: error.response.status,
            statusText: error.response.statusText,
            data: error.response.data,
          }}
      }

      return Promise.reject(error)
    }
  )

  try {
    // 并行请求初始化数据
    const [options, group, pages] = await Promise.all([
      getConfigs(), // 获取初始化配置
      getGroup(),   // 当前所在组信息
      getPages()    // 页面数据
    ]);

    // 缓存配置（setConfigs方法内部会自动调用loadCustomAssets）
    configStore.setConfigs(options.data!.data)
    configStore.setGroup(group.data!.data)
    configStore.setPages(pages.data!.data.data)
    
    // 标记初始化完成，立即开始渲染内容
    initialized.value = true
    
    // 然后在后台预加载其他组件
    Promise.resolve().then(() => {
      preloadMainComponents();
    });

    if (configStore.configs?.site.notice) {
      const noticeHash = MD5(configStore.configs?.site.notice).toString()
      if (noticeHash != localStorage.getItem('noticeHash')) {
        const m = modal.create({
          title: t('Announcement'),
          preset: 'card',
          class: 'container m-4 md:mx-auto md:my-10 max-w-screen-md',
          content: () => h('article', {class: 'prose dark:prose-invert max-w-none overflow-x-auto', innerHTML: configStore.configs?.site.notice}),
          footer: () => h('div', {class: 'flex justify-end'}, h(NButton, {
            type: 'primary',
            onClick: () => {
              localStorage.setItem('noticeHash', MD5(configStore.configs?.site.notice || '').toString())
              m.destroy()
            },
          }, {default: () => t('Got it')}))
        })
      }
    }
  } catch (error) {
    console.error('初始化失败:', error);
    notification.create({
      type: 'error',
      title: t('初始化失败'),
      content: t('请刷新页面重试'),
    })
  }
}

onMounted(() => {
  // 移动端默认关闭侧边栏
  if (deviceStore.isMobile) {
    layoutStore.setSidebarOpen(false)
  }

  initialize()
})
</script>

<template>
  <RouterView v-if="initialized" />
  <div v-else aria-hidden="true" class="fixed inset-0 z-50 bg-white dark:bg-black flex items-center justify-center">
    <NSpin size="large" />
  </div>
</template>
