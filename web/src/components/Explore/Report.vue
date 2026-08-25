<script setup lang="ts">
import {NButton, NForm, NFormItem, NInput, NModal, useMessage} from "naive-ui";
import {type PropType, ref} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {
  postExploreAlbumsByIdReport,
  postExplorePhotosByIdReport,
  postExploreUsersByUsernameReport,
  postSharesBySlugReport
} from "@/api";
import {useI18n} from "vue-i18n";

const { t } = useI18n()

const props = defineProps({
  type: {
    type: String as PropType<'album' | 'photo' | 'user' | 'share'>,
    required: true,
  },
})

const types = {album: t('Album'), photo: t('Photo'), user: t('User'), share: t('Share')}
const message = useMessage()
const showModal = ref(false)
const reportFormRef = ref()
const formData = ref<any>({
  content: '',
})
const formRules: FormRules = {}
const params = ref<any>({})

async function submit() {
  const methods = {
    photo: postExplorePhotosByIdReport,
    album: postExploreAlbumsByIdReport,
    user: postExploreUsersByUsernameReport,
    share: postSharesBySlugReport,
  }

  const response = await methods[props.type]({
    ...{
      body: {
       content: formData.value.content,
      }
    },
    ...params.value,
  })


  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  message.success(t('Report submitted successfully. Thank you for your feedback!'))
  close()
}

function open(data: any) {
  params.value = data

  showModal.value = true
}

function close() {
  showModal.value = false
}

defineExpose({
  open, close,
})
</script>

<template>
  <NModal
    v-model:show="showModal"
    :title="`举报${types[type]}`"
    preset="card"
    size="small"
    :bordered="false"
    class="max-w-screen-sm mx-4 md:mx-auto"
  >
    <div class="flex flex-col space-y-4">
      <NForm ref="reportFormRef" :model="formData" :rules="formRules">
        <NFormItem :label="$t('Please describe the reason for reporting')" path="content">
          <NInput resizable type="textarea" v-model:value="formData.content" />
        </NFormItem>
      </NForm>
    </div>

    <template #action>
      <div class="flex justify-end">
        <NButton tertiary @click="submit">{{ $t('Confirm Report') }}</NButton>
      </div>
    </template>
  </NModal>
</template>