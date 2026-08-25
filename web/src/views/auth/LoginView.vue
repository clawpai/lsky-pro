<script setup lang="ts">
import {
  NIcon,
  NForm,
  NFormItem,
  NCheckbox,
  NTabs,
  NTab,
  NButton,
  NInput,
  NInputGroup,
  NSelect,
  NTooltip,
  NSpin,
  NAlert,
  useMessage
} from "naive-ui"
import {
  LogInOutline as LogInOutlineIcon,
} from "@vicons/ionicons5"
import {onMounted, ref} from "vue";
import {
  type GetConfigsResponse,
  getOauthByIdRedirect,
  postLogin,
  type PostLoginData,
  postOauthByIdLogin
} from "@/api";
import {useRoute, useRouter} from "vue-router";
import AuthFormCard from "@/components/Auth/AuthFormCard.vue";
import {useConfigStore} from "@/stores/config";
import type {SelectMixedOption, SelectOption} from "naive-ui/es/select/src/interface";
import app from "@/utils/app";
import {useUserStore} from "@/stores/user";
import {useLayoutStore} from "@/stores/layout";
import Layout from "@/components/Layout.vue";

const layoutStore = useLayoutStore()
const configStore = useConfigStore()
const userStore = useUserStore()

const countryCodes = configStore.getSelectOptionCountryCodes() as SelectMixedOption[]
const renderCountryCodeTag = (props: {
  option: SelectOption;
  handleClose: () => void;
}) => {
  return props.option.label?.toString()?.match(/^\+\d+/) || ''
}
const loading = ref(true)
const route = useRoute()
const router = useRouter()
const message = useMessage()
const formRef = ref<HTMLFormElement | null>(null);
const formData = ref<Required<PostLoginData>['body']>({
  login_type: 'email',
  username: '',
  password: '',
  remember: true,
  token: route.query?.token?.toString() || '',
  country_code: 'cn',
})
const onSubmit = async () => {
  const result = await postLogin({body: formData.value})
  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }
  await toLogged(result.data!.data.token)
}
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
  window.location.href = '/user/dashboard'
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
        toLogged(token)
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
  <Layout :header-title="$t('Login')">
    <div class="h-full flex justify-center items-center" v-if="loading">
      <NSpin size="small" description="loading..."/>
    </div>
    <AuthFormCard v-else :title="$t('Login to {sitename}', {sitename: configStore.configs?.site.title})">
      <NAlert type="info" class="mb-4" v-if="formData.token">
        {{ $t('Please log in to your account. After binding, you can log in directly next time.') }}
      </NAlert>

      <NTabs type="segment" class="mb-4" v-model:value="formData.login_type">
        <NTab name="email" :tab="$t('Login with Email')"/>
        <NTab name="phone" :tab="$t('Login with Mobile Number')" v-if="configStore.configs?.app.user_phone_verify"/>
        <NTab name="username" :tab="$t('Login with Username')"/>
      </NTabs>

      <NForm
        ref="formRef"
        :model="formData"
        :show-require-mark="true"
        @submit.prevent="onSubmit"
      >
        <NFormItem :label="$t('Email')" v-if="'email' === formData.login_type">
          <NInput
            v-model:value="formData.username"
            :input-props="{type: 'email', required: true}"
            :placeholder="$t('Please enter your email')"
            size="large"
          />
        </NFormItem>

        <NFormItem :label="$t('Mobile Number')" v-if="'phone' === formData.login_type">
          <NInputGroup>
            <NSelect
              :options="countryCodes"
              :render-tag="renderCountryCodeTag"
              :consistent-menu-width="false"
              v-model:value="formData.country_code"
              class="w-28"
              size="large"
              filterable
              :placeholder="$t('Select Country')"
            />
            <NInput
              v-model:value="formData.username"
              :placeholder="$t('Please enter your mobile number')"
              :input-props="{required: true}"
              size="large"
            >
            </NInput>
          </NInputGroup>
        </NFormItem>

        <NFormItem :label="$t('Username')" v-if="'username' === formData.login_type">
          <NInput
            v-model:value="formData.username"
            :input-props="{required: true}"
            :placeholder="$t('Please enter your username')"
            size="large"
          />
        </NFormItem>

        <NFormItem :label="$t('Password')" :show-feedback="false">
          <NInput
            v-model:value="formData.password"
            :placeholder="$t('Please enter your password')"
            type="password"
            show-password-on="mousedown"
            size="large"
            :input-props="{required: true}"
          />
        </NFormItem>

        <NFormItem>
          <NButton
            type="primary"
            attr-type="submit"
            size="large"
            strong
            block
          >
            <template #icon>
              <NIcon :component="LogInOutlineIcon"/>
            </template>
            {{ $t('Login') }}
          </NButton>
        </NFormItem>

        <div class="flex justify-between">
          <NCheckbox :default-checked="formData.remember" v-model:checked-value="formData.remember">{{ $t('Remember Me') }}</NCheckbox>
          <NButton text type="info" @click="router.push('/forget-password')">{{ $t('Forgot Password?') }}</NButton>
        </div>

        <div class="mt-8 mb-4" v-if="! formData.token && (configStore.configs?.app.socialites || []).length > 0">
          <div class="flex flex-row justify-center relative">
            <div class="absolute top-[10px] h-[1px] bg-gray-200 dark:bg-gray-500 w-[80%] z-[1]"></div>
            <p class="relative z-[2] text-sm px-4 bg-white dark:bg-[var(--n-color)] text-gray-500">{{ $t('Login with Other Methods') }}</p>
          </div>

          <div class="flex justify-center space-x-6 mt-4">
            <NButton
              text
              v-for="socialite in (configStore.configs?.app.socialites || [])"
              @click="openSocialiteUrl(socialite)"
            >
              <template #icon>
                <NIcon :component="app.getSocialiteIcon(socialite.provider)" />
              </template>
              {{ socialite.name }}
            </NButton>
          </div>
        </div>

        <div class="flex justify-between mt-6">
          <NButton text type="info" @click="router.push('/')">{{ $t('Back to Home') }}</NButton>
          <NTooltip trigger="hover" :disabled="configStore.configs?.app.enable_registration">
            <template #trigger>
              <p>
                <span>{{ $t('Don\'t have an account?') }}</span>

                <NButton
                  text
                  type="info"
                  @click="router.push({path: '/register', query: {token: formData.token || undefined}})"
                  :disabled="! configStore.configs?.app.enable_registration"
                >{{ $t('Register Account') }}</NButton>
              </p>
            </template>
            {{ $t('Registration is currently closed') }}
          </NTooltip>
        </div>
      </NForm>
    </AuthFormCard>
  </Layout>
</template>