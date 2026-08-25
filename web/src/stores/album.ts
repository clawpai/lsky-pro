import {defineStore} from "pinia";
import {computed, ref} from "vue";
import {getUserAlbums, type GetUserAlbumsData, type GetUserAlbumsResponse} from "@/api";

export type HomeAlbum = GetUserAlbumsResponse['data']['data'][number] & {
  selected?: boolean
}

export const useAlbumStore = defineStore('album', () => {
  const isLoading = ref(false)
  const hasMore = ref(true)
  const page = ref(1)
  const queries = ref<GetUserAlbumsData['query'] & any>({})
  const albums = ref<GetUserAlbumsResponse['data']['data']>([])  // 相册数据

  // 设置分页页码
  function setPage(p: number) {
    page.value = p
  }

  function fetchAlbums(query: GetUserAlbumsData['query'] & any = {}) {
    if (isLoading.value || ! hasMore.value) {
      return;
    }

    isLoading.value = true

    getUserAlbums({
      query: {
        ...{
          page: page.value,
          per_page: 40,
        },
        ...{...queries.value, ...query},
      }
    }).then(result => {
      // 是否还有更多数据
      hasMore.value = result.data?.data.meta.current_page !== result.data?.data.meta.last_page

      const data = result.data?.data.data || []

      setAlbums([...albums.value, ...data])
    }).finally(() => {
      isLoading.value = false
    })
  }

  function resetAlbums(query: GetUserAlbumsData['query'] & any = {}) {
    isLoading.value = false
    hasMore.value = true
    queries.value = query || {}
    setPage(1)
    clearAlbums()
    fetchAlbums()
  }

  function setAlbums(data: GetUserAlbumsResponse['data']['data']) {
    albums.value = data
  }

  function clearAlbums() {
    albums.value = []
  }

  // 删除相册
  function deleteAlbums(ids: any[]) {
    for (let i = 0; i < albums.value.length; i++) {
      if (ids.includes(albums.value[i].id)) {
        albums.value.splice(i, 1)
        i--
      }
    }
  }

  return {
    isLoading,
    page,
    hasMore,
    queries,
    albums,
    setPage,
    setAlbums,
    clearAlbums,
    fetchAlbums,
    resetAlbums,
    deleteAlbums,
  }
})

export const useAlbumSelectorStore = defineStore('albumSelector', () => {
  const isLoading = ref(false)
  const hasMore = ref(true)
  const page = ref(1)
  const queries = ref<GetUserAlbumsData['query'] & any>({})
  const albums = ref<HomeAlbum[]>([])  // 相册数据
  const isAllSelected = computed(() => albums.value.every((album: HomeAlbum) => album.selected)) // 是否全选
  const selections = computed(() => albums.value.filter((album: HomeAlbum) => album.selected)) // 选中的图片

  // 设置分页页码
  function setPage(p: number) {
    page.value = p
  }

  function fetchAlbums(query: GetUserAlbumsData['query'] & any = {}) {
    if (isLoading.value || ! hasMore.value) {
      return;
    }

    isLoading.value = true

    getUserAlbums({
      query: {
        ...{
          page: page.value,
          per_page: 40,
        },
        ...{...queries.value, ...query},
      }
    }).then(result => {
      // 是否还有更多数据
      hasMore.value = result.data?.data.meta.current_page !== result.data?.data.meta.last_page

      const data = result.data?.data.data || []

      setAlbums([...albums.value, ...data])
    }).finally(() => {
      isLoading.value = false
    })
  }

  function resetAlbums(query: GetUserAlbumsData['query'] & any = {}) {
    isLoading.value = false
    hasMore.value = true
    queries.value = query || {}
    setPage(1)
    clearAlbums()
    fetchAlbums()
  }

  function setAlbums(data: GetUserAlbumsResponse['data']['data']) {
    albums.value = data
  }

  function clearAlbums() {
    albums.value = []
  }

  // 选择某条数据
  function toggleSelect(id: number | string) {
    for (const album of albums.value) {
      if (album.id == id) {
        album.selected = !album.selected
      }
    }
  }

  // 选择所有数据
  function selectAll() {
    const selectAll = !isAllSelected.value
    for (const album of albums.value) {
      album.selected = selectAll
    }
  }

  // 取消所有选择
  function unselectAll() {
    for (const album of albums.value) {
      album.selected = false
    }
  }

  return {
    isLoading,
    page,
    hasMore,
    queries,
    albums,
    isAllSelected,
    selections,
    setPage,
    setAlbums,
    clearAlbums,
    fetchAlbums,
    resetAlbums,
    toggleSelect,
    selectAll,
    unselectAll
  }
})