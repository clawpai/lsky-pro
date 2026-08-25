<script setup lang="ts">
import {useAlbumStore} from "@/stores/album";
import Album from "@/components/Album/Album.vue";
import {NEmpty, NSpin, useMessage} from "naive-ui";
import Delete from "@/components/AlbumOperates/Delete.vue";
import {ref} from "vue";
import Share from "@/components/AlbumOperates/Share.vue";
import {useRouter} from "vue-router";
import {useI18n} from "vue-i18n";

const albumStore = useAlbumStore()
const message = useMessage()
const router = useRouter()
const { t } = useI18n()
const deleteRef = ref<InstanceType<typeof Delete>>()
const shareRef = ref<InstanceType<typeof Share>>()

function handleSelect(key: string | number, album: any) {
  switch (key) {
    case 'edit':
      router.push(`/user/albums/${album.id}`)
      break
    case 'share':
      shareRef.value?.open(album)
      break
    case 'delete':
      deleteRef.value?.open(album, () => {
        message.success(t('Deleted successfully'))
      })
      break
  }
}
</script>

<template>
  <Delete ref="deleteRef" />
  <Share ref="shareRef" />

  <NEmpty v-if="! albumStore.isLoading && albumStore.albums.length === 0" class="my-10" :description="$t('No albums available')" />
  <div v-else class="w-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-1 p-1">
    <Album
      v-for="album in albumStore.albums"
      :album="album"
      :key="album.id"
      :handle-select="(key: string | number) => handleSelect(key, album)"
    />
  </div>
  <div v-if="albumStore.isLoading" class="flex justify-center py-10">
    <NSpin :size="25"/>
  </div>
</template>