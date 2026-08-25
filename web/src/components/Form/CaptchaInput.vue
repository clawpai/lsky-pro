<script setup lang="ts">
import { ref, onMounted, type PropType, computed } from 'vue'
import { NInput, NInputGroup, NButton, useMessage } from 'naive-ui'
import { getCaptcha } from '@/api'
import { useI18n } from 'vue-i18n'
import type { Size } from "naive-ui/es/input/src/interface"

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  size: {
    type: String as PropType<Size>,
    default: () => 'medium'
  }
})

const captcha = defineModel('captcha', { default: '' })
const captchaKey = defineModel('captchaKey', { default: '' })

const { t } = useI18n()
const message = useMessage()
const captchaImageSrc = ref('')
const loading = ref(false)

// 根据size计算高度
const captchaHeight = computed(() => {
  switch (props.size) {
    case 'small': return '28px'
    case 'large': return '40px'
    case 'medium':
    default: return '34px'
  }
})

// 获取验证码
const fetchCaptcha = async () => {
  try {
    loading.value = true
    const response = await getCaptcha()
    const responseData = response.data as any
    
    if (responseData && responseData.data) {
      captchaImageSrc.value = responseData.data.img
      captchaKey.value = responseData.data.key
      captcha.value = ''
    } else {
      message.error(t('Failed to load captcha'))
    }
  } catch (error) {
    message.error(t('Failed to load captcha'))
    console.error(error)
  } finally {
    loading.value = false
  }
}

// 页面加载时获取验证码
onMounted(() => {
  fetchCaptcha()
})
</script>

<template>
  <NInputGroup>
    <div class="flex items-center w-full">
      <NInput
        v-model:value="captcha"
        :placeholder="$t('Please enter captcha')"
        :disabled="props.disabled"
        :size="props.size"
        class="flex-1"
      />
      <div 
        class="ml-2 flex items-center cursor-pointer"
        @click="fetchCaptcha"
        :class="{ 'opacity-50': loading }"
        :style="{ height: captchaHeight }"
      >
        <div v-if="captchaImageSrc" class="overflow-hidden rounded-md border border-[#d9d9d9] dark:border-[#383838] h-full">
          <img 
            :src="captchaImageSrc" 
            alt="captcha" 
            class="h-full object-cover object-center"
            :style="{ maxHeight: captchaHeight }"
          />
        </div>
        <NButton v-else @click="fetchCaptcha" :loading="loading" :size="props.size" class="h-full">
          {{ $t('Load Captcha') }}
        </NButton>
      </div>
    </div>
  </NInputGroup>
</template> 