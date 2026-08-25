<script setup lang="ts">
import {usePhotoStore} from "@/stores/photo";

const photoStore = usePhotoStore()
</script>

<template>
  <div
    :key="photoStore.selections.length"
    class="flex flex-col items-center absolute p-2 md:p-3 my-2 z-[3] bg-white/95 dark:bg-black/80 border border-transparent dark:border-slate-400 transition-all rounded-2xl shadow-md space-y-2 md:space-y-3"
    :class="photoStore.selections.length > 0 ? 'right-2 md:right-6' : '-right-20'"
  >
    <slot/>
  </div>
</template>