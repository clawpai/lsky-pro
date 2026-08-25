<script lang="ts" setup>
import type {MenuOption} from 'naive-ui'
import {NAvatar, NButton, NDropdown, NIcon, NText} from "naive-ui";
import type {Component, ComputedRef} from 'vue'
import {computed, h} from "vue";
import {
  ImagesOutline as ImagesOutlineIcon,
  LogOutOutline as LogOutOutlineIcon,
  OptionsOutline as OptionsOutlineIcon,
  PersonOutline as PersonOutlineIcon,
  PieChartOutline as PieChartOutlineIcon,
  LaptopOutline as LaptopOutlineIcon,
} from '@vicons/ionicons5'
import {useUserStore} from "@/stores/user";
import app from "@/utils/app";
import router from "@/router";
import {useConfigStore} from "@/stores/config";
import {useI18n} from "vue-i18n";

const userStore = useUserStore()
const configStore = useConfigStore()
const { t } = useI18n()

function renderIcon(icon: Component) {
  return () => h(NIcon, null, {default: () => h(icon)})
}

const userMenuOptions: ComputedRef<MenuOption[]> = computed(() => [
  {
    key: 'header',
    type: 'render',
    render: () => h(
      'div',
      {
        class: 'flex items-center p-3 min-w-[260px] max-w-[300px] md:max-w-[400px]',
      },
      [
        h(NAvatar, {
          round: true,
          class: 'mr-3 shrink-0',
          src: app.getUserAvatar(userStore.profile?.avatar_url)
        }),
        h('div', {class: 'overflow-hidden'}, [
          h('div', {class: 'text-lg truncate'}, [h(NText, {depth: 2}, {default: () => userStore.profile?.name})]),
          h('div', {class: 'text-sm truncate'}, [
            h(
              NText,
              {depth: 3},
              {default: () => userStore.profile?.tagline || userStore.profile?.email}
            )
          ])
        ])
      ]
    )
  },
  {
    key: 'header-divider',
    type: 'divider'
  },
  {
    label: t('Dashboard'),
    key: 'dashboard',
    icon: renderIcon(PieChartOutlineIcon),
  },
  {
    label: t('My Photos'),
    key: 'photos',
    icon: renderIcon(ImagesOutlineIcon),
  },
  {
    label: t('Profile'),
    key: 'profile',
    icon: renderIcon(PersonOutlineIcon)
  },
  {
    label: t('Settings'),
    key: 'settings',
    icon: renderIcon(OptionsOutlineIcon),
  },
  {
    label: t('Admin Panel'),
    key: 'admin',
    icon: renderIcon(LaptopOutlineIcon),
    show: userStore.profile?.is_admin,
  },
  {
    key: 'header-divider',
    type: 'divider'
  },
  {
    label: t('Logout'),
    key: 'logout',
    icon: renderIcon(LogOutOutlineIcon)
  },
])

const onSelect = (key: string | number) => {
  switch (key) {
    case 'dashboard':
    case 'photos':
    case 'settings':
      router.push(`/user/${key}`)
      break;
    case 'profile':
      router.push('/user/profile')
      break;
    case 'admin':
      window.open(`${configStore.configs?.app.url}/admin`)
      break;
    case 'logout':
      userStore.removeToken()
      userStore.setUserProfile({})
      userStore.setIsLoggedIn(false)
      window.location.href = '/'
      break;
  }
}

</script>

<template>
  <NDropdown
    :options="userMenuOptions"
    placement="bottom-end"
    show-arrow
    trigger="click"
    class="rounded-lg"
    :on-select="onSelect"
  >
    <NButton circle round secondary strong>
      <NAvatar round size="small" :src="app.getUserAvatar(userStore.profile?.avatar_url)"/>
    </NButton>
  </NDropdown>
</template>