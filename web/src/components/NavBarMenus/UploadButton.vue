<template>
  <div>
    <ImageUploader ref="uploaderRef" v-bind="uploadOptions" @upload-success="handleUploadSuccess" />
    
    <NBadge :value="pendingCount > 0 ? pendingCount : undefined" :processing="isUploading">
      <NButton circle secondary strong @click="handleUploadClick" :title="i18n.t('Upload')">
        <template #icon>
          <NIcon :component="CloudUploadOutlineIcon" :size="22" />
        </template>
      </NButton>
    </NBadge>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { NButton, NIcon, useMessage, NBadge } from 'naive-ui'
import { CloudUploadOutline as CloudUploadOutlineIcon } from '@vicons/ionicons5'
import ImageUploader from '@/components/Upload/ImageUploader.vue'
import { useConfigStore } from '@/stores/config'
import { useUserStore } from '@/stores/user'
import { usePhotoStore } from '@/stores/photo'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import number from "@/utils/number";
import eventBus, { EVENTS } from '@/utils/eventBus'

const message = useMessage()
const configStore = useConfigStore()
const userStore = useUserStore()
const photoStore = usePhotoStore()
const route = useRoute()
const i18n = useI18n()

const uploaderRef = ref<any>(null)
const uploadingCount = ref(0)
const pendingCount = ref(0)
const isUploading = ref(false)

let statusTimer: number | null = null

const uploadOptions = computed(() => {
  const formData = {
    storage_id: (configStore.group?.storages || []).length > 0 ? configStore.group?.storages[0].id : undefined,
    'tags[]': [],
    is_public: false,
  }
  
  const headers: Record<string, string> = {}
  const token = userStore.token
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }
  
  const tip = i18n.t('You can currently upload up to {max} images, allowing {limit} uploads at a time. The site has hosted {count} images.', {
    max: (number.fileSize((configStore.group?.group.options.max_upload_size || 0) * 1024)).toString(),
    limit: (configStore.group?.group.options.limit_concurrent_upload || 3).toString(),
    count: (configStore.configs?.app.photo_count || 0).toString(),
  })
  
  return {
    endpoint: '/api/v2/upload',
    maxFileSize: (configStore.group?.group.options.max_upload_size || 0) * 1024,
    concurrency: configStore.group?.group.options.limit_concurrent_upload || 3,
    allowedFileTypes: ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.tif'],
    formData,
    headers,
    tip
  }
})

function updateUploadStatus() {
  if (uploaderRef.value) {
    const files = uploaderRef.value.getFiles?.() || []
    uploadingCount.value = files.filter((file: any) => file.status === 'uploading').length
    pendingCount.value = files.filter((file: any) => file.status === 'pending').length
    isUploading.value = uploadingCount.value > 0
  }
}

onMounted(() => {
  statusTimer = window.setInterval(updateUploadStatus, 1000)
  
  eventBus.on(EVENTS.TRIGGER_UPLOAD_BUTTON, handleUploadClick)
  eventBus.on(EVENTS.OPEN_UPLOAD_QUEUE, openUploadQueue)
})

onUnmounted(() => {
  if (statusTimer !== null) {
    clearInterval(statusTimer)
  }
  
  eventBus.off(EVENTS.TRIGGER_UPLOAD_BUTTON, handleUploadClick)
  eventBus.off(EVENTS.OPEN_UPLOAD_QUEUE, openUploadQueue)
})

function handleUploadClick() {
  if (!configStore.configs?.app.guest_upload && !userStore.isLoggedIn) {
    message.error(i18n.t('Please login before uploading images'))
    return
  }
  
  const uploadButtonAction = userStore.profile?.options?.upload_button_action || 'select_files'
  
  if (uploadButtonAction === 'open_queue') {
    uploaderRef.value?.showUploadQueue()
  } else {
    uploaderRef.value?.selectFiles()
  }
}

function handleUploadSuccess(file: any, response: any) {
  setTimeout(async () => {
    if (userStore.isLoggedIn) {
      await userStore.fetchUserProfile()
    }
    
    if (route.path.includes('/user/photo') && userStore.isLoggedIn) {
      const currentQueries = { ...photoStore.queries }
      photoStore.resetPhotos(currentQueries)
    }
  }, 300)
}

function openUploadQueue() {
  if (uploaderRef.value) {
    uploaderRef.value.showUploadQueue()
  }
}

defineExpose({
  selectFiles: () => uploaderRef.value?.selectFiles(),
  showUploadQueue: openUploadQueue,
  handleUploadClick,
  uploaderRef
})
</script> 