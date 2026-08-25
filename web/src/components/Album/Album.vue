<script setup lang="ts">
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {NButton, NDropdown, NIcon} from "naive-ui";
import {h, type Component, type PropType} from "vue";
import {DeleteOutlined, EditOutlined, ShareAltOutlined} from "@vicons/antd";
import type {DropdownMixedOption} from "naive-ui/es/dropdown/src/interface";
import {useRouter} from "vue-router";
import type {GetUserAlbumsResponse} from "@/api";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const props = defineProps({
  album: {
    type: Object as () => GetUserAlbumsResponse['data']['data'][number],
    required: true
  },
  handleSelect: {
    type: Function as PropType<(key: string | number) => void>,
    default: () => {}
  }
})

const router = useRouter()
const { t } = useI18n()

function renderIcon(icon: Component) {
  return () => {
    return h(NIcon, null, {
      default: () => h(icon)
    })
  }
}

const options: DropdownMixedOption[] = [
  {
    key: 'edit',
    label: t('Edit Album'),
    icon: renderIcon(EditOutlined)
  },
  {
    key: 'share',
    label: t('Share'),
    icon: renderIcon(ShareAltOutlined)
  },
  {
    key: 'delete',
    label: t('Delete'),
    icon: renderIcon(DeleteOutlined),
  },
]
</script>

<template>
  <div class="relative aspect-square cursor-pointer group overflow-hidden bg-gray-200 dark:bg-gray-800" @click="() => {
    router.push(`/user/albums/${props.album.id}`)
  }">
    <!-- 相册封面图片 -->
    <img
      v-if="props.album.covers && props.album.covers.length > 0"
      class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
      alt="album cover"
      :src="props.album.covers[0]"
    />
    <div v-else class="flex items-center justify-center w-full h-full bg-gray-200 dark:bg-gray-700">
      <FontAwesomeIcon icon="fa-solid fa-images" class="text-gray-400 dark:text-gray-500" size="3x" />
    </div>

    <!-- 遮罩层 -->
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>

    <!-- 操作按钮 -->
    <NDropdown
      size="large"
      placement="bottom-start"
      trigger="click"
      :options="options"
      @select="props.handleSelect"
      show-arrow
    >
      <NButton
        tertiary
        circle
        size="small"
        type="default"
        :title="$t('Show Album Options')"
        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black bg-opacity-50 text-white border-none hover:bg-opacity-70"
        @click.stop="() => {}"
      >
        <template #icon>
          <FontAwesomeIcon icon="fa-solid fa-ellipsis-vertical" class="text-white"/>
        </template>
      </NButton>
    </NDropdown>

    <!-- 信息叠加层 -->
    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-black/70 to-transparent p-3">
      <p v-if="props.album.name" class="text-white font-medium truncate text-sm mb-1">
        {{ props.album.name }}
      </p>
      <div class="flex justify-between items-center text-white/80 text-xs">
        <span>{{ $t('{count} items', {count: props.album.photo_count}) }}</span>
        <span>{{ useDayjs(props.album.created_at).format('L') }}</span>
      </div>
    </div>
  </div>
</template>