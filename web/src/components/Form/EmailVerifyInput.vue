<script setup lang="ts">
import {NButton, NFormItem, NInput, NInputGroup, useMessage} from "naive-ui";
import {postMailSend, type PostMailSendData} from "@/api";
import {type PropType, ref, watch, computed} from "vue";
import type {Size} from "naive-ui/es/input/src/interface";
import {useI18n} from "vue-i18n";
import CaptchaInput from "./CaptchaInput.vue";

const props = defineProps({
  event: {
    type: String as PropType<NonNullable<PostMailSendData['body']>['event']>,
    required: true,
  },
  size: {
    type: String as PropType<Size>,
    default: () => 'medium'
  },
  modelValue: {
    type: String,
    default: undefined
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const email = defineModel('email', { default: '' })
const code = defineModel('code', { default: '' })
const captcha = ref('')
const captchaKey = ref('')

// 监听modelValue变化，同步到email
watch(() => props.modelValue, (newVal) => {
  if (newVal && newVal !== email.value) {
    email.value = newVal
  }
}, { immediate: true })

const { t } = useI18n()
const message = useMessage()
const btnText = ref(t('Send Email Verification Code'))
const btnDisabled = ref(false)

// 计算属性：是否显示验证码
const showCaptcha = computed(() => {
  return (props.modelValue && props.modelValue.length > 0) || email.value.length > 0
})

const startCountdown = () => {
  let s = 60
  btnDisabled.value = true
  const interval = setInterval(() => {
    s--
    btnText.value = t('Resend after {seconds} seconds', {seconds: s})
    if (s <= 0) {
      btnDisabled.value = false
      btnText.value = t('Send Email Verification Code')
      clearInterval(interval)
    }
  }, 1000)
}

const sendVerifyCode = async () => {
  const emailValue = props.modelValue || email.value.toString()
  
  if (!showCaptcha.value) {
    return message.error(t('Please enter your email first'))
  }
  
  if (!captcha.value) {
    return message.error(t('Please enter captcha'))
  }
  
  if (!captchaKey.value) {
    return message.error(t('Captcha key is missing, please refresh the captcha'))
  }

  const result = await postMailSend({
    body: {
      event: props.event,
      email: emailValue,
      captcha: captcha.value,
      captcha_key: captchaKey.value,
    }
  })
  if (result.data?.status === 'success') {
    startCountdown()
  } else {
    message.error(result.data!.message)
  }
}
</script>

<template>
  <slot v-if="props.disabled" :send-verify-code="sendVerifyCode" :btn-disabled="btnDisabled" :btn-text="btnText"></slot>
  
  <template v-else>
    <NFormItem :label="$t('Email')" show-require-mark>
      <NInputGroup>
        <NInput
          v-model:value="email"
          :input-props="{type: 'email', required: true}"
          :placeholder="$t('Please enter your email')"
          :size="props.size"
        />
      </NInputGroup>
    </NFormItem>
    
    <NFormItem v-if="showCaptcha" :label="$t('Captcha')" show-require-mark>
      <CaptchaInput v-model:captcha="captcha" v-model:captcha-key="captchaKey" :size="props.size" />
    </NFormItem>

    <NFormItem :label="$t('Email Verification Code')" show-require-mark>
      <NInput
        v-model:value="code"
        :input-props="{required: true}"
        :placeholder="$t('Please enter the email verification code')"
        :size="props.size"
      >
        <template #suffix>
          <NButton
            text
            type="info"
            @click="sendVerifyCode"
            :disabled="btnDisabled || (!props.modelValue && !email) || (showCaptcha && !captcha)"
          >
            {{ btnText }}
          </NButton>
        </template>
      </NInput>
    </NFormItem>
  </template>
</template>