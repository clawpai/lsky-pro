<script setup lang="ts">
import {type PropType, ref} from "vue";
import {PhotoAlbum, type RowConstraints} from 'vue-photo-album';
import PhotoSwipeAdapter from "@/components/Photo/Photo.vue";
import usePhotoSwipe from '@/hooks/usePhotoSwipe'
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {type UserPhotoGroup, usePhotoStore} from "@/stores/photo";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const props = defineProps({
  group: {
    type: Object as PropType<UserPhotoGroup>,
    default: () => {},
    required: true,
  },
})

const {toggleSelectAllByGroup} = usePhotoStore()
const { t } = useI18n()

const weeks = [t('Sunday'), t('Monday'), t('Tuesday'), t('Wednesday'), t('Thursday'), t('Friday'), t('Saturday')]
const spacing = ref(4)
const rowHeight = ref(200)

const rowConstraints = (containerWidth: number): RowConstraints => {
  let maxPhotos, singleRowMaxHeight = 250
  if (containerWidth >= 1200) {
    maxPhotos = 12
  } else if (containerWidth >= 600 && containerWidth < 1200) {
    maxPhotos = 8
  } else if (containerWidth >= 300 && containerWidth < 600) {
    maxPhotos = 4
  } else {
    maxPhotos = 3
  }

  return {maxPhotos, singleRowMaxHeight}
}

usePhotoSwipe({ gallery: '.gallery', children: 'a' })
</script>

<template>
  <div class="flex flex-col space-y-2">
    <div class="sticky top-12 z-[3] py-2 bg-white dark:bg-[var(--n-color)] flex space-x-2 items-center overflow-hidden group text-md">
      <FontAwesomeIcon
        :icon="props.group.selected ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'"
        size="xl"
        class="cursor-pointer transition-all select-none"
        :class="props.group.selected ? 'text-violet-500 ml-0.5' : '-ml-6 group-hover:ml-0.5 text-gray-500/70'"
        @click="() => toggleSelectAllByGroup(props.group.date)"
      />
      <p class="font-bold">{{ useDayjs(props.group.date).format('L') }} {{ weeks[useDayjs(props.group.date).day()] }}</p>
    </div>
    <PhotoAlbum
      class="gallery"
      layout="rows"
      :photos="props.group.photos"
      :spacing="spacing"
      :photo-renderer="PhotoSwipeAdapter"
      :row-constraints="rowConstraints"
      :target-row-height="rowHeight"
    />
  </div>
</template>