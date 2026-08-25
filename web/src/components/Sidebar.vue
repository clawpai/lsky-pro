<script lang="ts" setup>
import {type Component, computed, watch} from 'vue'
import {h, ref} from 'vue'
import type {MenuOption} from 'naive-ui'
import {NIcon, NLayoutHeader, NLayoutSider, NMenu, NProgress,} from 'naive-ui'
import {
  AlbumsOutline as AlbumsOutlineIcon,
  CartOutline as CartOutlineIcon,
  EarthOutline as EarthOutlineIcon,
  ImagesOutline as ImagesOutlineIcon,
  ListOutline as ListOutlineIcon,
  OptionsOutline as OptionsOutlineIcon,
  PieChartOutline as PieChartOutlineIcon,
  ShareSocialOutline as ShareSocialOutlineIcon,
  SearchOutline as SearchOutlineIcon,
  AnalyticsOutline as AnalyticsOutlineIcon,
  PersonOutline as PersonOutlineIcon,
} from '@vicons/ionicons5'
import {useLayoutStore} from "@/stores/layout"
import {useDeviceStore} from "@/stores/device";
import {useConfigStore} from "@/stores/config";
import number from "../utils/number";
import {useUserStore} from "@/stores/user";
import {useRoute, useRouter} from "vue-router";
import {useI18n} from "vue-i18n";

const { t } = useI18n()

function renderIcon(icon: Component) {
  return () => h(NIcon, {color: '#0094c5'}, {default: () => h(icon)})
}

const menuOptions = computed<MenuOption[]>(() => {
  const options: MenuOption[] = []
  
  // 根据配置决定是否显示广场
  if (configStore.configs?.app?.enable_explore) {
    options.push({
      label: t('Square'),
      key: '/explore',
      icon: renderIcon(SearchOutlineIcon),
    })
  }
  
  options.push({
    label: t('Dashboard'),
    key: '/user/dashboard',
    icon: renderIcon(PieChartOutlineIcon),
  })
  
  options.push({
    type: 'group',
    label: t('General'),
    key: 'basic',
    children: [
      {
        label: t('My Photos'),
        key: '/user/photos',
        icon: renderIcon(ImagesOutlineIcon),
      },
      {
        label: t('My Albums'),
        key: '/user/albums',
        icon: renderIcon(AlbumsOutlineIcon),
      },
      {
        label: t('My Shares'),
        key: '/user/shares',
        icon: renderIcon(ShareSocialOutlineIcon),
      },
    ]
  })
  
  options.push({
    type: 'group',
    label: t('Finance'),
    key: 'finance',
    children: [
      {
        label: t('Purchase Subscription'),
        key: '/user/plans',
        icon: renderIcon(CartOutlineIcon),
      },
      {
        label: t('My Orders'),
        key: '/user/orders',
        icon: renderIcon(ListOutlineIcon),
      },
    ]
  })
  
  options.push({
    type: 'group',
    label: t('Others'),
    key: 'other',
    children: [
      {
        label: t('My Tickets'),
        key: '/user/tickets',
        icon: renderIcon(EarthOutlineIcon),
      },
      {
        label: t('My Tokens'),
        key: '/user/tokens',
        icon: renderIcon(AnalyticsOutlineIcon),
      },
      {
        label: t('Profile'),
        key: '/user/profile',
        icon: renderIcon(PersonOutlineIcon),
      },
      {
        label: t('Settings'),
        key: '/user/settings',
        icon: renderIcon(OptionsOutlineIcon),
      }
    ]
  })
  
  return options
})
const route = useRoute()
const activeKey = ref(route.path)
const layoutStore = useLayoutStore()
const deviceStore = useDeviceStore()
const configStore = useConfigStore()
const userStore = useUserStore()
const router = useRouter()
const changeMenu = (key: string, item: MenuOption) => {
  router.push(key).then(() => {
    if (deviceStore.isMobile) {
      layoutStore.setSidebarOpen(false)
    }
  })
}
watch(() => route.path, value => activeKey.value = value)
</script>

<template>
  <Transition>
    <div
      v-if="deviceStore.isMobile && layoutStore.isSidebarOpen"
      class="fixed inset-0 bg-black/50 z-[9] w-full"
      @click="layoutStore.setSidebarOpen(false)"
    />
  </Transition>

  <NLayoutSider
    :collapsed="! layoutStore.isSidebarOpen"
    :collapsed-width="0"
    :native-scrollbar="false"
    :position="deviceStore.isMobile ? 'absolute' : 'static'"
    :width="240"
    bordered
    class="z-10"
    collapse-mode="transform"
    content-class="flex flex-col min-h-full justify-between"
  >
    <div class="flex flex-col pt-16">
      <NLayoutHeader class="absolute top-0 z-[2] flex p-4 justify-center text-xl items-center h-16 overflow-hidden bg-gray-600 border-b border-r border-transparent dark:bg-gray-900 dark:border-gray-700">
        <p class="truncate text-white font-medium cursor-pointer" @click="() => router.push('/')">{{ configStore.configs?.site.title }}</p>
      </NLayoutHeader>

      <NMenu
        v-model:value="activeKey"
        :collapsed="! layoutStore.isSidebarOpen"
        :collapsed-icon-size="22"
        :collapsed-width="0"
        :indent="0"
        :options="menuOptions"
        :root-indent="35"
        :on-update-value="changeMenu"
      />
    </div>

    <!-- 容量展示 -->
    <div class="flex flex-col space-y-1.5 px-6 py-4 text-gray-500 dark:text-gray-300">
      <p>{{ $t('Storage Usage')}} </p>
      <NProgress
        :percentage="((userStore.profile?.used_storage || 0) / (userStore.profile?.total_storage || 0)) * 100"
        :show-indicator="false"
        status="success"
        type="line"
      />
      <p>{{ number.fileSize((userStore.profile?.used_storage || 0) * 1024) }} / {{ number.fileSize((userStore.profile?.total_storage || 0) * 1024) }}</p>
    </div>
  </NLayoutSider>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.25s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>