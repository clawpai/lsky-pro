<script setup lang="ts">
import {NButton} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";

const photoStore = usePhotoStore()
</script>

<template>
  <!-- 取消选择 -->
  <NButton tertiary circle size="small" type="default" :title="$t('Deselect')">
    <template #icon>
      <FontAwesomeIcon icon="fa-solid fa-times" @click="photoStore.unselectAll()" />
    </template>
  </NButton>
</template>