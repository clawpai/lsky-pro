<script setup lang="ts">
import {
  NForm,
  NFormItemRow,
  NTabs,
  NTab,
  NButton,
  NInput,
  useMessage,
} from "naive-ui"
import {ref} from "vue";
import {
  postMailResetPassword,
  postSmsResetPassword,
} from "@/api";
import {useRouter} from "vue-router";
import AuthFormCard from "@/components/Auth/AuthFormCard.vue";
import {useConfigStore} from "@/stores/config";
import EmailVerifyInput from "@/components/Form/EmailVerifyInput.vue";
import PhoneVerifyInput from "@/components/Form/PhoneVerifyInput.vue";
import Layout from "@/components/Layout.vue";
import {useI18n} from "vue-i18n";

const configStore = useConfigStore()
const { t } = useI18n()
const router = useRouter()
const message = useMessage()
const formRef = ref<HTMLFormElement | null>(null);
type ResetPasswordBody = {
  email: string
  phone: string
  password: string
  password_confirmation: string
  code: string
  country_code: string
}
const formData = ref<ResetPasswordBody>({
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  code: '',
  country_code: 'cn',
})
const onSubmit = async () => {
  let result;
  if (tab.value === 'email') {
    result = await postMailResetPassword({body: formData.value})
  } else {
    result = await postSmsResetPassword({body: formData.value})
  }

  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }

  router.push('/login').then(() => message.success(t('Password reset successfully')))
}
const tab = ref('email')
</script>

<template>
  <Layout :header-title="$t('Forgot Password')">
    <AuthFormCard :title="$t('Recover Password')">
      <NTabs type="segment" class="mb-4" v-model:value="tab" v-if="configStore.configs?.app.user_phone_verify">
        <NTab name="email">
          {{ $t('Recover via Email') }}
        </NTab>
        <NTab name="phone">
          {{ $t('Recover via Mobile Number') }}
        </NTab>
      </NTabs>

      <NForm
        :disabled="! configStore.configs?.app.enable_registration"
        ref="formRef"
        :model="formData"
        :show-require-mark="true"
        @submit.prevent="onSubmit"
      >
        <EmailVerifyInput
          v-if="'email' === tab"
          event="forget_password"
          v-model:email="formData.email"
          v-model:code="formData.code"
        />

        <PhoneVerifyInput
          v-if="'phone' === tab"
          event="forget_password"
          v-model:phone="formData.phone"
          v-model:country-code="formData.country_code"
          v-model:code="formData.code"
        />

        <NFormItemRow :label="$t('New Password')">
          <NInput
            v-model:value="formData.password"
            :placeholder="$t('Please enter a new password')"
            type="password"
            show-password-on="mousedown"
            size="large"
            :input-props="{required: true}"
          />
        </NFormItemRow>

        <NFormItemRow :label="$t('Confirm New Password')" :show-feedback="false">
          <NInput
            v-model:value="formData.password_confirmation"
            :placeholder="$t('Please confirm your new password')"
            type="password"
            show-password-on="mousedown"
            size="large"
            :input-props="{required: true}"
          />
        </NFormItemRow>

        <NFormItemRow>
          <NButton
            type="primary"
            attr-type="submit"
            size="large"
            strong
            block
          >
            {{ $t('Confirm Password Reset') }}
          </NButton>
        </NFormItemRow>

        <div class="flex justify-between">
          <NButton text type="info" @click="router.push('/')">{{ $t('Back to Home') }}</NButton>
          <NButton text type="info" @click="router.push('/login')">{{ $t('Return to Login') }}</NButton>
        </div>
      </NForm>
    </AuthFormCard>
  </Layout>
</template>