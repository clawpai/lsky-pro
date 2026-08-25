<script setup lang="ts">
import {ref, watch} from "vue";
import {type HomeAlbum, useAlbumSelectorStore} from "@/stores/album";
import {NModal, NInfiniteScroll, NInput, NSpin, NEmpty} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {debounce} from "lodash";
import i18n from '@/i18n'
import {useI18n} from "vue-i18n";

const albumSelectorStore = useAlbumSelectorStore()
const { t } = useI18n()

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: 'Select Album'
  }
})

const emit = defineEmits<{
  (e: 'onSelect', selections: HomeAlbum[]): void
}>()

const active = ref(false)
const q = ref('')

function open() {
  albumSelectorStore.resetAlbums()
  active.value = true
}

function close() {
  active.value = false
}

function onSelect(album: HomeAlbum) {
  albumSelectorStore.toggleSelect(album.id?.toString() || '')
  emit('onSelect', albumSelectorStore.selections)
  close()
}

function handleLoad() {
  if (! albumSelectorStore.isLoading) {
    albumSelectorStore.setPage(albumSelectorStore.page + 1)
    albumSelectorStore.fetchAlbums()
  }
}

watch(() => q.value, debounce(() => {
  albumSelectorStore.resetAlbums({q})
}, 500))

defineExpose({
  open,
  close,
})
</script>

<template>
  <NModal
    v-model:show="active"
    :title="props.title"
    preset="card"
    size="small"
    :bordered="false"
    class="container m-4 md:mx-auto md:my-10 max-w-screen-md overflow-hidden"
  >
    <div class="flex flex-col space-y-4">
      <div class="flex justify-between w-full border-b pb-2 dark:border-b-gray-600">
        <NInput :placeholder="$t('Search Albums')" :bordered="false" v-model:value="q"/>
      </div>
      <NEmpty v-if="! albumSelectorStore.isLoading && albumSelectorStore.albums.length === 0" class="py-8" :description="$t('No albums available')" />
      <NInfiniteScroll class="max-h-[400px]" @load="handleLoad">
        <div
          class="flex p-2 mb-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800"
          v-for="album in albumSelectorStore.albums"
          @click="onSelect(album)"
        >
          <div class="h-16 w-16 rounded-lg overflow-hidden shrink-0">
            <img
              v-if="album.covers && album.covers.length > 0"
              class="rounded-lg w-full h-full object-cover"
              alt="album cover"
              :src="album.covers[0]"
            />
            <div v-else class="flex grow items-center justify-center h-full bg-gray-100 dark:bg-gray-700">
              <FontAwesomeIcon icon="fa-solid fa-images" class="text-gray-500" size="xl" />
            </div>
          </div>
          <div class="flex flex-col justify-center ml-4 overflow-hidden">
            <div class="truncate text-lg" v-if="album.name">{{ album.name }}</div>
            <div class="truncate text-gray-500">{{ $t('{count} items', {count: album.photo_count}) }}</div>
          </div>
        </div>

        <div v-if="albumSelectorStore.isLoading" class="flex justify-center py-10">
          <NSpin :size="25"/>
        </div>
      </NInfiniteScroll>
    </div>
  </NModal>
</template>