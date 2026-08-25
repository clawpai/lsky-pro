<script setup lang="ts">
import {NButton} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";

const photoStore = usePhotoStore()
</script>

<template>
  <!-- 全选/取消全选 -->
  <NButton tertiary circle size="small" type="default" :title="photoStore.isAllSelected ? $t('Deselect All') : $t('Select All')">
    <template #icon>
      <FontAwesomeIcon :icon="`fa-solid fa-${photoStore.isAllSelected ? 'check-double' : 'check'}`" @click="() => {
        if (photoStore.isAllSelected) {
          photoStore.unselectAll()
        } else {
          photoStore.selectAll()
        }
      }" />
    </template>
  </NButton>
</template>