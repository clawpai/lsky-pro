<script lang="ts" setup>
import {onMounted, ref} from "vue";
import type {Ref} from "vue";
import ExploreImage from "@/components/Explore/ExploreImage.vue";
import {NCard, NIcon, NAvatar, NButton, NInputGroup, NInput, NInfiniteScroll, useMessage} from "naive-ui";
import {getSharesBySlug, getSharesBySlugPhotos} from "@/api";
import {useRoute, useRouter} from "vue-router";
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import Report from "@/components/Explore/Report.vue";
import {AlertCircle} from "@vicons/ionicons5";
import {useLayoutStore} from "@/stores/layout";
import app from "@/utils/app";
import {useI18n} from "vue-i18n";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const layoutStore = useLayoutStore()
const { t } = useI18n()

const password = ref<string>(String(route.query.p || ''))
const reportRef = ref<any>(null)
const share = ref<any>({})
const total = ref(0)
const photos: Ref<any[]> = ref([])
const q = ref('')
const page = ref(1)
const loading = ref(false)
const verification = ref(true)
const noMore = ref(false)

async function getShare() {
  const result = await getSharesBySlug({
    path: {
      slug: route.params.slug.toString(),
    },
    query: {
      password: password.value,
    }
  })

  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }

  if (! result.data?.data.is_valid && password.value) {
    message.error(t('Incorrect password'))
  }

  return result.data?.data
}

async function getData(query: any = {}) {
  if (share.value.is_valid) {
    const response = await getSharesBySlugPhotos({
      path: {
        slug: share.value.slug,
      },
      query: {
        ...{q: q.value, page: page.value, password: password.value},
        ...query,
      },
    })

    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    if (! response.data?.data.is_valid && password.value) {
      message.error(t('Incorrect password'))
    } else {
      photos.value.push(...response.data?.data.data || [])
      loading.value = false
      total.value = response.data?.data.meta.total || 0
      noMore.value = response.data?.data.meta.last_page === response.data?.data.meta.current_page
      page.value++
    }
  }
}

function onScroll() {
  if (loading.value || noMore.value) {
    return
  }

  getData()
}

function report() {
  reportRef.value?.open({
    path: {
      slug: route.params.slug.toString()
    }
  })
}

async function submit() {
  share.value = await getShare()
  await getData()

  if (share.value.is_valid) {
    await router.replace(`/shares/${route.params.slug}?p=${password.value}`)
  }
}

function toUser() {
  router.push(`/explore/@${share.value.user.username}`).then(() => layoutStore.setSidebarOpen(false))
}

onMounted(async () => {
  verification.value = true
  share.value = await getShare()
  verification.value = false
  await getData()
})
</script>

<style scoped>
.masonry-container {
  column-count: 2;
  column-gap: 2px;
}

@media (min-width: 640px) {
  .masonry-container {
    column-count: 3;
    column-gap: 2px;
  }
}

@media (min-width: 768px) {
  .masonry-container {
    column-count: 4;
    column-gap: 2px;
  }
}

@media (min-width: 1024px) {
  .masonry-container {
    column-count: 5;
    column-gap: 2px;
  }
}

@media (min-width: 1280px) {
  .masonry-container {
    column-count: 6;
    column-gap: 2px;
  }
}

@media (min-width: 1536px) {
  .masonry-container {
    column-count: 7;
    column-gap: 2px;
  }
}

.masonry-container > * {
  break-inside: avoid;
  margin-bottom: 2px;
}
</style>

<template>
  <Report ref="reportRef" type="share" />

  <Layout
    :toggle-header="false"
    :show-footer="true"
    :header-title="$t('Share Details')"
  >
    <Content>
      <div class="container mx-auto p-4 sm:p-8 w-full" v-if="verification">
        <div class="flex flex-col justify-center items-center mt-20">
          {{ $t('Verifying...') }}
        </div>
      </div>

      <div class="w-full" v-else-if="share.is_valid">
        <div class="container mx-auto p-4 sm:p-8">
          <div class="flex flex-col sm:flex-row sm:justify-between mb-10 w-full">
            <div class="flex flex-col">
              <p class="text-2xl mb-4 font-bold">{{ share?.album?.name || share.content }}</p>
              <div class="flex items-center w-full">
                <NAvatar
                  class="shrink-0"
                  round
                  size="medium"
                  :src="app.getUserAvatar(share.user?.avatar_url || '')"
                />
                <div class="flex flex-col ml-2 w-full overflow-hidden cursor-pointer" @click="toUser">
                  {{ share.user?.name }}
                </div>
              </div>
              <p class="mt-4">{{ share?.album?.intro }}</p>
            </div>
            <div class="flex justify-end mt-2 sm:mt-0">
              <NButton secondary strong size="small" @click="report">{{ $t('Report') }}</NButton>
            </div>
          </div>

          <p class="my-4">{{ $t('Total {count} photos', {count: total}) }}</p>
        </div>

        <NInfiniteScroll @load="onScroll">
          <div class="masonry-container">
            <ExploreImage :show-user-info="false" :show-like="false" :show-report="false" :photo="photo" v-for="photo in photos" :key="photo.id" />
          </div>
        </NInfiniteScroll>
      </div>

      <div class="container mx-auto p-4 sm:p-8 w-full" v-else>
        <div class="flex flex-col justify-center items-center mt-20">
          <div class="w-[300px] md:w-[400px]">
            <NCard size="small">
              <p class="flex items-center space-x-1 text-gray-500 mb-2">
                <NIcon :component="AlertCircle" :size="16" />
                <span>{{ $t('This content requires a password to access') }}</span>
              </p>
              <NInputGroup>
                <NInput
                  type="password"
                  @keyup.enter="submit"
                  v-model:value="password"
                  :placeholder="$t('Please enter the access password')"
                  show-password-on="click"
                />
                <NButton @click="submit">{{ $t('Open Share') }}</NButton>
              </NInputGroup>
            </NCard>
          </div>
        </div>
      </div>
    </Content>
  </Layout>
</template>