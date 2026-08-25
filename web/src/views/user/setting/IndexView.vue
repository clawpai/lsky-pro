<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import {
  NCard,
  NSelect,
  NThing,
  NSwitch,
} from "naive-ui";
import {ref, watch, computed} from "vue";
import type {SelectMixedOption} from "naive-ui/es/select/src/interface";
import {postUserSetting} from "@/api";
import {useUserStore} from "@/stores/user";
import {useLocaleStore} from "@/stores/locale";
import {useLayoutStore} from "@/stores/layout";
import {useConfigStore} from "@/stores/config";
import {useI18n} from "vue-i18n";

const layoutStore = useLayoutStore()
const userStore = useUserStore()
const localeStore = useLocaleStore()
const configStore = useConfigStore()
const { t } = useI18n()
const formData = ref({
  ...{
    language: 'zh-CN',
    show_original_photos: false,
    encode_copied_url: false,
    auto_upload_after_select: false,
    upload_button_action: 'select_files',
    default_storage_id: null,
  },
  ...(userStore.profile?.options || {})
})

const languages: SelectMixedOption[] = localeStore.getLocales()

// 获取可用的储存选项，处理储存被删除的情况
const storageOptions = computed((): SelectMixedOption[] => {
  const storages = configStore.group?.storages || []
  return [
    { label: t('None (follow global settings)'), value: null },
    ...storages.map(storage => ({
      label: storage.name,
      value: storage.id
    }))
  ] as SelectMixedOption[]
})

// 检查当前选择的储存是否还存在
const isCurrentStorageValid = computed(() => {
  if (!formData.value.default_storage_id) return true
  const storages = configStore.group?.storages || []
  return storages.some(storage => storage.id === formData.value.default_storage_id)
})

watch(formData, (value) => {
  postUserSetting({
    body: value as any,
  }).then(() => {
    userStore.setOptions(formData.value)
  })
}, {
  deep: true,
})

watch(() => formData.value.language, (value) => {
  localeStore.setLocale(value)
  layoutStore.refresh()
})

// 监听储存变化，如果当前选择的储存被删除，自动重置为null
watch(() => configStore.group?.storages, () => {
  if (!isCurrentStorageValid.value) {
    formData.value.default_storage_id = null
  }
}, { deep: true })

</script>

<template>
  <Layout
    :header-title="$t('Settings')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NCard content-class="space-y-6">
        <NThing>
          <template #header>
            {{ $t('System Language') }}
          </template>
          <template #description>
            {{ $t('Choose your language preference') }}
          </template>
          <template #header-extra>
            <NSelect class="min-w-40 md:min-w-60" v-model:value="formData.language" :options="languages" />
          </template>
        </NThing>

        <NThing>
          <template #header>
            {{ $t('Show original photos') }}
          </template>
          <template #description>
            {{ $t('Priority is given to the original file rather than thumbnails when viewing images. This may cause the photos to appear slower.') }}
          </template>
          <template #header-extra>
            <NSwitch v-model:value="formData.show_original_photos" />
          </template>
        </NThing>

        <NThing>
          <template #header>
            {{ $t('Encode URL when copying') }}
          </template>
          <template #description>
            {{ $t('When copying an image URL, automatically encode special characters to ensure compatibility across different platforms.') }}
          </template>
          <template #header-extra>
            <NSwitch v-model:value="formData.encode_copied_url" />
          </template>
        </NThing>

        <NThing>
          <template #header>
            {{ $t('Auto upload after selecting images') }}
          </template>
          <template #description>
            {{ $t('Automatically start uploading all images immediately after selection.') }}
          </template>
          <template #header-extra>
            <NSwitch v-model:value="formData.auto_upload_after_select" />
          </template>
        </NThing>

        <NThing>
          <template #header>
            {{ $t('Upload button behavior') }}
          </template>
          <template #description>
            {{ $t('Choose what happens when you click the upload button in the header. Select files to open file selector, or open queue to show upload queue.') }}
          </template>
          <template #header-extra>
            <NSelect 
              class="min-w-40 md:min-w-60" 
              v-model:value="formData.upload_button_action" 
              :options="[
                { label: $t('Select files'), value: 'select_files' },
                { label: $t('Open upload queue'), value: 'open_queue' }
              ]" 
            />
          </template>
        </NThing>

        <NThing>
          <template #header>
            {{ $t('Default Storage') }}
          </template>
          <template #description>
            {{ $t('Choose the default storage location for uploading images. If not set, it will follow the global settings.') }}
          </template>
          <template #header-extra>
            <NSelect 
              class="min-w-40 md:min-w-60" 
              v-model:value="formData.default_storage_id" 
              :options="storageOptions"
              filterable
              clearable
            />
          </template>
        </NThing>
      </NCard>
    </Content>
  </Layout>
</template>