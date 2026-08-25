<script setup lang="ts">
import number from "../../utils/number";
import EmptyImage from "../../assets/empty-image.png"

const props = defineProps({
  album: {
    type: Object,
    required: true,
    default: () => {},
  }
})

function open(path: string) {
  window.open(path, "_blank")
}
</script>

<template>
  <div class="flex flex-col w-full hover:opacity-75 transition-all cursor-pointer" @click="() => open(`/explore/albums/${props.album.id}`)">
    <div class="flex overflow-hidden rounded-md h-[200px]">
      <div class="flex basis-2/3 border-r shrink-0">
        <img :src="props.album.covers[0] || EmptyImage" alt="Album Cover" class="w-full h-full object-cover">
      </div>
      <div class="flex basis-1/3 flex-col">
        <img :src="props.album.covers[1] || EmptyImage" alt="Album Cover" class="w-full h-full border-b object-cover shrink-0 overflow-hidden basis-1/2">
        <img :src="props.album.covers[2] || EmptyImage" alt="Album Cover" class="w-full h-full object-cover shrink-0 overflow-hidden basis-1/2">
      </div>
    </div>
    <div class="flex flex-col w-full mt-4 space-y-2">
      <p class="text-base truncate">{{ props.album.name }}</p>
      <p class="text-md text-gray-500 dark:text-gray-400">{{ $t('{count} photos', {count: number.format(props.album.photo_count || 0)}) }} 张照片 · Curated by {{ props.album.user.name }}</p>
    </div>
  </div>
</template>