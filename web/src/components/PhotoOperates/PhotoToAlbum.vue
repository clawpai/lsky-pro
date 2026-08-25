<script setup lang="ts">
import {NButton, useMessage} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";
import AlbumSelector from "@/components/Album/AlbumSelector.vue";
import type {HomeAlbum} from "@/stores/album";
import {onMounted, ref} from "vue";
import {useI18n} from "vue-i18n";

const photoStore = usePhotoStore()
const albumSelectorRef = ref<InstanceType<typeof AlbumSelector> | null>(null)
const message = useMessage()
const { t } = useI18n()

function openAlbumSelector() {
  albumSelectorRef.value?.open()
}

function onSelect(selections: HomeAlbum[]) {
  photoStore.selectedToAlbum(selections)
    .then(() => {
      message.success(t('Added successfully'))
    })
    .catch((error) => {
      message.error(t('Failed to add to album'))
    })
}

onMounted(() => {
  photoStore.setAlbumSelectorRef(albumSelectorRef.value)
})
</script>

<template>
  <AlbumSelector ref="albumSelectorRef" :title="$t('Add to Album')" @onSelect="onSelect" />

  <!-- 添加到相册 -->
  <NButton tertiary circle size="small" type="default" :title="$t('Add to Album')" @click="openAlbumSelector">
    <template #icon>
      <FontAwesomeIcon icon="fa-solid fa-plus" />
    </template>
  </NButton>
</template>