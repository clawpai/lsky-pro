<script setup lang="ts">
import {type FormInst, type FormItemRule, NButton, NForm, NFormItem, NInput, useMessage} from "naive-ui";
import {ref} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {putUserPassword} from "@/api";
import {useI18n} from "vue-i18n";

const message = useMessage()
const { t } = useI18n()

const loading = ref(false)
const formData = ref<any>({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const formRef = ref<FormInst | null>(null)
const formRules : FormRules = {
  current_password: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter your old password')
  },
  password: {
    type: 'string',
    required: true,
    trigger: ['blur', 'change'],
    message: t('Please enter a new password')
  },
  password_confirmation: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    validator: (rule: FormItemRule, value: string) => {
      return new Promise<void>((resolve, reject) => {
        if (! value) {
          reject(new Error(t('Please confirm your new password')))
        }
        if (value !== formData.value.password) {
          reject(new Error(t('New password and confirmation do not match')))
        } else {
          resolve()
        }
      })
    }
  },
}

const onSubmit = async (e: Event) => {
  e.preventDefault()
  formRef.value?.validate(async (errors: any) => {
    if (!errors) {
      loading.value = true
      const response = await putUserPassword({
        body: formData.value,
      }).finally(() => loading.value = false)

      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      message.success(t('Successfully updated. It will take effect next login.'))

      Object.keys(formData.value).forEach(key => formData.value[key] = '')
    }

    loading.value = false
  })
}
</script>

<template>
  <NForm
    ref="formRef"
    label-placement="left"
    :label-width="100"
    label-align="left"
    :model="formData"
    :rules="formRules"
    @submit.prevent="onSubmit"
  >
    <NFormItem :label="$t('Old Password')" path="current_password">
      <NInput
        type="password"
        :input-props="{type: 'password', required: true}"
        v-model:value="formData.current_password"
        :placeholder="$t('Please enter your old password')"
      />
    </NFormItem>

    <NFormItem :label="$t('New Password')" path="password">
      <NInput
        type="password"
        :minlength="8"
        :input-props="{type: 'password', required: true}"
        v-model:value="formData.password"
        :placeholder="$t('Please enter a new password, at least 8 characters')"
      />
    </NFormItem>

    <NFormItem :label="$t('Confirm New Password')" path="password_confirmation">
      <NInput
        type="password"
        :minlength="8"
        :input-props="{type: 'password', required: true}"
        v-model:value="formData.password_confirmation"
        :placeholder="$t('Please confirm your new password, at least 8 characters')"
      />
    </NFormItem>

    <NFormItem :show-feedback="false">
      <NButton
        attr-type="submit"
        type="primary"
        :loading="loading"
      >{{ $t('Confirm Changes') }}</NButton>
    </NFormItem>
  </NForm>
</template>