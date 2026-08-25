<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import {useAlbumStore} from "@/stores/album";
import {onMounted, ref, watch} from "vue";
import {NInput, NButton, useMessage, NSelect} from "naive-ui";
import {debounce} from "lodash";
import AlbumList from "@/components/Album/AlbumList.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {postUserAlbums} from "@/api";
import {useRouter} from "vue-router";
import type {SelectMixedOption} from "naive-ui/es/select/src/interface";
import {useI18n} from "vue-i18n";

const message = useMessage()
const router = useRouter()
const albumStore = useAlbumStore()
const { t } = useI18n()

const onScroll = (event: Event) => {
  const container = event.target as HTMLElement;
  const scrollTop = container.scrollTop;
  const clientHeight = container.clientHeight;
  const scrollHeight = container.scrollHeight;

  if (100 + scrollTop + clientHeight >= scrollHeight) {
    if (! albumStore.isLoading) {
      albumStore.setPage(albumStore.page + 1)
      albumStore.fetchAlbums()
    }
  }
}

const orderByOptions: SelectMixedOption[] = [
  {label: t('Newest'), value: 'latest'},
  {label: t('Oldest'), value: 'oldest'},
]

const filters = ref({
  q: '',
  order_by: 'latest',
})

function getData() {
  albumStore.resetAlbums({
    q: `sort:${filters.value.order_by} ${filters.value.q}`,
  })
}

watch(filters, debounce(() => getData(), 500), {
  deep: true,
})

const createAlbum = () => {
  postUserAlbums({
    body: {
      name: t('Create New Album'),
    }
  }).then((response) => {
    router.push(`/user/albums/${response.data?.data.id}`).then(() => {
      message.success(t('Created successfully'))
    })
  })
}

onMounted(() => getData())
</script>

<template>
  <Layout
    :toggle-header="false"
    :show-footer="false"
    :header-title="$t('Album Management')"
    content-class="bg-white dark:bg-[var(--n-color)]"
    :on-scroll="onScroll"
  >
    <Content class="max-w-full">
      <div class="flex items-center justify-between space-x-2 sticky top-0 z-[4] bg-gray-50 border-b border-transparent dark:bg-[var(--n-color)] dark:border-b-gray-800 p-2 w-full">
        <div class="space-x-2">
          <NButton tertiary type="default" @click="createAlbum">
            <template #icon>
              <FontAwesomeIcon icon="fa-solid fa-plus" />
            </template>
            {{ $t('Create Album') }}
          </NButton>
        </div>
        <div class="shrink-0 grow md:grow-0 w-1/2 flex space-x-2">
          <NInput class="grow" v-model:value="filters.q" :placeholder="$t('Enter keywords to search...')" clearable/>
          <NSelect
            class="w-32"
            :placeholder="$t('Sort')"
            :options="orderByOptions"
            v-model:value="filters.order_by"
          />
        </div>
      </div>

      <AlbumList/>
    </Content>
  </Layout>
</template>