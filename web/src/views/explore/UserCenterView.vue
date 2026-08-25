<script setup lang="ts">
import {NTabs, NTab, NIcon, useMessage, NInfiniteScroll} from "naive-ui"
import UserInfo from "@/components/User/UserInfo.vue";
import {
  AlbumsOutline as AlbumsOutlineIcon,
  HeartOutline as HeartOutlineIcon,
  ImageOutline as ImageOutlineIcon
} from "@vicons/ionicons5";
import {onMounted, ref, watch} from "vue";
import type {Ref} from "vue";
import ExploreImage from "@/components/Explore/ExploreImage.vue";
import ExploreAlbum from "@/components/Explore/ExploreAlbum.vue";
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import {getExploreAlbums, getExplorePhotos, getExploreUsersByUsername} from "@/api";
import {useRoute, useRouter} from "vue-router";
import {useConfigStore} from "@/stores/config";

const route = useRoute()
const router = useRouter()
const configStore = useConfigStore()

// 检查广场功能是否启用
if (configStore.configs && !configStore.configs?.app?.enable_explore) {
  router.replace('/404')
}

const user = ref<any>({})
const tab = ref<'photo' | 'album'>('photo')
const message = useMessage()

const data: Ref<any[]> = ref([])
const q = ref('')
const page = ref(1)
const loading = ref(false)
const noMore = ref(false)

function resetData() {
  data.value = []
  q.value = ''
  page.value = 1
  loading.value = false
  noMore.value = false
}

async function getData(query: any = {}) {
  const methods = {
    photo: getExplorePhotos,
    album: getExploreAlbums,
  }

  const response = await methods[tab.value]({
    query: {
      ...{q: q.value, page: page.value, username: user.value.username},
      ...query,
    },
  })
  if (response.data?.status === 'error') {
    return message.error(response.data?.message)
  }

  data.value.push(...response.data?.data.data || [])
  loading.value = false
  noMore.value = response.data?.data.meta.last_page === response.data?.data.meta.current_page
  page.value++
}

function onScroll() {
  if (loading.value || noMore.value) {
    return
  }

  getData()
}

watch(tab, (value) => {
  resetData()
  getData()
})

onMounted(() => {
  getExploreUsersByUsername({
    path: {
      username: route.params.username.toString(),
    }
  }).then(response => {
    if (response.data?.status === 'error') {
      router.back()
      return message.error(response.data?.message)
    }

    user.value = response.data?.data
    getData()
  })
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
      <div class="flex w-full justify-center my-10 xl:px-60">
        <UserInfo :user="user" />
      </div>

      <div class="w-full px-6">
        <NTabs type="line" animated v-model:value="tab">
          <NTab name="photo">
            <div class="flex items-center space-x-2 text-md">
              <NIcon :size="20" :component="ImageOutlineIcon"/>
              <span>{{ $t('Photo') }} {{ user.photo_count > 999 ? '999+' : user.photo_count }}</span>
            </div>
          </NTab>
          <NTab name="album">
            <div class="flex items-center space-x-2 text-md">
              <NIcon :size="20" :component="AlbumsOutlineIcon"/>
              <span>{{ $t('Album') }} {{ user.album_count > 999 ? '999+' : user.album_count }}</span>
            </div>
          </NTab>
        </NTabs>
      </div>

      <div class="my-10">
        <NInfiniteScroll @load="onScroll" v-if="tab === 'photo'">
          <div class="masonry-container">
            <ExploreImage :photo="photo" v-for="photo in data" :key="photo.id" />
          </div>
        </NInfiniteScroll>

        <div class="px-6" v-if="tab === 'album'">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-8">
            <ExploreAlbum :album="album" v-for="album in data" />
          </div>
        </div>
      </div>
    </Content>
  </Layout>
</template>