<script lang="ts" setup>
import {onMounted, ref, watch} from "vue";
import ExploreImage from "@/components/Explore/ExploreImage.vue";
import ExploreSearch from "@/components/Explore/ExploreSearch.vue";
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import {getExplorePhotos} from "@/api";
import {NEmpty, NInfiniteScroll, useMessage} from "naive-ui";
import {useRoute, useRouter} from "vue-router";
import {useConfigStore} from "@/stores/config";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const configStore = useConfigStore()

// 检查广场功能是否启用
if (configStore.configs && !configStore.configs?.app?.enable_explore) {
  router.replace('/404')
}

const photos = ref<any>([])
const q = ref('')
const page = ref(1)
const loading = ref(false)
const noMore = ref(false)

function resetData() {
  photos.value = []
  q.value = ''
  page.value = 1
  loading.value = false
  noMore.value = false
}

async function getData() {
  loading.value = true

  const response = await getExplorePhotos({
    query: {
      page: page.value,
      q: q.value,
    }
  })

  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  photos.value.push(...response.data?.data.data || [])
  loading.value = false
  noMore.value = response.data?.data.meta.last_page === response.data?.data.meta.current_page
  page.value++
}

onMounted(() => {
  getData()
})

function onScroll() {
  if (loading.value || noMore.value) {
    return
  }

  getData()
}

watch(() => route.query.q, (value) => {
  resetData()
  q.value = value?.toString() || ''
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
  <Layout
    :toggle-header="false"
    :show-footer="true"
    :header-title="$t('Square')"
  >
    <Content>
      <div class="w-full">
        <div class="container mx-auto p-4 sm:p-8">
          <ExploreSearch v-model:value="q"/>
        </div>

        <NInfiniteScroll @load="onScroll" v-if="photos.length > 0">
          <div class="masonry-container">
            <ExploreImage :photo="photo" v-for="photo in photos" :key="photo.id" />
          </div>
        </NInfiniteScroll>

        <div class="flex justify-center mt-20 container mx-auto" v-else>
          <NEmpty :description="$t('No data available')" size="large" />
        </div>
      </div>
    </Content>
  </Layout>
</template>