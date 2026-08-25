<script setup lang="ts">
import {usePhotoSelectorStore} from "@/stores/photo";
import PhotoGroup from "@/components/PhotoSelector/PhotoSelectorGroup.vue";
import { NEmpty, NSpin } from "naive-ui";

const photoSelectorStore = usePhotoSelectorStore()
</script>

<template>
  <NEmpty v-if="! photoSelectorStore.isLoading && photoSelectorStore.groups.length === 0" class="my-10" :description="$t('No photos available')" />
  <div class="space-y-4" v-else>
    <PhotoGroup v-for="group in photoSelectorStore.groups" :group="group"/>
  </div>
  <div v-if="photoSelectorStore.isLoading" class="flex justify-center py-10">
    <NSpin :size="25"/>
  </div>
</template>