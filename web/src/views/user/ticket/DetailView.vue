<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import {
  NAvatar,
  NButton,
  NCard,
  NCollapse,
  NCollapseItem,
  NDescriptions,
  NDescriptionsItem,
  NIcon,
  NInfiniteScroll,
  NInput,
  NSpin,
  useMessage
} from "naive-ui";
import {computed, onMounted, ref} from "vue";
import {getUserTicketsByIssueNo, getUserTicketsByIssueNoReplies, postUserTicketsByIssueNoReply} from "@/api";
import {useRoute} from "vue-router";
import app from "@/utils/app";
import {ArrowUpOutline} from "@vicons/ionicons5";
import {useUserStore} from "@/stores/user";
import Back from "@/components/Page/Back.vue";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const route = useRoute()
const message = useMessage()
const userStore = useUserStore()
const { t } = useI18n()
const ticket = ref<any>({})
const loading = ref(true)
const page = ref(0)
const replies = ref<any>([])
const noMore = ref(false)
const infiniteScrollRef = ref<InstanceType<typeof NInfiniteScroll>>()
const content = ref('')

function toBottom() {
  setTimeout(() => {
    infiniteScrollRef.value?.scrollbarInstRef?.scrollTo({
      top: infiniteScrollRef.value?.scrollbarInstRef?.contentRef?.scrollHeight || 0,
      behavior: 'smooth'
    })
  }, 50)
}

async function getReplies() {
  if (noMore.value) {
    return
  }

  page.value++

  // 获取回复内容
  loading.value = true
  const result = await getUserTicketsByIssueNoReplies({
    path: {
      issue_no: route.params.issue_no.toString(),
    },
    query: {
      page: 1,
      per_page: 9999,
    }
  })

  replies.value.push(...result.data?.data.data || [])

  loading.value = false

  noMore.value = result.data?.data.current_page === result.data?.data.last_page

  toBottom()
}

async function submit() {
  if (! content.value) {
    return;
  }

  const response = await postUserTicketsByIssueNoReply({
    path: {
      issue_no: route.params.issue_no.toString(),
    },
    body: {
      content: content.value,
      is_notify: true,
    }
  })

  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  replies.value.push({
    content: content.value,
    created_at: new Date(),
    user: {
      avatar: userStore.profile?.avatar_url,
      name: userStore.profile?.name,
    }
  })
  content.value = ''
  toBottom()
}

onMounted(async () => {
  const response = await getUserTicketsByIssueNo({
    path: {
      issue_no: route.params.issue_no.toString(),
    }
  })

  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  ticket.value = response.data?.data || {}

  await getReplies()
})

const levels = computed(() => ({low: t('Low'), medium: t('Medium'), high: t('High')}))
const statuses = computed(() => ({in_progress: t('Processing'), completed: t('Completed')}))
</script>

<template>
  <Layout
    :header-title="$t('Ticket Details')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10 h-full">
      <div class="flex flex-col space-y-2 h-full">
        <div class="flex-none space-x-2">
          <Back to="/user/tickets" />
        </div>

        <NCard>
          <NCollapse
            accordion
            default-expanded-names="info"
          >
            <NCollapseItem :title="$t('Ticket Information')" name="info">
              <NDescriptions size="small" bordered>
                <NDescriptionsItem :label="$t('Ticket Number')">
                  {{ ticket.issue_no }}
                </NDescriptionsItem>
                <NDescriptionsItem :label="$t('Ticket Title')">
                  {{ ticket.title }}
                </NDescriptionsItem>
                <NDescriptionsItem :label="$t('Ticket Level')">
                  {{ levels[ticket.level as keyof typeof levels] }}
                </NDescriptionsItem>
                <NDescriptionsItem :label="$t('Ticket Status')">
                  {{ statuses[ticket.status as keyof typeof statuses] }}
                </NDescriptionsItem>
                <NDescriptionsItem :label="$t('Creation Time')">
                  {{ useDayjs(ticket.created_at).format('L') }}
                </NDescriptionsItem>
              </NDescriptions>
            </NCollapseItem>
          </NCollapse>
        </NCard>

        <NCard class="grow w-full overflow-hidden" content-class="flex flex-col overflow-hidden">

          <NInfiniteScroll ref="infiniteScrollRef" class="grow w-full">
            <NSpin size="small" class="w-full my-10" v-if="loading"/>

            <template v-for="reply in replies">
              <div class="flex space-x-2 overflow-hidden mb-5" v-if="reply.user.id == userStore.profile?.id">
                <div class="flex flex-col items-end space-y-1 grow">
                  <p><span class="text-gray-500 text-xs">{{ useDayjs(reply.created_at).fromNow() }}</span>
                    {{ reply.user.name }}</p>
                  <p class="flex-none w-fit inline-block bg-[#20b2e5] text-white overflow-hidden break-all p-2 rounded-lg md:max-w-[80%]">
                    {{ reply.content }}
                  </p>
                </div>
                <NAvatar class="shrink-0" round :src="app.getUserAvatar(reply.user.avatar_url)"/>
              </div>

              <div class="flex space-x-2 overflow-hidden mb-5" v-else>
                <NAvatar class="shrink-0" round :src="app.getUserAvatar(reply.user.avatar_url)"/>
                <div class="flex flex-col space-y-1 grow">
                  <p>{{ reply.user.name }} <span class="text-gray-500 text-xs">{{
                      useDayjs(reply.created_at).fromNow()
                    }}</span></p>
                  <p class="flex-none w-fit inline-block bg-gray-200 dark:bg-gray-700 overflow-hidden break-all p-2 rounded-lg md:max-w-[80%]">
                    {{ reply.content }}
                  </p>
                </div>
              </div>
            </template>
          </NInfiniteScroll>

          <div class="shrink-0 w-full mt-5">
            <NInput
              v-model:value="content"
              size="large"
              type="textarea"
              :autosize="{minRows: 1, maxRows: 4}"
              class="w-full"
              :placeholder="ticket.status === 'completed' ? $t('Ticket Completed') : $t('Please describe your problem')"
              :disabled="ticket.status === 'completed'"
            >
              <template #suffix>
                <NButton strong secondary circle type="primary" size="small" @click="submit" :disabled="ticket.status === 'completed' || content == ''">
                  <template #icon>
                    <NIcon :size="14" :component="ArrowUpOutline"/>
                  </template>
                </NButton>
              </template>
            </NInput>
          </div>
        </NCard>

      </div>
    </Content>
  </Layout>
</template>