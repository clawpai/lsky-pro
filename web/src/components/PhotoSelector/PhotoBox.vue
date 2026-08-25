<script setup lang="ts">
import {computed} from 'vue'
import {type PhotoRendererMetadata} from 'vue-photo-album'
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {type UserPhoto, usePhotoSelectorStore} from "@/stores/photo";

const emit = defineEmits(['onToggleSelect'])

const props = defineProps<PhotoRendererMetadata>()
const photo = computed<UserPhoto & any>(() => props.photo.srcSet?.[0] ?? props.photo)
const {toggleSelect} = usePhotoSelectorStore()
</script>

<template>
  <div
    class="relative group bg-gray-200 dark:bg-gray-700"
    @click="() => toggleSelect(photo.key)"
  >
    <div class="transition-all absolute w-full top-0 h-1/2 bg-gradient-to-b from-black/30 opacity-0 group-hover:opacity-100"></div>

    <div class="absolute inset-0 flex justify-between z-[2] select-none">
      <FontAwesomeIcon
        class="rounded-full cursor-pointer absolute top-2 left-2"
        :class="photo.selected ? 'text-violet-500 bg-white outline outline-1 outline-white dark:outline-gray-500' : 'hidden group-hover:block text-white/70 hover:text-white'"
        icon="fa-solid fa-circle-check"
        size="xl"
        @click.prevent.stop="() => toggleSelect(photo.key)"
      />
    </div>

    <div class="overflow-hidden transition-all" :class="photo.selected ? 'scale-90 rounded-lg' : ''">
      <slot />
    </div>
  </div>
</template>