<script setup lang="ts">
import {ref} from "vue";
import {useDialog} from "naive-ui";
import {deleteUserAlbumsById} from "@/api";
import {useAlbumStore} from "@/stores/album";
import {useI18n} from "vue-i18n";

const albumStore = useAlbumStore()
const album = ref<any>({})
const dialog = useDialog()
const { t } = useI18n()

function open(data: any, callback = () => {}) {
  album.value = data

  const d = dialog.warning({
    title: t('Delete Album'),
    content: t('Are you sure you want to delete this album?'),
    positiveText: t('Confirm'),
    negativeText: t('Cancel'),
    onPositiveClick: () => {
      d.loading = true
      return new Promise((resolve) => {
        deleteUserAlbumsById({
          path: {
            id: album.value.id,
          },
        }).then(() => {
          albumStore.deleteAlbums([album.value.id])
          callback()
          resolve(true)
        }).finally(() => {
          d.loading = false
        })
      })
    },
  })
}

defineExpose({open})
</script>

<template>

</template>