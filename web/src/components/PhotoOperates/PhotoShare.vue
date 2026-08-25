<script setup lang="ts">
import {NButton} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";
import {useI18n} from "vue-i18n";

const { t } = useI18n()
const photoStore = usePhotoStore()
</script>

<template>
  <!-- 分享按钮 -->
  <NButton
    tertiary
    circle
    size="small"
    type="default"
    :title="$t('Share')"
    @click="photoStore.setShareModalActive(true)"
  >
    <template #icon>
      <FontAwesomeIcon icon="fa-solid fa-share-nodes" />
    </template>
  </NButton>
</template>