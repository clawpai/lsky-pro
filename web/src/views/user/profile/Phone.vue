<script setup lang="ts">
import {type FormInst, NButton, NForm, NFormItem, NInput, NAlert, useMessage} from "naive-ui";
import {useUserStore} from "@/stores/user";
import {ref} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {postUserBindPhone, postSmsSend} from "@/api";
import PhoneVerifyInput from "@/components/Form/PhoneVerifyInput.vue";
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
  country_code: 'cn',
  phone: '',
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
  phone: {
    type: 'string',
    required: true,
    trigger: ['blur', 'change'],
    message: t('Please enter a new mobile number')
  },
  code: {
    type: 'string',
    required: true,
    trigger: ['blur', 'change'],
    message: t('Please enter the mobile verification code')
  },
}
const verifyFormRules : FormRules = {
  code: {
    type: 'string',
    required: true,
    trigger: ['blur', 'change'],
    message: t('Please enter the mobile verification code')
  },
}

// 发送手机验证码
const phoneBtnText = ref(t('Send SMS Verification Code'))
const phoneBtnDisabled = ref(false)

const startPhoneCountdown = () => {
  let s = 60
  phoneBtnDisabled.value = true
  const interval = setInterval(() => {
    s--
    phoneBtnText.value = t('Resend after {seconds} seconds', {seconds: s})
    if (s <= 0) {
      phoneBtnDisabled.value = false
      phoneBtnText.value = t('Send SMS Verification Code')
      clearInterval(interval)
    }
  }, 1000)
}

const sendPhoneVerifyCode = async () => {
  if (!captcha.value) {
    return message.error(t('Please enter captcha'))
  }
  
  if (!captchaKey.value) {
    return message.error(t('Captcha key is missing, please refresh the captcha'))
  }
  
  const result = await postSmsSend({
    body: {
      event: 'bind',
      country_code: userStore.profile?.country_code || 'cn',
      phone: userStore.profile?.phone || '',
      captcha: captcha.value,
      captcha_key: captchaKey.value,
    }
  })
  if (result.data?.status === 'success') {
    startPhoneCountdown()
  } else {
    message.error(result.data!.message)
  }
}

const onSubmit = async (e: Event) => {
  e.preventDefault()
  formRef.value?.validate(async (errors: any) => {
    if (!errors) {
      loading.value = true
      const response = await postUserBindPhone({
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

// 激活手机号
const onVerifySubmit = async (e: Event) => {
  e.preventDefault()
  verifyFormRef.value?.validate(async (errors: any) => {
    if (!errors) {
      verifyLoading.value = true
      const response = await postUserBindPhone({
        body: {
          country_code: userStore.profile?.country_code || 'cn',
          phone: userStore.profile?.phone || '',
          code: verifyFormData.value.code
        }
      }).finally(() => verifyLoading.value = false)

      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      await userStore.fetchUserProfile()
      message.success(t('Phone verified successfully'))
      verifyFormData.value.code = ''
    }

    verifyLoading.value = false
  })
}
</script>

<template>
  <!-- 未激活状态 -->
  <div v-if="configStore.configs?.app.user_phone_verify && userStore.profile?.phone && !userStore.profile?.phone_verified_at">
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
          {{ $t('Your phone number has not been verified. Please verify your phone number for account security and to access all features. Click the button below to send a verification code to your current phone number.') }}
        </NAlert>
      </NFormItem>

      <NFormItem :label="$t('Current Mobile Number')">
        <NInput
          :default-value="userStore.profile?.phone"
          disabled
        />
      </NFormItem>
      
      <NFormItem :label="$t('Captcha')" show-require-mark>
        <CaptchaInput v-model:captcha="captcha" v-model:captcha-key="captchaKey" />
      </NFormItem>

      <NFormItem :label="$t('Verification Code')" show-require-mark>
        <NInput
          v-model:value="verifyFormData.code"
          :input-props="{required: true}"
          :placeholder="$t('Please enter the SMS verification code')"
        >
          <template #suffix>
            <NButton
              text
              type="info"
              @click="sendPhoneVerifyCode"
              :disabled="phoneBtnDisabled || !captcha"
            >
              {{ phoneBtnText }}
            </NButton>
          </template>
        </NInput>
      </NFormItem>

      <NFormItem :show-feedback="false">
        <NButton
          type="primary"
          :loading="verifyLoading"
          @click="onVerifySubmit"
        >{{ $t('Verify Phone') }}</NButton>
      </NFormItem>
    </NForm>

    <div class="border-b border-gray-200 dark:border-gray-700 my-6"></div>
  </div>

  <!-- 修改/绑定手机号部分 -->
  <NForm
    ref="formRef"
    label-placement="left"
    :label-width="100"
    label-align="left"
    :model="formData"
    :rules="formRules"
  >
    <NFormItem :label="$t('Current Mobile Number')" v-if="userStore.profile?.phone">
      <NInput
        :default-value="userStore.profile?.phone"
        disabled
      />
    </NFormItem>

    <NFormItem>
      <NAlert :show-icon="false">
        <p v-if="userStore.profile?.phone">{{ $t('To change your mobile number, please enter the new number in the form below. A verification code will be sent to the new number. Once verified, you can update your mobile number.') }}</p>
        <p v-else>{{ $t('According to national laws and regulations, the account must be bound to at least one mobile number.') }}</p>
      </NAlert>
    </NFormItem>

    <PhoneVerifyInput
      event="bind"
      v-model:country-code="formData.country_code"
      v-model:phone="formData.phone"
      v-model:code="formData.code"
      size="medium"
    />

    <NFormItem :show-feedback="false">
      <NButton
        type="primary"
        :loading="loading"
        @click="onSubmit"
      >{{ $t('Confirm') }}{{ userStore.profile?.phone ? $t('Change') : $t('Bind') }}</NButton>
    </NFormItem>
  </NForm>
</template>