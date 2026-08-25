<script lang="ts" setup>
import Layout from "@/components/Layout.vue";
import {NButton, NIcon, NNumberAnimation, useMessage, type NumberAnimationInst} from "naive-ui"
import {type CSSProperties, onMounted, ref} from "vue";
import {useConfigStore} from "@/stores/config";
import {
  CloudUploadOutline as CloudUploadOutlineIcon,
  HomeOutline as HomeOutlineIcon,
  CompassOutline as CompassOutlineIcon,
} from "@vicons/ionicons5"
import {useUserStore} from "@/stores/user";
import number from "../utils/number";
import {useLayoutStore} from "@/stores/layout";
import {useRouter} from "vue-router";
import arr from "@/utils/arr";
import bgImg from '@/assets/bg.jpg'
import { useI18n } from 'vue-i18n'
import eventBus, { EVENTS } from '@/utils/eventBus'

const router = useRouter()
const layoutStore = useLayoutStore()
const configStore = useConfigStore()
const userStore = useUserStore()
const message = useMessage()
const i18n = useI18n()

const numberAnimationInstRef = ref<NumberAnimationInst | null>(null)
const bgStyle = ref<CSSProperties>()

onMounted(() => {
  // 默认关闭左侧导航
  layoutStore.setSidebarOpen(false)
  setTimeout(() => numberAnimationInstRef.value?.play(), 500)

  if (configStore.configs?.site.homepage_background_image_url) {
    bgStyle.value = {backgroundImage: `url("${configStore.configs?.site.homepage_background_image_url}")`}
  } else if (configStore.configs?.site.homepage_background_images && configStore.configs?.site.homepage_background_images.length > 0) {
    const imageUrl = arr.randomItem(configStore.configs?.site.homepage_background_images)
    bgStyle.value = {backgroundImage: `url("${imageUrl}")`}
  } else {
    bgStyle.value = {backgroundImage: `url("${bgImg}")`}
  }
})

// 处理上传按钮点击
function handleUploadClick() {
  if (!configStore.configs?.app.guest_upload && !userStore.isLoggedIn) {
    message.error(i18n.t('Please login before uploading images'))
    return
  }
  
  // 触发全局事件，让UploadButton组件响应
  eventBus.emit(EVENTS.TRIGGER_UPLOAD_BUTTON)
}
</script>

<template>
  <Layout
    :toggle-header="true"
    :show-footer="true"
    :header-title="configStore.configs?.app.name || ''"
  >
    <div class="relative z-[1] h-screen w-full overflow-hidden">
      <div class="h-full w-full bg-gradient-to-b from-indigo-500 bg-fixed bg-center bg-cover animate-slide rightness-50" :style="bgStyle"></div>

      <div class="absolute inset-0 z-[2] w-full h-full flex items-center bg-black/40">
        <div class="container mx-auto max-w-screen-lg px-6 space-y-6 break-all text-center">
          <h1 class="text-white text-6xl md:text-7xl font-bold">{{ configStore.configs?.site.homepage_title }}</h1>
          <h2 class="text-white text-2xl md:text-3xl font-medium">{{ configStore.configs?.site.homepage_description }}</h2>
          <h3 class="inline-flex bg-black/30 rounded text-md p-2 text-gray-100">
            <i18n-t keypath="The site has hosted {count} images, occupying {size} of storage." scope="global">
              <template #count>
                <NNumberAnimation
                  ref="numberAnimationInstRef"
                  :to="configStore.configs?.app.photo_count"
                  :active="false"
                  show-separator
                />
              </template>
              <template #size>
                {{ number.fileSize((configStore.configs?.app.photo_size || 0) * 1024) }}
              </template>
            </i18n-t>
          </h3>
          <div class="flex flex-col sm:flex-row justify-center gap-6">

            <NButton
              class="p-6"
              strong
              round
              type="info"
              size="large"
              @click="router.push('/explore')"
              v-if="configStore.configs?.app?.enable_explore"
            >
              <template #icon>
                <NIcon :component="CompassOutlineIcon" />
              </template>
              {{ $t('Exploration Square') }}
            </NButton>

            <NButton
              class="p-6"
              strong
              round
              type="error"
              size="large"
              @click="handleUploadClick"
              :disabled="!configStore.configs?.app.guest_upload && !userStore.isLoggedIn"
            >
              <template #icon>
                <NIcon :component="CloudUploadOutlineIcon"/>
              </template>
              {{ $t('Upload Now') }}
            </NButton>

            <NButton class="p-6" strong round type="success" size="large" @click="router.push('/user/dashboard')">
              <template #icon>
                <NIcon :component="HomeOutlineIcon" />
              </template>
              {{ $t('User Center') }}
            </NButton>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>