<script lang="ts" setup>
import {onMounted, ref} from "vue";
import type {Ref} from "vue";
import ExploreImage from "@/components/Explore/ExploreImage.vue";
import {NAvatar, NButton, NInfiniteScroll, useMessage} from "naive-ui";
import {getExploreAlbumsById, getExploreAlbumsByIdPhotos} from "@/api";
import {useRoute, useRouter} from "vue-router";
import {useConfigStore} from "@/stores/config";
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import Report from "@/components/Explore/Report.vue";
import app from "@/utils/app";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const configStore = useConfigStore()

// 检查广场功能是否启用
if (configStore.configs && !configStore.configs?.app?.enable_explore) {
  router.replace('/404')
}

const reportRef = ref<any>(null)
const album = ref<any>({})
const total = ref(0)
const photos: Ref<any[]> = ref([])
const q = ref('')
const page = ref(1)
const loading = ref(false)
const noMore = ref(false)

async function getData(query: any = {}) {
  const result = await getExploreAlbumsById({
    path: {
      id: Number(route.params.id)
    }
  })

  if (result.data?.status === 'error') {
    return message.error(result.data?.message)
  }

  album.value = result.data?.data

  const response = await getExploreAlbumsByIdPhotos({
    path: {
      id: Number(route.params.id),
    },
    query: {
      ...{q: q.value, page: page.value},
      ...query,
    },
  })
  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  photos.value.push(...response.data?.data.data || [])
  loading.value = false
  total.value = response.data?.data.meta.total || 0
  noMore.value = response.data?.data.meta.last_page === response.data?.data.meta.current_page
  page.value++
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
      id: route.params.id
    }
  })
}

onMounted(() => {
  getData()
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
  <Report ref="reportRef" type="album" />

  <Layout
    :toggle-header="false"
    :show-footer="true"
    :header-title="$t('Album Details')"
  >
    <Content>
      <div class="w-full">
        <div class="container mx-auto p-4 sm:p-8">
          <div class="flex flex-col sm:flex-row sm:justify-between mb-10 w-full">
            <div class="flex flex-col">
              <p class="text-2xl mb-4 font-bold">{{ album.name }}</p>
              <div class="flex items-center w-full">
                <NAvatar
                  class="shrink-0"
                  round
                  size="medium"
                  :src="app.getUserAvatar(album.user?.avatar_url)"
                />
                <div class="flex flex-col ml-2 w-full overflow-hidden">
                  {{ album.user?.name }}
                </div>
              </div>
              <p class="mt-4">{{ album.intro }}</p>
            </div>
            <div class="flex justify-end mt-2 sm:mt-0">
              <NButton secondary strong size="small" @click="report">{{ $t('Report') }}</NButton>
            </div>
          </div>

          <p class="my-4">{{ $t('Total {count} photos', {count: total}) }}</p>
        </div>

        <NInfiniteScroll @load="onScroll">
          <div class="masonry-container">
            <ExploreImage :photo="photo" v-for="photo in photos" :key="photo.id" />
          </div>
        </NInfiniteScroll>
      </div>
    </Content>
  </Layout>
</template>