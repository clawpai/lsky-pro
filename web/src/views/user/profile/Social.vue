<script setup lang="ts">
import {
  NTable,
  NAlert,
  NButton,
  NIcon,
  NPopconfirm,
  NEmpty,
  useMessage,
} from "naive-ui"
import {useConfigStore} from "@/stores/config";
import app from "@/utils/app";
import {
  deleteOauthByIdUnbind,
  type GetConfigsResponse,
  getOauthBinds,
  type GetOauthBindsResponse,
  getOauthByIdRedirect,
  postOauthByIdBind
} from "@/api";
import {computed, onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const configStore = useConfigStore()
const { t } = useI18n()
const socialites = ref<GetOauthBindsResponse['data']['data']>([])

const openSocialiteUrl = async (socialite: GetConfigsResponse['data']['app']['socialites'][number]) => {
  // 聚合多方式驱动：跳转地址携带 type，回调时用于区分登录方式
  const result = await getOauthByIdRedirect({
    path: {
      id: socialite.id.toString(),
    },
    query: {
      // type 仅作为请求参数交给聚合网关；避免网关回调再次追加时形成 wx,wx。
      redirect_url: `${app.getWithoutQueryUrl()}?tab=social&driver_id=${socialite.id}`,
      type: socialite.type ?? undefined,
    }
  })
  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }

  window.location.replace(result.data!.data.redirect_url)
}

// 解除绑定（聚合多方式：携带 type 精确解绑，防止误删其他登录方式）
const unbind = (oauthId: number, driverId: number, type?: string | null) => {
  deleteOauthByIdUnbind({
    path: {id: driverId.toString()},
    query: {oauth_id: oauthId, ...(type ? {type} : {})},
  }).then(response => {
    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    getOauthBinds().then(response => {
      socialites.value = response.data?.data.data || []
    })
    message.success(t('Unbound successfully'))
  })
}

// 未绑定的第三方（多方式驱动按 driver_id+type 区分）
const socials = computed(() => (configStore.configs?.app.socialites || []).filter(item => {
  return socialites.value.map(socialite => `${socialite.driver.id}:${socialite.driver.type || ''}`).indexOf(`${item.id}:${item.type || ''}`) === -1
}))

onMounted(() => {
  getOauthBinds({
    query: {
      page: 1,
      per_page: 999
    }
  }).then(response => {
    socialites.value = response.data?.data.data || []
  })

  if (route.query.driver_id && route.query.code) {
    postOauthByIdBind({
      path: {
        id: route.query.driver_id.toString(),
      },
      body: {
        code: route.query.code.toString(),
        type: route.query.type?.toString(),
      }
    }).then(response => {
      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      getOauthBinds({
        query: {
          page: 1,
          per_page: 999
        }
      }).then(response => {
        socialites.value = response.data?.data.data || []
      })

      router.replace('/user/profile?tab=social')
      message.success(t('Bound successfully'))
    })
  }
})
</script>

<template>
  <div class="space-y-4">
    <NAlert :show-icon="false">{{ $t('Bind a third-party account to log in directly with it in the future.') }}</NAlert>

    <NTable :bordered="false">
      <thead>
      <tr>
        <th>{{ $t('Name') }}</th>
        <th>{{ $t('User Information') }}</th>
        <th>{{ $t('Binding Time') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="socialite in socialites">
        <td>{{ app.getSocialiteDisplayName({provider: socialite.driver.provider, type: socialite.driver.type, name: socialite.driver.name}) }}</td>
        <td>
          <div class="flex space-x-1 items-center">
            <img class="w-8 rounded-full" :src="socialite.avatar" v-if="socialite.avatar" alt="avatar">
            <span>{{ socialite.name || socialite.nickname }}</span>
          </div>
        </td>
        <td>{{ useDayjs(socialite.created_at).format('LLL') }}</td>
        <td>
          <NPopconfirm
            @positive-click="unbind(socialite.id, socialite.driver.id, socialite.driver.type)"
          >
            {{ $t('Are you sure you want to unbind this account?') }}
            <template #trigger>
              <NButton
                type="primary"
                text
              >{{ $t('Unbind') }}</NButton>
            </template>
          </NPopconfirm>
        </td>
      </tr>
      </tbody>
    </NTable>

    <NEmpty v-if="socialites.length <= 0">{{ $t('No third-party accounts bound yet') }}</NEmpty>

    <div v-if="socials.length > 0">
      <p>{{ $t('You can also bind the following accounts:') }}</p>
      <div class="flex space-x-6 mt-2">
        <NButton
          text
          v-for="social in socials"
          @click="openSocialiteUrl(social)"
        >
          <template #icon>
            <NIcon :component="app.getSocialiteIcon(social.provider, social.type)" />
          </template>
          <span v-if="socials.length <= 2">
            {{ app.getSocialiteDisplayName(social) }}
          </span>
        </NButton>
      </div>
    </div>
  </div>
</template>