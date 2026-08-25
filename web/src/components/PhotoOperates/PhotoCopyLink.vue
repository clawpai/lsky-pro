<script setup lang="ts">
import {NButton, NDropdown, useMessage} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";
import {useI18n} from "vue-i18n";

const photoStore = usePhotoStore()
const message = useMessage()
const { t } = useI18n()

const options = [
  {
    label: 'Url',
    key: 'url'
  },
  {
    label: 'Html',
    key: 'html'
  },
  {
    label: 'BBCode',
    key: 'bbcode'
  },
  {
    label: 'Markdown',
    key: 'markdown'
  },
  {
    label: 'Markdown with link',
    key: 'markdown_with_link'
  },
  {
    label: 'Thumbnail url',
    key: 'thumbnail_url'
  }
]

function handleSelect(key: 'url' | 'html' | 'bbcode' | 'markdown' | 'markdown_with_link' | 'thumbnail_url') {
  // 检查是否有选中的图片
  if (photoStore.selections.length === 0) {
    message.warning(t('Please select photos first'));
    return;
  }
  
  photoStore.copyLink(key).then(() => {
    message.success(t('Copy successful'))
  }).catch(() => {
    message.error(t('Copy failed'))
  })
}
</script>

<template>
  <!-- 复制链接 -->
  <NDropdown
    placement="left-start"
    trigger="hover"
    size="small"
    show-arrow
    :options="options"
    @select="handleSelect"
  >
    <NButton tertiary circle size="small" type="default" :title="$t('Copy Link')">
      <template #icon>
        <FontAwesomeIcon icon="fa-solid fa-copy" />
      </template>
    </NButton>
  </NDropdown>
</template>