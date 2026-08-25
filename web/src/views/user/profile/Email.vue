<script setup lang="ts">
import {type FormInst, NButton, NForm, NFormItem, NInput, NAlert, useMessage} from "naive-ui";
import {useUserStore} from "@/stores/user";
import {ref} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import EmailVerifyInput from "@/components/Form/EmailVerifyInput.vue";
import {postUserBindEmail, postMailSend} from "@/api";
import {useI18n} from "vue-i18n";
import {useConfigStore} from "@/stores/config";
import CaptchaInput from "@/components/Form/CaptchaInput.vue";

const message = useMessage()
const userStore = useUserStore()
const configStore = useConfigStore()
const { t } = useI18n()

const loading = ref(false)
const verifyLoading = ref(false)
const formData = ref<any>({
  email: '',
  code: '',
})
const verifyFormData = ref<any>({
  code: '',
})

// 添加图形验证码相关变量
const captcha = ref('')
const captchaKey = ref('')

const formRef = ref<FormInst | null>(null)
const verifyFormRef = ref<FormInst | null>(null)
const formRules : FormRules = {
  email: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter a new email')
  },
  code: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter the email verification code')
  },
}
const verifyFormRules : FormRules = {
  code: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter the email verification code')
  },
}

const onSubmit = async (e: Event) => {
  e.preventDefault()
  formRef.value?.validate(async (errors: any) => {
    if (!errors) {
      loading.value = true
      const response = await postUserBindEmail({
        body: formData.value
      }).finally(() => loading.value = false)

      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      await userStore.fetchUserProfile()

      Object.keys(formData.value).forEach(key => formData.value[key] = '')
    }

    loading.value = false
  })
}

// 激活邮箱
const onVerifySubmit = async (e: Event) => {
  e.preventDefault()
  verifyFormRef.value?.validate(async (errors: any) => {
    if (!errors) {
      verifyLoading.value = true
      const response = await postUserBindEmail({
        body: {
          email: userStore.profile?.email || '',
          code: verifyFormData.value.code
        }
      }).finally(() => verifyLoading.value = false)

      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      await userStore.fetchUserProfile()
      message.success(t('Email verified successfully'))
      verifyFormData.value.code = ''
    }

    verifyLoading.value = false
  })
}

// 发送邮箱验证码
const emailBtnText = ref(t('Send Email Verification Code'))
const emailBtnDisabled = ref(false)

const startEmailCountdown = () => {
  let s = 60
  emailBtnDisabled.value = true
  const interval = setInterval(() => {
    s--
    emailBtnText.value = t('Resend after {seconds} seconds', {seconds: s})
    if (s <= 0) {
      emailBtnDisabled.value = false
      emailBtnText.value = t('Send Email Verification Code')
      clearInterval(interval)
    }
  }, 1000)
}

const sendEmailVerifyCode = async () => {
  if (!captcha.value) {
    return message.error(t('Please enter captcha'))
  }
  
  if (!captchaKey.value) {
    return message.error(t('Captcha key is missing, please refresh the captcha'))
  }
  
  const result = await postMailSend({
    body: {
      event: 'bind',
      email: userStore.profile?.email || '',
      captcha: captcha.value,
      captcha_key: captchaKey.value,
    }
  })
  if (result.data?.status === 'success') {
    startEmailCountdown()
  } else {
    message.error(result.data!.message)
  }
}
</script>

<template>
  <!-- 未激活状态 -->
  <div v-if="configStore.configs?.app.user_email_verify && !userStore.profile?.email_verified_at && userStore.profile?.email">
    <NForm
      ref="verifyFormRef"
      label-placement="left"
      :label-width="100"
      label-align="left"
      :model="verifyFormData"
      :rules="verifyFormRules"
    >
      <NFormItem>
        <NAlert type="warning" :show-icon="false">
          {{ $t('Your email has not been verified. Please verify your email for account security and to access all features. Click the button below to send a verification code to your current email.') }}
        </NAlert>
      </NFormItem>

      <NFormItem :label="$t('Current Email')">
        <NInput
          :default-value="userStore.profile?.email"
          disabled
        />
      </NFormItem>
      
      <NFormItem :label="$t('Captcha')" show-require-mark>
        <CaptchaInput v-model:captcha="captcha" v-model:captcha-key="captchaKey" size="small" />
      </NFormItem>

      <NFormItem :label="$t('Verification Code')" show-require-mark>
        <NInput
          v-model:value="verifyFormData.code"
          :input-props="{required: true}"
          :placeholder="$t('Please enter the email verification code')"
        >
          <template #suffix>
            <NButton
              text
              type="info"
              @click="sendEmailVerifyCode"
              :disabled="emailBtnDisabled || !captcha"
            >
              {{ emailBtnText }}
            </NButton>
          </template>
        </NInput>
      </NFormItem>

      <NFormItem :show-feedback="false">
        <NButton
          type="primary"
          :loading="verifyLoading"
          @click="onVerifySubmit"
        >{{ $t('Verify Email') }}</NButton>
      </NFormItem>
    </NForm>

    <div class="border-b border-gray-200 dark:border-gray-700 my-6"></div>
  </div>

  <!-- 修改邮箱部分 -->
  <NForm
    ref="formRef"
    label-placement="left"
    :label-width="100"
    label-align="left"
    :model="formData"
    :rules="formRules"
  >
    <NFormItem :label="$t('Current Email')">
      <NInput
        :default-value="userStore.profile?.email"
        disabled
      />
    </NFormItem>

    <NFormItem>
      <NAlert :show-icon="false">{{ $t('To change your email address, please enter the new email in the form below. A verification code will be sent to the new email. Once verified, you can update your email.') }}</NAlert>
    </NFormItem>

    <EmailVerifyInput
      event="bind"
      v-model:email="formData.email"
      v-model:code="formData.code"
      size="medium"
    />

    <NFormItem :show-feedback="false">
      <NButton
        type="primary"
        :loading="loading"
        @click="onSubmit"
      >{{ $t('Confirm Change') }}</NButton>
    </NFormItem>
  </NForm>
</template>