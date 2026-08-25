<script setup lang="ts">
import {NDrawer, NDrawerContent, NButton} from "naive-ui";
import {ref} from "vue";
import PhotoList from "@/components/PhotoSelector/PhotoSelectorList.vue";
import {type UserPhoto, usePhotoSelectorStore} from "@/stores/photo";
import number from "../../utils/number";
import {useI18n} from "vue-i18n";
import i18n from '@/i18n'

const { t } = useI18n()
const photoSelectorStore = usePhotoSelectorStore()

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: 'Select Photos'
  }
})

const emit = defineEmits<{
  (e: 'onSelect', selections: UserPhoto[]): void
}>()

const active = ref(false)

function open() {
  photoSelectorStore.resetPhotos({
    q: 'sort:latest'
  })
  active.value = true
}

function close() {
  active.value = false
}

function onSelect() {
  emit('onSelect', photoSelectorStore.selections)
  close()
}

defineExpose({
  open,
  close,
})
</script>

<template>
  <NDrawer
    v-model:show="active"
    placement="bottom"
    height="90%"
  >
    <NDrawerContent
      :title="$t(props.title)"
      body-content-class="!py-0 !px-2"
      closable
    >
      <PhotoList />

      <template #footer>
        <NButton secondary strong @click="onSelect">{{ $t('Selection Complete ({count})', {count: number.format(photoSelectorStore.selections.length)}) }}</NButton>
      </template>
    </NDrawerContent>
  </NDrawer>
</template>