<script setup lang="ts">
// 图片组件，支持以下快捷键功能：
// 1. Ctrl/Command(⌘)+点击可以多选图片
// 2. Shift+点击可以连续选择图片
// 3. 在管理页面中按Ctrl/Command(⌘)+A可以全选图片
import {type Component, computed, h, nextTick, ref} from 'vue'
import {type PhotoRendererMetadata} from 'vue-photo-album'
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {type UserPhoto, usePhotoStore} from "@/stores/photo";
import {NDropdown, NIcon, useDialog, useMessage} from "naive-ui";
import type {DropdownMixedOption} from "naive-ui/es/dropdown/src/interface";
import {AlbumsOutline, CopyOutline, OpenOutline, ShareOutline} from "@vicons/ionicons5";
import {DeleteOutlined, EditOutlined} from "@vicons/antd";
import {useI18n} from "vue-i18n";
import {useRoute} from "vue-router";
import {deleteUserAlbumsByIdPhotos} from "@/api";
import {useUserStore} from "@/stores/user";

const emit = defineEmits(['onToggleSelect'])

const props = defineProps<PhotoRendererMetadata>()
const userStore = useUserStore()
const photo = computed<UserPhoto & any>(() => {
  if (userStore.profile?.options?.show_original_photos || !props.photo.srcSet?.length) {
    return props.photo
  }
  return props.photo.srcSet[0]
})
const photoStore = usePhotoStore()
const { t } = useI18n()
const message = useMessage()
const route = useRoute()
const isInAlbumDetail = computed(() => route.path.includes('/user/albums/'))

// 存储最后选择的图片key，用于Shift+点击连续选择
const lastSelectedPhotoKey = ref<string | null>(null)

const imageProps = computed(() => {
  return {
    srcset: props.photo.srcSet ? 
      props.photo.srcSet.map(img => `${img.src} ${img.width}w`).join(', ') : 
      null
  }
})

// 处理图片选择，支持Ctrl/Command和Shift键
function handlePhotoSelect(event: MouseEvent) {
  event.preventDefault();
  event.stopPropagation();
  
  // 获取当前图片key
  const currentKey = photo.value.key;
  
  // Ctrl键或Command键多选 (Windows/Linux 使用 ctrlKey, macOS 使用 metaKey)
  if (event.ctrlKey || event.metaKey) {
    photoStore.toggleSelect(currentKey);
    lastSelectedPhotoKey.value = currentKey;
    return;
  }
  
  // Shift键连续选择
  if (event.shiftKey && lastSelectedPhotoKey.value) {
    selectPhotoRange(lastSelectedPhotoKey.value, currentKey);
    return;
  }
  
  photoStore.toggleSelect(currentKey);
  lastSelectedPhotoKey.value = currentKey;
}

// 选择一个范围内的图片
function selectPhotoRange(startKey: string, endKey: string) {
  // 先寻找开始和结束的图片在哪个组和索引
  let startGroupIndex = -1, startPhotoIndex = -1;
  let endGroupIndex = -1, endPhotoIndex = -1;
  
  const groups = photoStore.groups;
  
  // 查找起始和结束图片的位置
  for (let i = 0; i < groups.length; i++) {
    const group = groups[i];
    for (let j = 0; j < group.photos.length; j++) {
      if (group.photos[j].key === startKey) {
        startGroupIndex = i;
        startPhotoIndex = j;
      }
      if (group.photos[j].key === endKey) {
        endGroupIndex = i;
        endPhotoIndex = j;
      }
    }
  }
  
  // 如果找不到起始或结束位置，则退出
  if (startGroupIndex === -1 || endGroupIndex === -1) return;
  
  // 确保start在end之前
  if (startGroupIndex > endGroupIndex || (startGroupIndex === endGroupIndex && startPhotoIndex > endPhotoIndex)) {
    // 交换开始和结束位置
    [startGroupIndex, endGroupIndex] = [endGroupIndex, startGroupIndex];
    [startPhotoIndex, endPhotoIndex] = [endPhotoIndex, startPhotoIndex];
  }
  
  // 选择范围内的所有图片
  for (let i = startGroupIndex; i <= endGroupIndex; i++) {
    const group = groups[i];
    const start = i === startGroupIndex ? startPhotoIndex : 0;
    const end = i === endGroupIndex ? endPhotoIndex : group.photos.length - 1;
    
    for (let j = start; j <= end; j++) {
      const photoKey = group.photos[j].key;
      // 如果图片没有被选中，则选中它
      if (!group.photos[j].selected) {
        photoStore.toggleSelect(photoKey);
      }
    }
  }
}

function renderIcon(icon: Component) {
  return () => h(NIcon, null, {default: () => h(icon)})
}

const options = computed(() => <DropdownMixedOption[]>[
  {
    label: t('Copy Image'),
    key: 'copy',
    disabled: photoStore.selections.length > 1,
    icon: renderIcon(CopyOutline)
  },
  {
    label: t('Copy Link'),
    key: 'copy-urls',
    icon: renderIcon(CopyOutline),
    children: [
      {
        label: 'Url',
        key: 'copy-url',
      },
      {
        label: 'Html',
        key: 'copy-html'
      },
      {
        label: 'BBCode',
        key: 'copy-bbcode',
      },
      {
        label: 'Markdown',
        key: 'copy-markdown'
      },
      {
        label: 'Markdown with link',
        key: 'copy-markdown_with_link'
      },
      {
        label: 'Thumbnail url',
        key: 'copy-thumbnail_url'
      },
    ],
  },
  {
    label: t('Open in New Window'),
    key: 'open',
    disabled: photoStore.selections.length > 1,
    icon: renderIcon(OpenOutline)
  },
  {
    label: t('Edit Information'),
    key: 'edit',
    icon: renderIcon(EditOutlined)
  },
  {
    label: t('Share'),
    key: 'share',
    icon: renderIcon(ShareOutline)
  },
  ...(isInAlbumDetail.value 
    ? [{
        label: t('Remove from Album'),
        key: 'remove-from-album',
        icon: renderIcon(AlbumsOutline)
      }] 
    : [{
        label: t('Add to Album'),
        key: 'to-album',
        icon: renderIcon(AlbumsOutline)
      }]
  ),
  {
    type: 'divider',
    key: 'd1'
  },
  {
    label: t('Delete'),
    key: 'delete',
    icon: renderIcon(DeleteOutlined)
  },
])

const showDropdownRef = ref(false)
const xRef = ref(0)
const yRef = ref(0)
const dialog = useDialog()

function handleSelect(key: string | number) {
  showDropdownRef.value = false
  const name = String(key)
  switch (name) {
    case 'copy':
      photoStore.copyImage(photo.value.src)
        .then(() => {
          message.success(t('Copy successful'))
        })
        .catch((error) => {
          console.error('复制图片失败:', error)
          message.error(t('Copy failed'))
        })
      break;
    case 'copy-url':
    case 'copy-html':
    case 'copy-bbcode':
    case 'copy-markdown':
    case 'copy-markdown_with_link':
    case 'copy-thumbnail_url':
      photoStore.copyLink(name.replace('copy-', '') as 'url' | 'html' | 'bbcode' | 'markdown' | 'markdown_with_link' | 'thumbnail_url').then(() => {
        message.success(t('Copy successful'))
      }).catch(() => {
        message.error(t('Copy failed'))
      })
      break;
    case 'open':
      photoStore.openPhoto(photo.value)
      break;
    case 'edit':
      photoStore.setEditModalActive(true)
      break;
    case 'share':
      photoStore.setShareModalActive(true)
      break;
    case 'to-album':
      photoStore.albumSelectorRef?.open()
      break;
    case 'remove-from-album':
      const albumId = Number(route.params.id)
      if (albumId) {
        const d = dialog.warning({
          title: t('Warning'),
          content: t('Are you sure you want to remove the selected {count} photos from the album?', {count: photoStore.selections.length}),
          positiveText: t('Confirm'),
          negativeText: t('Cancel'),
          onPositiveClick: () => {
            d.loading = true
            return deleteUserAlbumsByIdPhotos({
              path: {
                id: albumId,
              },
              body: photoStore.selections.map((photo: any) => photo.id),
            }).then(() => {
              message.success(t('Removed successfully'))
              photoStore.deletePhotos(photoStore.selections.map(photo => photo.key))
              const userStore = useUserStore()
              if (userStore.isLoggedIn) {
                userStore.fetchUserProfile()
              }
            })
          },
        })
      }
      break;
    case 'delete':
      const d = dialog.warning({
        title: t('Warning'),
        content: t('Are you sure you want to delete the selected {count} photos?', {count: photoStore.selections.length}),
        positiveText: t('Confirm'),
        negativeText: t('Cancel'),
        onPositiveClick: () => {
          d.loading = true
          return photoStore.deleteSelectedPhotos().then(() => {
            message.success(t('Deleted successfully'))
          })
        },
      })
  }
}

function handleContextMenu(e: MouseEvent) {
  e.preventDefault()
  showDropdownRef.value = false
  nextTick().then(() => {
    showDropdownRef.value = true
    xRef.value = e.clientX
    yRef.value = e.clientY
  })

  if (! photo.value.selected) {
    photoStore.unselectAll()
    setTimeout(() => {
      photoStore.toggleSelect(photo.value.key)
    }, 10)
  }
}

function onClickOutside() {
  showDropdownRef.value = false
}
</script>

<template>
  <a
    :href="photo.public_url || photo.src"
    :data-pswp-width="photo.width"
    :data-pswp-height="photo.height"
    :data-pswp-srcset="imageProps.srcset"
    target="_blank"
    rel="noopener noreferrer"
    @contextmenu="handleContextMenu"
    @click.prevent="(e) => (e.ctrlKey || e.metaKey || e.shiftKey) ? handlePhotoSelect(e) : null"
  >
    <NDropdown
      placement="bottom-start"
      trigger="manual"
      :x="xRef"
      :y="yRef"
      :options="options"
      :show="showDropdownRef"
      :on-clickoutside="onClickOutside"
      @select="handleSelect"
    />

    <div class="relative group bg-gray-200 dark:bg-gray-700">
      <div class="transition-all absolute w-full top-0 h-1/2 bg-gradient-to-b from-black/30 opacity-0 group-hover:opacity-100"></div>

      <div class="absolute inset-0 flex justify-between z-[2] select-none">
      <FontAwesomeIcon
          class="rounded-full cursor-pointer absolute top-2 left-2"
          :class="photo.selected ? 'text-violet-500 bg-white outline outline-1 outline-white dark:outline-gray-500' : 'hidden group-hover:block text-white/70 hover:text-white'"
          icon="fa-solid fa-circle-check"
          size="xl"
          @click.prevent.stop="handlePhotoSelect"
        />

        <div class="absolute top-2 right-2">
          <FontAwesomeIcon v-if="photo.is_public" class="text-red-500" icon="fa-solid fa-eye" :title="$t('Public')" />
        </div>
      </div>

      <div class="overflow-hidden transition-all" :class="photo.selected ? 'scale-90 rounded-lg' : ''">
        <slot />
      </div>
    </div>
  </a>
</template>