<script setup lang="ts">
import {usePhotoStore} from "@/stores/photo";
import PhotoGroup from "@/components/Photo/PhotoGroup.vue";
import {NEmpty, NSpin} from "naive-ui";

const photoStore = usePhotoStore()
</script>

<template>
  <NEmpty v-if="! photoStore.isLoading && photoStore.groups.length === 0" class="my-10" :description="$t('No photos available')" />
  <PhotoGroup v-else v-for="group in photoStore.groups" :group="group" :key="group.date"/>
  <div v-if="photoStore.isLoading" class="flex justify-center py-10">
    <NSpin :size="25"/>
  </div>
</template>