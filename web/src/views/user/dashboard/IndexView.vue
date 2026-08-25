<script setup lang="ts">
import Content from "@/components/Content.vue";
import {
  NButton,
  NCard,
  NEllipsis,
  useDialog,
  useMessage,
  useModal,
  NAlert
} from "naive-ui"
import {onMounted, ref, h, watch} from "vue";
import {
  deleteUserCapacitiesById,
  deleteUserGroupsById,
  getNotices,
  getNoticesById,
  type GetNoticesResponse, getUserCapacities,
  type GetUserCapacitiesResponse, getUserGroups,
  type GetUserGroupsResponse
} from "@/api";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useUserStore} from "@/stores/user";
import number from "../../../utils/number";
import {useConfigStore} from "@/stores/config";
import Layout from "@/components/Layout.vue";
import {useDayjs} from "@/hooks/useDayjs";
import dayjs from "@/utils/dayjs";
import {useI18n} from "vue-i18n";

const modal = useModal()
const dialog = useDialog()
const message = useMessage()
const userStore = useUserStore()
const configStore = useConfigStore()
const { t } = useI18n()
const availableStorage = Math.max((userStore.profile?.total_storage || 0) - (userStore.profile?.used_storage || 0), 0)
const notices = ref<GetNoticesResponse['data']['data']>([])
const userGroups = ref<GetUserGroupsResponse['data']['data']>([])
const userCapacities = ref<GetUserCapacitiesResponse['data']['data']>([])

const viewNotice = async (id: number) => {
  const result = await getNoticesById({
    path: {id}
  })

  if (! result.data?.status) {
    return message.error(result.data!.message)
  }

  modal.create({
    title: result.data?.data.title,
    preset: 'card',
    class: 'container m-4 md:mx-auto md:my-10 max-w-screen-md',
    content: () => [
      h('article', {class: 'prose dark:prose-invert max-w-none overflow-x-auto', innerHTML: result.data?.data.content}),
      h('p', {class: 'mt-6 text-right'}, useDayjs(result.data?.data.created_at).fromNow())
    ],
  })
}

/**
 * 计算到期时间
 * @param date
 */
const calcExpiredDate = (date: string) => {
  const now = useDayjs(); // 当前时间（响应式）
  const expirationDate = useDayjs(date); // 到期时间（响应式）

  const diffMilliseconds = expirationDate.diff(now); // 计算时间差（毫秒）

  if (diffMilliseconds <= 0) {
    return '已过期';
  }

  const diffDuration = dayjs.duration(diffMilliseconds);

  return `${Math.floor(diffDuration.asDays())} 天 ${diffDuration.hours()} 小时 ${diffDuration.minutes()} 分钟 ${diffDuration.seconds()} 秒`;
}

const delGroup = (id: number) => {
  const d = dialog.warning({
    title: t('Delete Purchased Role Groups'),
    content: t('Warning: This action cannot be undone!'),
    positiveText: t('Confirm'),
    negativeText: t('Cancel'),
    onPositiveClick: () => {
      d.loading = true
      return new Promise((resolve) => {
        deleteUserGroupsById({
          path: {id},
        }).then(() => {
          getUserGroups().then(response => {
            userGroups.value = response.data?.data.data || []
            resolve(1)
          })
        })
      })
    }
  })
}

const delCapacity = (id: number) => {
  const d = dialog.warning({
    title: t('Delete Purchased Storage'),
    content: t('Warning: This action cannot be undone!'),
    positiveText: t('Confirm'),
    negativeText: t('Cancel'),
    onPositiveClick: () => {
      d.loading = true
      return new Promise((resolve) => {
        deleteUserCapacitiesById({
          path: {id},
        }).then(() => {
          getUserCapacities().then(response => {
            userCapacities.value = response.data?.data.data || []
            resolve(1)
          })
        })
      })
    }
  })
}

onMounted(() => {
  fetchDashboardData()
})

// 添加对用户资料变化的监听
watch(() => userStore.profile, () => {
  // 当用户资料更新时，刷新容量和存储数据
  getUserCapacities({
    query: {
      page: 1,
      per_page: 99
    }
  }).then(response => {
    userCapacities.value = response.data?.data.data || []
  })
}, { deep: true })

// 抽取数据获取逻辑到独立函数
function fetchDashboardData() {
  getNotices({
    query: {
      page: 1,
      per_page: 999
    }
  }).then(response => {
    notices.value = (response.data?.data.data || [])
  })

  getUserGroups({
    query: {
      page: 1,
      per_page: 99
    }
  }).then(response => {
    userGroups.value = response.data?.data.data || []
  })

  getUserCapacities({
    query: {
      page: 1,
      per_page: 99
    }
  }).then(response => {
    userCapacities.value = response.data?.data.data || []
  })
}
</script>

<template>
  <Layout
    :header-title="$t('Dashboard')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NCard title="站内公告" v-if="notices.length > 0">
        <div
          v-for="notice in notices"
          class="flex justify-between py-1 px-2 w-full transition-all rounded hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
          @click="viewNotice(notice.id || 0)"
        >
          <NEllipsis>{{ notice.title }}</NEllipsis>
          <span class="text-gray-400 flex shrink-0 ml-2">{{ useDayjs(notice.created_at).fromNow() }}</span>
        </div>
      </NCard>

      <div class="space-y-6 md:space-y-0 md:grid md:grid-cols-2 xl:grid-cols-4 md:gap-x-4 xl:gap-x-8 md:gap-y-4 xl:gap-y-8">
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-images" class="text-amber-500 text-5xl" />
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Number of Photos') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.format(userStore.profile?.photo_count || 0) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-hdd" class="text-red-500 text-5xl"/>
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Available Storage') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.fileSize(availableStorage * 1024) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-hdd" class="text-green-500 text-5xl" />
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Used Storage') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.fileSize((userStore.profile?.used_storage || 0) * 1024) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-hdd" class="text-emerald-500 text-5xl"/>
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Total Storage') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.fileSize((userStore.profile?.total_storage || 0) * 1024) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-folder" class="text-blue-500 text-5xl" />
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Album Count') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.format(userStore.profile?.album_count || 0) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-share-alt" class="text-purple-500 text-5xl" />
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Share Count') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.format(userStore.profile?.share_count || 0) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-receipt" class="text-orange-500 text-5xl" />
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Order Count') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.format(userStore.profile?.order_count || 0) }}</p>
          </div>
        </NCard>
        <NCard content-class="flex space-x-4">
          <FontAwesomeIcon icon="fas fa-life-ring" class="text-pink-500 text-5xl" />
          <div class="flex flex-col">
            <p class="text-gray-700 text-sm dark:text-gray-400">{{ $t('Ticket Count') }}</p>
            <p class="text-gray-800 font-semibold text-xl dark:text-gray-200">{{ number.format(userStore.profile?.ticket_count || 0) }}</p>
          </div>
        </NCard>
      </div>

      <NCard :title="$t('Owned Role Groups')" content-class="space-y-2" v-if="userGroups.length > 0">
        <div class="relative flex flex-col p-4 bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-slate-800/70 rounded-md" v-for="userGroup in userGroups">
          <div class="flex justify-between">
            <div class="space-y-2">
              <h2 class="text-xl text-gray-900 dark:text-gray-100">{{ userGroup.group?.name }}</h2>
              <p class="text-sm text-gray-800 dark:text-gray-100" v-if="userGroup.group?.intro">{{ userGroup.group?.intro }}</p>
              <p class="text-xs text-gray-600 dark:text-gray-200" v-if="userGroup.from === 'subscribe'">
                <span>{{ $t('Purchased on {date}', {date: useDayjs(userGroup.created_at).format('LLL')}) }}</span>
                <span v-if="userGroup.expired_at">, {{ $t('Expires on {expired_at}, {day} remaining', {
                  expired_at: useDayjs(userGroup.expired_at).format('LLL'),
                  day: calcExpiredDate(userGroup.expired_at)
                }) }}。</span>
              </p>
              <p class="text-xs text-gray-600 dark:text-gray-200" v-else>{{ $t('This role group is gifted by the system') }}</p>
            </div>
            <div v-if="userGroup.from !== 'system'">
              <NButton
                type="error"
                size="small"
                strong
                secondary
                @click="delGroup(userGroup.id || 0)"
              >
                {{ $t('Delete') }}
              </NButton>
            </div>
          </div>
        </div>
      </NCard>

      <NCard :title="$t('Owned Storage')" content-class="space-y-2" v-if="userCapacities.length > 0">
        <div class="relative flex flex-col p-4 bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-slate-800/70 rounded-md" v-for="userCapacity in userCapacities">
          <div class="flex justify-between">
            <div class="space-y-2">
              <h2 class="text-xl text-gray-900 dark:text-gray-100">{{ number.fileSize((userCapacity.capacity || 0) * 1024) }}</h2>
              <p class="text-xs text-gray-600 dark:text-gray-200" v-if="userCapacity.from === 'subscribe'">
                <span>{{ $t('Purchased on {date}', {date: useDayjs(userCapacity.created_at).format('LLL')}) }}</span>
                <span v-if="userCapacity.expired_at">, {{ $t('Expires on {expired_at}, {day} remaining', {
                  expired_at: useDayjs(userCapacity.expired_at).format('LLL'),
                  day: calcExpiredDate(userCapacity.expired_at)
                }) }}。</span>
              </p>
              <p class="text-xs text-gray-600 dark:text-gray-200" v-else>{{ $t('This storage is gifted by the system') }}</p>
            </div>
            <div v-if="userCapacity.from !== 'system'">
              <NButton
                type="error"
                size="small"
                strong
                secondary
                @click="delCapacity(userCapacity.id || 0)"
              >
                {{ $t('Delete') }}
              </NButton>
            </div>
          </div>
        </div>
      </NCard>

      <div class="flex md:mt-8 flex-col-reverse md:flex-row space-y-6 md:space-y-0 md:space-x-4">
        <div class="w-full mt-4 md:mt-0">
          <NCard :title="$t('Available Policies')" content-class="divide-y dark:divide-slate-700 divide-solid space-y-2" v-if="configStore.group?.storages">
            <div class="w-full p-4 bg-slate-50 dark:bg-gray-800 hover:bg-slate-100 dark:hover:bg-slate-800/70 rounded-md" v-for="storage in configStore.group?.storages">
              <p class="dark:text-gray-400">{{ storage.name }}</p>
              <span class="text-gray-700 text-xs dark:text-gray-100">{{ storage.intro }}</span>
            </div>
          </NCard>
        </div>
        <div class="flex flex-col md:w-[70%] xl:w-[40%] space-y-8">
          <NCard :title="$t('My Information')" content-class="space-y-3">
            <div class="flex">
              <p class="basis-1/3 dark:text-gray-400">{{ $t('Full Name') }}</p>
              <p class="basis-2/3 truncate text-gray-800 dark:text-gray-100">{{ userStore.profile?.name }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/3 dark:text-gray-400">{{ $t('Email') }}</p>
              <p class="basis-2/3 truncate text-gray-800 dark:text-gray-100">{{ userStore.profile?.email }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/3 dark:text-gray-400">{{ $t('Registration Time') }}</p>
              <p class="basis-2/3 truncate text-gray-800 dark:text-gray-100">{{ useDayjs(userStore.profile?.created_at).format('LLL') }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/3 dark:text-gray-400">{{ $t('Login IP') }}</p>
              <p class="basis-2/3 truncate text-gray-800 dark:text-gray-100">{{ userStore.profile?.login_ip }}</p>
            </div>
          </NCard>
          
          <!-- 邮箱或手机验证提示 -->
          <NCard v-if="(configStore.configs?.app.user_email_verify && !userStore.profile?.email_verified_at) || 
                       (configStore.configs?.app.user_phone_verify && !userStore.profile?.phone_verified_at)">
            <div class="space-y-4">
              <NAlert type="warning" :show-icon="true" title="">
                <template #icon>
                  <FontAwesomeIcon icon="fas fa-exclamation-triangle" />
                </template>
                <div class="space-y-2">
                  <p class="font-medium" v-if="configStore.configs?.app.user_email_verify && !userStore.profile?.email_verified_at">
                    {{ $t('Your email has not been verified. Please verify your email to ensure account security.') }}
                  </p>
                  <p class="font-medium" v-if="configStore.configs?.app.user_phone_verify && !userStore.profile?.phone_verified_at">
                    {{ $t('Your phone number has not been verified. Please verify your phone number to ensure account security.') }}
                  </p>
                </div>
              </NAlert>
              
              <div class="flex flex-wrap gap-2">
                <NButton v-if="configStore.configs?.app.user_email_verify && !userStore.profile?.email_verified_at" 
                         type="primary" 
                         @click="$router.push('/user/profile?tab=email')">
                  {{ $t('Verify Email') }}
                </NButton>
                <NButton v-if="configStore.configs?.app.user_phone_verify && !userStore.profile?.phone_verified_at" 
                         type="primary" 
                         @click="$router.push('/user/profile?tab=phone')">
                  {{ $t('Verify Phone') }}
                </NButton>
              </div>
            </div>
          </NCard>
          
          <NCard :title="$t('Current Role Group Information')" content-class="space-y-3" v-if="configStore.group?.group">
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Group Name') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ configStore.group?.group.name }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Maximum File Size') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ number.fileSize(configStore.group?.group.options.max_upload_size * 1024) }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Concurrent Uploads') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ $t('{count} Pictures', {count: number.format(configStore.group?.group.options.limit_concurrent_upload)}) }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Uploads per Minute Limit') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ $t('{count} Pictures', {count: number.format(configStore.group?.group.options.limit_per_minute)}) }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Uploads per Hour Limit') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ $t('{count} Pictures', {count: number.format(configStore.group?.group.options.limit_per_hour)}) }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Daily Upload Limit') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ $t('{count} Pictures', {count: number.format(configStore.group?.group.options.limit_per_day)}) }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Weekly Upload Limit') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ $t('{count} Pictures', {count: number.format(configStore.group?.group.options.limit_per_week)}) }}</p>
            </div>
            <div class="flex">
              <p class="basis-1/2 dark:text-gray-400">{{ $t('Monthly Upload Limit') }}</p>
              <p class="basis-1/2 truncate text-gray-800 dark:text-gray-100">{{ $t('{count} Pictures', {count: number.format(configStore.group?.group.options.limit_per_month)}) }}</p>
            </div>
          </NCard>
        </div>
      </div>
    </Content>
  </Layout>
</template>