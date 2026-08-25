<script lang="ts" setup>
import {NAvatar, NButton, NIcon, NTag} from "naive-ui"
import {CloseOutline as CloseOutlineIcon, Heart, HeartOutline as HeartOutlineIcon} from "@vicons/ionicons5"
import {h, ref, watch, onMounted, onUnmounted, nextTick} from "vue"
import {useExploreStore} from "@/stores/explore"
import {useRouter} from "vue-router"
import {useLayoutStore} from "@/stores/layout"
import Report from "@/components/Explore/Report.vue"
import app from "@/utils/app"
import number from "@/utils/number"

const props = defineProps({
  photo: {
    type: Object,
    required: true,
    default: () => {}
  },
  show: {
    type: Boolean,
    required: true,
    default: false
  },
  showUserInfo: {
    type: Boolean,
    required: false,
    default: () => true,
  },
  showLike: {
    type: Boolean,
    required: false,
    default: () => true,
  },
  showReport: {
    type: Boolean,
    required: false,
    default: () => true,
  }
})

const emit = defineEmits(['update:show'])

const exploreStore = useExploreStore()
const router = useRouter()
const layoutStore = useLayoutStore()
const reportRef = ref<any>(null)

const imageRef = ref<HTMLImageElement>()
const containerRef = ref<HTMLDivElement>()
const scale = ref(1)
const translateX = ref(0)
const translateY = ref(0)
const isDragging = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const imageLoaded = ref(false)

function like() {
  if (props.photo.is_liked) {
    exploreStore.photoUnlinked(props.photo.id)
    props.photo.is_liked = false
  } else {
    exploreStore.photoLiked(props.photo.id)
    props.photo.is_liked = true
  }
}

function report() {
  reportRef.value?.open({
    path: {
      id: props.photo.id
    }
  })
}

function toUser() {
  router.push(`/explore/@${props.photo.user.username}`).then(() => layoutStore.setSidebarOpen(false))
}

function close() {
  emit('update:show', false)
}

function resetImageState() {
  scale.value = 1
  translateX.value = 0
  translateY.value = 0
  imageLoaded.value = false
}

function onImageLoad() {
  imageLoaded.value = true
}

function onWheel(event: WheelEvent) {
  event.preventDefault()
  const delta = event.deltaY > 0 ? -0.1 : 0.1
  const newScale = Math.max(0.1, Math.min(5, scale.value + delta))
  scale.value = newScale
}

function onMouseDown(event: MouseEvent) {
  if (scale.value <= 1) return
  isDragging.value = true
  dragStart.value = {
    x: event.clientX - translateX.value,
    y: event.clientY - translateY.value
  }
  event.preventDefault()
}

function onMouseMove(event: MouseEvent) {
  if (!isDragging.value || scale.value <= 1) return
  translateX.value = event.clientX - dragStart.value.x
  translateY.value = event.clientY - dragStart.value.y
}

function onMouseUp() {
  isDragging.value = false
}

function onDoubleClick() {
  if (scale.value === 1) {
    scale.value = 2
  } else {
    scale.value = 1
    translateX.value = 0
    translateY.value = 0
  }
}

function onKeyDown(event: KeyboardEvent) {
  if (!props.show) return
  
  if (event.key === 'Escape') {
    close()
  }
}

watch(() => props.show, (newShow) => {
  if (newShow) {
    resetImageState()
    nextTick(() => {
      document.body.style.overflow = 'hidden'
    })
  } else {
    document.body.style.overflow = ''
  }
})

onMounted(() => {
  document.addEventListener('keydown', onKeyDown)
  document.addEventListener('mousemove', onMouseMove)
  document.addEventListener('mouseup', onMouseUp)
})

onUnmounted(() => {
  document.removeEventListener('keydown', onKeyDown)
  document.removeEventListener('mousemove', onMouseMove)
  document.removeEventListener('mouseup', onMouseUp)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="photo-viewer">
      <div class="photo-viewer-backdrop" @click="close"></div>
      
      <div 
        class="photo-viewer-bg"
        :style="{ backgroundImage: `url(${props.photo.public_url})` }"
      ></div>
      
      <button class="photo-viewer-close" @click="close">
        <NIcon :component="CloseOutlineIcon" size="24" />
      </button>
      
      <div ref="containerRef" class="photo-viewer-container" @wheel="onWheel">
        <img
          ref="imageRef"
          :src="props.photo.public_url"
          :alt="props.photo.name"
          class="photo-viewer-image"
          :class="{ 'cursor-grab': scale > 1 && !isDragging, 'cursor-grabbing': isDragging }"
          :style="{
            transform: `scale(${scale}) translate(${translateX / scale}px, ${translateY / scale}px)`,
            opacity: imageLoaded ? 1 : 0
          }"
          @load="onImageLoad"
          @mousedown="onMouseDown"
          @dblclick="onDoubleClick"
          draggable="false"
        />
        
        <div v-if="!imageLoaded" class="photo-viewer-loading">
          <div class="loading-spinner"></div>
        </div>
      </div>
      
      <div class="photo-viewer-info" @click.stop>
        <div class="photo-viewer-info-left">
          <div class="space-y-1">
            <div class="text-white text-sm font-medium">{{ props.photo.name || 'Untitled' }}</div>
            <div class="text-white/80 text-xs">
              {{ (props.photo.extension || 'Unknown').toUpperCase() }} • 
              {{ props.photo.width || 0 }} × {{ props.photo.height || 0 }} • 
              {{ number.fileSize(props.photo.size || 0) }}
            </div>
            <div class="flex gap-1 flex-wrap" v-if="props.photo.tags && props.photo.tags.length > 0">
              <NTag 
                v-for="tag in props.photo.tags.slice(0, 3)" 
                :key="tag.id"
                size="small"
                :bordered="false"
                class="bg-white/20 text-white"
              >
                {{ tag.name }}
              </NTag>
              <span v-if="props.photo.tags.length > 3" class="text-xs text-white/70">+{{ props.photo.tags.length - 3 }}</span>
            </div>
          </div>
        </div>
        
        <div class="photo-viewer-info-right">
          <div class="flex items-center space-x-3" v-if="props.showUserInfo">
            <div class="flex items-center space-x-2 cursor-pointer" @click="toUser">
              <NAvatar
                round
                :size="32"
                :src="app.getUserAvatar(props.photo.user.avatar_url)"
              />
              <span class="text-white text-sm">{{ props.photo.user.name }}</span>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <NButton
              v-if="props.showLike"
              quaternary
              size="small"
              :type="props.photo.is_liked ? 'error' : 'default'"
              :render-icon="() => h(NIcon, { size: 16 }, { default: () => h(props.photo.is_liked ? Heart : HeartOutlineIcon)} )"
              @click="like"
              class="text-white border-white/30 hover:bg-white/10"
            >
              {{ $t('Like') }}
            </NButton>
            
            <NButton
              v-if="props.showReport"
              quaternary
              size="small"
              @click="report"
              class="text-white border-white/30 hover:bg-white/10"
            >
              {{ $t('Report') }}
            </NButton>
          </div>
        </div>
      </div>
      
      <div class="photo-viewer-hint" v-if="scale === 1" @click.stop>
        <span class="text-white/60 text-xs">滚轮缩放 • 双击放大 • ESC关闭</span>
      </div>
      
    </div>
  </Teleport>
  
  <Teleport to="body">
    <Report ref="reportRef" type="photo" />
  </Teleport>
</template>

<style scoped>
.photo-viewer {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.photo-viewer-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: transparent;
  cursor: pointer;
  z-index: 1;
}

.photo-viewer-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  filter: blur(20px);
  transform: scale(1.1);
}

.photo-viewer-bg::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
}

.photo-viewer-close {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 10;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.photo-viewer-close:hover {
  background: rgba(0, 0, 0, 0.7);
}

.photo-viewer-container {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  z-index: 5;
}

.photo-viewer-image {
  max-width: 90vw;
  max-height: 90vh;
  object-fit: contain;
  transition: opacity 0.3s, transform 0.1s;
  user-select: none;
  -webkit-user-drag: none;
}

.photo-viewer-loading {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top: 3px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.photo-viewer-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
  padding: 40px 20px 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  z-index: 5;
}

.photo-viewer-info-left {
  flex: 1;
  min-width: 0;
}

.photo-viewer-info-right {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

.photo-viewer-hint {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 10;
}

.cursor-grab {
  cursor: grab;
}

.cursor-grabbing {
  cursor: grabbing;
}

@media (max-width: 768px) {
  .photo-viewer-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .photo-viewer-info-right {
    width: 100%;
    justify-content: space-between;
  }
  
  .photo-viewer-close {
    top: 10px;
    right: 10px;
    width: 36px;
    height: 36px;
  }
  
  .photo-viewer-hint {
    top: 10px;
    left: 10px;
  }
}
</style>