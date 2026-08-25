<script setup lang="ts">
import {NButton, NPopconfirm, useMessage} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";
import {useI18n} from "vue-i18n";

const photoStore = usePhotoStore()
const message = useMessage()
const {t} = useI18n()

function submit() {
  photoStore.deleteSelectedPhotos().then(() => {
    message.success(t('Deleted successfully'))
  })
}
</script>

<template>
  <NPopconfirm
    placement="left-start"
    @positive-click="submit"
  >
    <template #trigger>
      <!-- 删除选择项 -->
      <NButton quaternary circle size="small" type="error" :title="$t('Delete Selected Items')">
        <template #icon>
          <FontAwesomeIcon icon="fa-solid fa-trash-alt" />
        </template>
      </NButton>
    </template>

    {{ $t('Are you sure you want to delete the selected {count} photos?', {count: photoStore.selections.length}) }}
  </NPopconfirm>
</template>