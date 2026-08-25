<script setup lang="ts">
import {NButton, NDatePicker, NForm, NFormItem, NInput, NModal, useMessage} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {ref} from "vue";
import str from "@/utils/str";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {postUserShares} from "@/api";
import {useI18n} from "vue-i18n";

const active = ref(false)
const album = ref<any>({})
const formRef = ref()
const formData = ref<any>({
  type: 'album',
  ids: [],
})
const formRules: FormRules = {}
const url = ref('')
const message = useMessage()
const { t } = useI18n()

function open(data: any) {
  url.value = ''
  album.value = data
  formData.value.ids = [album.value.id]
  active.value = true
}

function submit() {
  postUserShares({
    body: formData.value,
  }).then(response => {
    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    url.value = `${window.location.origin}/shares/${response.data?.data.slug}`
  })
}

defineExpose({open})
</script>

<template>
  <NModal
    v-model:show="active"
    :title="$t('Share Album')"
    preset="card"
    size="small"
    :bordered="false"
    class="max-w-screen-sm mx-4 md:mx-auto"
  >
    <div class="flex flex-col space-y-4">
      <NForm ref="formRef" :model="formData" :rules="formRules" :disabled="Boolean(url)">
        <NFormItem :label="$t('Share Description')" path="content">
          <NInput resizable type="textarea" v-model:value="formData.content" />
        </NFormItem>
        <NFormItem :label="$t('Password')" path="password">
          <NInput type="password" v-model:value="formData.password" />
        </NFormItem>
        <NFormItem :label="$t('Expiration Time')" path="password">
          <NDatePicker
            v-model:formatted-value="formData.expired_at"
            type="datetime"
            class="w-full"
            :placeholder="$t('Select Expiration Time')"
            :is-date-disabled="(timestamp: number) => timestamp < Date.now()"
            value-format="yyyy-MM-dd HH:mm:ss"
            clearable
          />
        </NFormItem>
      </NForm>
    </div>

    <template #action>
      <div class="flex justify-end" v-if="! url">
        <NButton tertiary @click="submit">{{ $t('Create Share Link') }}</NButton>
      </div>
      <div class="flex justify-between space-x-2" v-else>
        <NInput class="grow select-all" :default-value="url" readonly />
        <NButton tertiary type="primary" class="shrink-0" @click="() => {
          str.copyText(url).then(() => {
            message.success(t('Copy successful'))
          }).catch(() => {
            message.error(t('Copy failed'))
          })
        }">
          <template #icon>
            <FontAwesomeIcon icon="fa-solid fa-copy" />
          </template>
        </NButton>
      </div>
    </template>
  </NModal>
</template>