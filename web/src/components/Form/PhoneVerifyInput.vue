<script setup lang="ts">
import {NButton, NFormItem, NInput, NInputGroup, NSelect, useMessage} from "naive-ui";
import {type PostMailSendData, postSmsSend} from "@/api";
import {type PropType, ref, watch, computed} from "vue";
import type {SelectMixedOption, SelectOption} from "naive-ui/es/select/src/interface";
import {useConfigStore} from "@/stores/config";
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
  modelValueCountryCode: {
    type: String,
    default: undefined
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const countryCode = defineModel('countryCode', { default: 'cn' })
const phone = defineModel('phone', { default: '' })
const code = defineModel('code', { default: '' })
const captcha = ref('')
const captchaKey = ref('')

// 监听modelValue和modelValueCountryCode变化，同步到对应的值
watch(() => props.modelValue, (newVal) => {
  if (newVal && newVal !== phone.value) {
    phone.value = newVal
  }
}, { immediate: true })

watch(() => props.modelValueCountryCode, (newVal) => {
  if (newVal && newVal !== countryCode.value) {
    countryCode.value = newVal
  }
}, { immediate: true })

const { t } = useI18n()
const message = useMessage()
const configStore = useConfigStore()
const btnText = ref(t('Send SMS Verification Code'))
const btnDisabled = ref(false)

// 计算属性：是否显示验证码
const showCaptcha = computed(() => {
  return (props.modelValue && props.modelValue.length > 0) || phone.value.length > 0
})

const countryCodes = configStore.getSelectOptionCountryCodes() as SelectMixedOption[]
const renderCountryCodeTag = (props: {
  option: SelectOption;
  handleClose: () => void;
}) => {
  return props.option.label?.toString()?.match(/^\+\d+/) || ''
}

const startCountdown = () => {
  let s = 60
  btnDisabled.value = true
  const interval = setInterval(() => {
    s--
    btnText.value = t('Resend after {seconds} seconds', {seconds: s})
    if (s <= 0) {
      btnDisabled.value = false
      btnText.value = t('Send SMS Verification Code')
      clearInterval(interval)
    }
  }, 1000)
}

const sendVerifyCode = async () => {
  const phoneValue = props.modelValue || phone.value.toString()
  const countryCodeValue = props.modelValueCountryCode || countryCode.value.toString()
  
  if (!showCaptcha.value) {
    return message.error(t('Please enter your mobile number first'))
  }
  
  if (!captcha.value) {
    return message.error(t('Please enter captcha'))
  }
  
  if (!captchaKey.value) {
    return message.error(t('Captcha key is missing, please refresh the captcha'))
  }
  
  const result = await postSmsSend({
    body: {
      event: props.event,
      country_code: countryCodeValue,
      phone: phoneValue,
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
    <NFormItem :label="$t('Mobile Number')" show-require-mark>
      <NInputGroup>
        <NSelect
          :options="countryCodes"
          :render-tag="renderCountryCodeTag"
          :consistent-menu-width="false"
          v-model:value="countryCode"
          class="w-40"
          :size="props.size"
          filterable
          :placeholder="$t('Select Country')"
        />
        <NInput
          v-model:value="phone"
          :placeholder="$t('Please enter your mobile number')"
          :input-props="{required: true}"
          :size="props.size"
        />
      </NInputGroup>
    </NFormItem>
    
    <NFormItem v-if="showCaptcha" :label="$t('Captcha')" show-require-mark>
      <CaptchaInput v-model:captcha="captcha" v-model:captcha-key="captchaKey" :size="props.size" />
    </NFormItem>

    <NFormItem :label="$t('SMS Verification Code')" show-require-mark>
      <NInput
        v-model:value="code"
        :input-props="{required: true}"
        :placeholder="$t('Please enter the SMS verification code')"
        :size="props.size"
      >
        <template #suffix>
          <NButton
            text
            type="info"
            @click="sendVerifyCode"
            :disabled="btnDisabled || (!props.modelValue && !phone) || (showCaptcha && !captcha)"
          >
            {{ btnText }}
          </NButton>
        </template>
      </NInput>
    </NFormItem>
  </template>
</template>