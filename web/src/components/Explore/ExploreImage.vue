<script lang="ts" setup>
import {NAvatar, NButton, NIcon, NTag} from "naive-ui"
import {Heart, HeartOutline as HeartOutlineIcon} from "@vicons/ionicons5";
import {h, ref} from "vue";
import {useExploreStore} from "@/stores/explore";
import {useRouter} from "vue-router";
import {useLayoutStore} from "@/stores/layout";
import PhotoViewer from "./PhotoViewer.vue";
import app from "@/utils/app";
import number from "@/utils/number";

const props = defineProps({
  photo: {
    type: Object,
    required: true,
    default: () => {}
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

const showViewer = ref(false)
const exploreStore = useExploreStore()
const router = useRouter()
const layoutStore = useLayoutStore()

function like() {
  if (props.photo.is_liked) {
    exploreStore.photoUnlinked(props.photo.id)
    props.photo.is_liked = false
  } else {
    exploreStore.photoLiked(props.photo.id)
    props.photo.is_liked = true
  }
}

function toUser() {
  router.push(`/explore/@${props.photo.user.username}`).then(() => layoutStore.setSidebarOpen(false))
}

</script>

<template>
  <div class="relative group overflow-hidden w-full cursor-zoom-in bg-gray-200 dark:bg-gray-800" @click="showViewer = true">
    <img 
      :src="props.photo.thumbnail_url" 
      :alt="props.photo.name" 
      class="w-full h-auto transition-transform duration-300 group-hover:scale-105"
      loading="lazy"
    >
    
    <!-- 遮罩层 -->
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
    
    <!-- 悬浮信息层 -->
    <div class="absolute bottom-0 left-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white">
      <!-- 底部信息层 -->
      <div class="bg-gradient-to-t from-black via-black/80 to-transparent p-2 space-y-1">
        <!-- 第一行：名称 -->
        <div class="text-xs font-medium truncate">{{ props.photo.name || 'Untitled' }}</div>
        
        <!-- 第二行：格式、尺寸、大小 - 移动端换行显示 -->
        <div class="text-xs opacity-90">
          <div class="sm:hidden space-y-0.5">
            <div>{{ (props.photo.extension || 'Unknown').toUpperCase() }}</div>
            <div>{{ props.photo.width || 0 }} × {{ props.photo.height || 0 }}</div>
            <div>{{ number.fileSize(props.photo.size || 0) }}</div>
          </div>
          <div class="hidden sm:block truncate">
            {{ (props.photo.extension || 'Unknown').toUpperCase() }} • 
            {{ props.photo.width || 0 }} × {{ props.photo.height || 0 }} • 
            {{ number.fileSize(props.photo.size || 0) }}
          </div>
        </div>
        
        <!-- 第三行：标签 -->
        <div class="flex gap-1 flex-wrap" v-if="props.photo.tags && props.photo.tags.length > 0">
          <span 
            v-for="tag in props.photo.tags.slice(0, 2)" 
            :key="tag.id" 
            class="bg-white bg-opacity-20 rounded px-1 text-xs max-w-16 truncate"
          >
            {{ tag.name }}
          </span>
          <span v-if="props.photo.tags.length > 2" class="text-xs opacity-70">+{{ props.photo.tags.length - 2 }}</span>
        </div>

        <!-- 第四行：用户信息和按钮 -->
        <div class="flex items-center justify-between space-x-2 pt-1">
          <div class="flex items-center min-w-0 flex-1" v-if="props.showUserInfo">
            <NAvatar
              class="shrink-0 cursor-pointer z-10"
              round
              :size="16"
              :src="app.getUserAvatar(props.photo.user.avatar_url)"
              @click.stop="toUser"
            />
            <div class="ml-2 min-w-0">
              <a href="javascript:void(0)" @click.stop="toUser" class="text-white text-xs truncate block hover:text-gray-200">{{ props.photo.user.name }}</a>
            </div>
          </div>
          <div class="shrink-0" v-if="props.showLike">
            <NButton
              quaternary
              size="tiny"
              class="z-10 relative"
              :type="props.photo.is_liked ? 'error' : 'default'"
              :render-icon="() => h(NIcon, { size: 12 }, { default: () => h(props.photo.is_liked ? Heart : HeartOutlineIcon)} )"
              @click.stop="like"
            >
              <span class="text-white text-xs hidden sm:inline">{{ $t('Like') }}</span>
            </NButton>
          </div>
        </div>
      </div>
    </div>
  </div>

  <PhotoViewer 
    v-model:show="showViewer"
    :photo="props.photo"
    :show-user-info="props.showUserInfo"
    :show-like="props.showLike"
    :show-report="props.showReport"
  />
</template>