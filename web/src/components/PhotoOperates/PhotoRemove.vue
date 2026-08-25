<script setup lang="ts">
import {NButton, NPopconfirm, useMessage} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";
import {deleteUserAlbumsByIdPhotos, deleteUserPhotos} from "@/api";
import {useI18n} from "vue-i18n";
import {useUserStore} from "@/stores/user";

const photoStore = usePhotoStore()
const userStore = useUserStore()
const message = useMessage()
const { t } = useI18n()

const props = defineProps({
  albumId: {
    type: Number,
    required: true,
  },
})

function submit() {
  deleteUserAlbumsByIdPhotos({
    path: {
      id: props.albumId,
    },
    body: photoStore.selections.map((photo: any) => photo.id),
  }).then(() => {
    message.success(t('Removed successfully'))
    photoStore.deletePhotos(photoStore.selections.map(photo => photo.key))
    if (userStore.isLoggedIn) {
      userStore.fetchUserProfile()
    }
  })
}
</script>

<template>
  <NPopconfirm
    placement="left-start"
    @positive-click="submit"
  >
    <template #trigger>
      <!-- 从相册中移除选中项 -->
      <NButton quaternary circle size="small" type="error" :title="$t('Remove selected items from album')">
        <template #icon>
          <FontAwesomeIcon icon="fa-regular fa-square-minus" />
        </template>
      </NButton>
    </template>

    {{ $t('Are you sure you want to remove the selected {count} photos from the album?', {count: photoStore.selections.length}) }}
  </NPopconfirm>
</template>