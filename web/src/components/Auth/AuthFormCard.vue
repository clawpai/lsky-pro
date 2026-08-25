<script setup lang="ts">
import {NCard} from "naive-ui";
import {type CSSProperties, type PropType, ref, type VNodeChild} from "vue";
import {useConfigStore} from "@/stores/config";
import arr from "@/utils/arr";
import bgImg from '@/assets/bg.jpg'

const props = defineProps({
  title: {
    type: String,
    default: () => ''
  }
})

const slots = defineSlots<{
  cover: PropType<() => VNodeChild>,
  content: PropType<string | (() => VNodeChild)>,
  footer: PropType<() => VNodeChild>,
  action: PropType<() => VNodeChild>,
  header: PropType<() => VNodeChild>,
  'header-extra': PropType<() => VNodeChild>,
}>()

const configStore = useConfigStore()
const bgStyle = ref<CSSProperties>()

if (configStore.configs?.site.auth_page_background_image_url) {
  bgStyle.value = {backgroundImage: `url("${configStore.configs?.site.auth_page_background_image_url}")`}
} else if (configStore.configs?.site.auth_page_background_images && configStore.configs?.site.auth_page_background_images.length > 0) {
  const imageUrl = arr.randomItem(configStore.configs?.site.auth_page_background_images)
  bgStyle.value = {backgroundImage: `url("${imageUrl}")`}
} else {
  bgStyle.value = {backgroundImage: `url("${bgImg}")`}
}

</script>

<template>
  <div class="min-h-full flex py-20 bg-gray-100 dark:bg-slate-800 items-center w-full bg-center bg-cover" :style="bgStyle">
    <div class="container mx-auto max-w-screen-sm px-4 md:px-20">
      <NCard class="rounded-xl shadow-black" :title="props.title">
        <template v-for="(slot, name) in slots" #[name]>
          <slot :name="name" />
        </template>
      </NCard>
    </div>
  </div>
</template>