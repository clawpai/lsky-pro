<script setup lang="ts">
import {
  NIcon,
  NForm,
  NFormItem,
  NTabs,
  NTab,
  NButton,
  NInput,
  NAlert,
  useMessage,
  NSpin, NInputGroup
} from "naive-ui"
import {
  LogInOutline as LogInOutlineIcon,
} from "@vicons/ionicons5"
import {onMounted, ref} from "vue";
import {
  type GetConfigsResponse,
  getOauthByIdRedirect,
  postOauthByIdLogin,
  postRegister,
  type PostRegisterData,
} from "@/api";
import {useRoute, useRouter} from "vue-router";
import AuthFormCard from "@/components/Auth/AuthFormCard.vue";
import {useConfigStore} from "@/stores/config";
import app from "@/utils/app";
import {useUserStore} from "@/stores/user";
import {useLayoutStore} from "@/stores/layout";
import EmailVerifyInput from "@/components/Form/EmailVerifyInput.vue";
import PhoneVerifyInput from "@/components/Form/PhoneVerifyInput.vue";
import Layout from "@/components/Layout.vue";
import {useI18n} from "vue-i18n";

const layoutStore = useLayoutStore()
const configStore = useConfigStore()
const userStore = useUserStore()

const loading = ref(true)
const route = useRoute()
const router = useRouter()
const message = useMessage()
const { t } = useI18n()
const formRef = ref<HTMLFormElement | null>(null);
const formData = ref<Required<PostRegisterData>['body']>({
  email: '',
  phone: '',
  name: '',
  username: '',
  password: '',
  password_confirmation: '',
  code: '',
  country_code: 'cn',
  token: route.query?.token?.toString() || '',
})
const onSubmit = async () => {
  const result = await postRegister({body: formData.value})
  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }
  message.success(t('Registration successful. Please log in.'))
  await router.push('/login')
}

const tab = ref('email')
const openSocialiteUrl = async (socialite: GetConfigsResponse['data']['app']['socialites'][number]) => {
  const result = await getOauthByIdRedirect({
    path: {
      id: socialite.id.toString(),
    },
    query: {
      redirect_url: `${app.getWithoutQueryUrl()}?driver_id=${socialite.id}`,
    }
  })
  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }

  window.location.replace(result.data!.data.redirect_url)
}
const toLogged = async (token: string) => {
  userStore.setToken(token)
  router.push('/user/dashboard').then(() => {
    layoutStore.setSidebarOpen(true)
  })
}
onMounted(() => {
  if (userStore.isLoggedIn) {
    router.push('/user/dashboard').then(() => {
      layoutStore.setSidebarOpen(true)
    })
  }

  if (route.query.driver_id && route.query.code) {
    // 请求授权登录接口
    // 返回 202 表示需要绑定或注册账号，需要将结构中的token储存，并携带到注册或登录页面，注册，请求注册或登录接口时将token一并发送即可绑定账号。
    postOauthByIdLogin({
      path: {
        id: route.query.driver_id.toString(),
      },
      body: {
        code: route.query.code.toString(),
      }
    }).then(result => {
      const token = result.data?.data.token.toString() || ''
      if (result.status === 202) {
        router.replace({query: {token}})
        formData.value.token = token
      } else {
        toLogged(result.data!.data.token).then(() => {
          message.success(t('Account exists. Logged in directly.'))
        })
      }
    }).finally(() => {
      loading.value = false
    })
  } else {
    loading.value = false
  }
})
</script>

<template>
  <Layout :header-title="$t('Register')">
    <div v-if="loading" class="w-full h-full flex items-center justify-center">
      <NSpin size="small" description="loading..."/>
    </div>
    <AuthFormCard v-else :title="$t('Register a {sitename} account', {sitename: configStore.configs?.site.title})">
      <NAlert type="info" class="mb-4" v-if="formData.token">
        {{ $t('Register a new account and bind it. After binding, you can log in directly next time.') }}
      </NAlert>

      <NTabs type="segment" class="mb-4" v-model:value="tab" v-if="configStore.configs?.app.user_phone_verify">
        <NTab name="email">
          {{ $t('Register with Email') }}
        </NTab>
        <NTab name="phone">
          {{ $t('Register with Mobile Number') }}
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
          v-if="'email' === tab && configStore.configs?.app.user_email_verify"
          size="large"
          event="register"
          v-model:email="formData.email"
          v-model:code="formData.code"
        />

        <NFormItem v-else-if="'email' === tab" :label="$t('Email')" show-require-mark>
          <NInputGroup>
            <NInput
              v-model:value="formData.email"
              :input-props="{type: 'email', required: true}"
              :placeholder="$t('Please enter your email')"
              size="large"
            />
          </NInputGroup>
        </NFormItem>

        <PhoneVerifyInput
          v-if="'phone' === tab"
          size="large"
          event="register"
          v-model:phone="formData.phone"
          v-model:country-code="formData.country_code"
          v-model:code="formData.code"
        />

        <NFormItem :label="$t('Username')">
          <NInput
            v-model:value="formData.username"
            :input-props="{required: true}"
            :placeholder="$t('Please enter your username')"
            size="large"
          />
        </NFormItem>
        <NFormItem :label="$t('Name')">
          <NInput
            v-model:value="formData.name"
            :input-props="{required: true}"
            :placeholder="$t('Please enter your name')"
            size="large"
          />
        </NFormItem>

        <NFormItem :label="$t('Password')">
          <NInput
            v-model:value="formData.password"
            :placeholder="$t('Please enter your password')"
            type="password"
            show-password-on="mousedown"
            size="large"
            :input-props="{required: true}"
          />
        </NFormItem>

        <NFormItem :label="$t('Confirm Password')" :show-feedback="false">
          <NInput
            v-model:value="formData.password_confirmation"
            :placeholder="$t('Please confirm your password')"
            type="password"
            show-password-on="mousedown"
            size="large"
            :input-props="{required: true}"
          />
        </NFormItem>

        <NFormItem>
          <NButton
            :disabled="! configStore.configs?.app.enable_registration"
            type="primary"
            attr-type="submit"
            size="large"
            strong
            block
          >
            <template #icon>
              <NIcon :component="LogInOutlineIcon"/>
            </template>
            {{ $t('Register') }}
          </NButton>
        </NFormItem>

        <div class="flex justify-between">
          <div></div>
          <p>
            {{ $t('Already have an account?') }}
            <NButton text type="info" @click="router.push({path: '/login', query: {token: formData.token || undefined}})">{{ $t('Go to Login') }}</NButton>
          </p>
        </div>

        <div class="mt-8 mb-4" v-if="! formData.token && (configStore.configs?.app.socialites || []).length > 0">
          <div class="flex flex-row justify-center relative">
            <div class="absolute top-[10px] h-[1px] bg-gray-200 dark:bg-gray-500 w-[80%] z-[1]"></div>
            <p class="relative z-[2] text-sm px-4 bg-white dark:bg-[var(--n-color)] text-gray-500">{{ $t('Register with Other Methods') }}</p>
          </div>

          <div class="flex justify-center space-x-6 mt-4">
            <NButton
              text
              v-for="socialite in configStore.configs?.app.socialites"
              @click="openSocialiteUrl(socialite)"
            >
              <template #icon>
                <NIcon :component="app.getSocialiteIcon(socialite.provider)" />
              </template>
              {{ socialite.name }}
            </NButton>
          </div>
        </div>
      </NForm>
    </AuthFormCard>
  </Layout>
</template>
