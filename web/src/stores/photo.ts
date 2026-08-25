import {defineStore} from "pinia";
import type {Photo} from "vue-photo-album";
import {computed, ref} from "vue";
import {
  deleteUserPhotos,
  getUserPhotos,
  type GetUserPhotosData,
  type GetUserPhotosResponse,
  postUserAlbumsByIdPhotos
} from "@/api";
import str from "@/utils/str";
import type AlbumSelector from "@/components/Album/AlbumSelector.vue";
import type {HomeAlbum} from "@/stores/album";
import image from "@/utils/image";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";
import {useUserStore} from "@/stores/user";

export type UserPhoto = Photo & {
  selected?: boolean,
}

export type UserPhotoGroup = {
  date: string,
  selected?: boolean,
  photos: UserPhoto[],
}

/**
 * 图片 store
 * @remarks 图片管理列表与相册详情中共用此 store，区别在于获取列表时的筛选条件不同，这意味着这两个模块不能同时出现。
 */
export const usePhotoStore = defineStore('photo', () => {
  const isLoading = ref(false)
  const hasMore = ref(true)
  const queries = ref<GetUserPhotosData['query'] & any>({})
  const page = ref(1)
  const groups = ref<UserPhotoGroup[]>([])  // 图片分组数据
  const isAllSelected = computed(() => groups.value.every(group => group.selected)) // 是否全选
  const selections = computed(() => groups.value.flatMap(group => group.photos.filter(photo => photo.selected))) // 选中的图片
  const { t } = useI18n()

  const editModalActive = ref(false)
  const shareModalActive = ref(false)
  const albumSelectorRef = ref<InstanceType<typeof AlbumSelector> | null>(null)

  const userStore = useUserStore()

  function setQueries(q: GetUserPhotosData['query']) {
    queries.value = q
  }

  // 获取图片列表
  function fetchPhotos(query: GetUserPhotosData['query'] & any = {}) {
    if (isLoading.value || !hasMore.value) {
      return;
    }

    isLoading.value = true;

    getUserPhotos({
      query: {
        ...{
          page: page.value,
          per_page: 40,
        },
        ...{ ...queries.value, ...query },
      },
    })
      .then((result) => {
        // 是否还有更多数据
        hasMore.value = result.data?.data.meta.current_page !== result.data?.data.meta.last_page;

        const photos = (result.data?.data.data || []) as GetUserPhotosResponse['data']['data'];

        // 判断 groups 中是否有该日期(created_at)的分组，有则push图片数据，否则创建分组
        for (const photo of photos) {
          const date = useDayjs(photo.created_at).format('L');
          const group = groups.value.find((group) => group.date === date);
          const data = {
            ...{
              key: photo.id.toString(),
              selected: false,
              src: userStore.profile!.options?.show_original_photos ? photo.public_url : (photo.thumbnail_url || photo.public_url),
            },
            ...photo,
          };

          if (group) {
            // 检查该图片是否已经存在于该分组，避免重复
            const isDuplicate = group.photos.some(
              (existingPhoto: any) => existingPhoto.id == photo.id
            );
            if (!isDuplicate) {
              group.photos.push(data);
            }
          } else {
            groups.value.push({
              date: date,
              photos: [data],
            });
          }
        }
      })
      .finally(() => {
        isLoading.value = false;
      });
  }

  // 重置并刷新列表
  function resetPhotos(query: GetUserPhotosData['query'] & any = {}) {
    isLoading.value = false
    hasMore.value = true
    queries.value = query || {}
    setPage(1)
    clearGroups()
    fetchPhotos()
  }

  // 设置分页页码
  function setPage(p: number) {
    page.value = p
  }

  // 设置分组数据
  function setGroups(groupList: UserPhotoGroup[]) {
    groups.value = groupList
  }

  // 清空分组数据
  function clearGroups() {
    groups.value = []
  }

  // 设置某个时间节点的图片
  function pushGroup(date: string, photos: UserPhoto[]) {
    groups.value.push({date, photos})
  }

  // 选择某条数据
  function toggleSelect(key?: string) {
    for (const group of groups.value) {
      const photo = group.photos.find(photo => photo.key === key)
      if (photo) {
        photo.selected = !photo.selected
        group.selected = group.photos.every(photo => photo.selected)
      }
    }
  }

  // 选择某个组里的所有图片
  function toggleSelectAllByGroup(date: string) {
    const group = groups.value.find(group => group.date === date)
    if (group) {
      group.selected = !group.selected
      group.photos.forEach(photo => photo.selected = group.selected)
    }
  }

  // 删除某些数据
  function deletePhotos(key: any[]) {
    for (let i = groups.value.length - 1; i >= 0; i--) { // 逆序循环组
      for (let j = groups.value[i].photos.length - 1; j >= 0; j--) { // 逆序循环照片
        if (key.includes(groups.value[i].photos[j].key)) {
          groups.value[i].photos.splice(j, 1);

          // 删除组
          if (groups.value[i].photos.length === 0) {
            groups.value.splice(i, 1);
            break; // 组被删除，跳出当前组的循环
          }
        }
      }
    }
  }

  // 选择所有数据
  function selectAll() {
    const selectAll = !isAllSelected.value
    for (const group of groups.value) {
      group.selected = selectAll
      group.photos.forEach(photo => photo.selected = selectAll)
    }
  }

  // 取消所有选择
  function unselectAll() {
    groups.value.forEach(group => {
      group.selected = false
      group.photos.forEach(photo => photo.selected = false)
    })
  }

  // 复制图片
  function copyImage(imageSource: string) {
    return image.copyImage(imageSource)
  }

  // 复制选中项链接
  function copyLink(key: 'url' | 'html' | 'bbcode' | 'markdown' | 'markdown_with_link' | 'thumbnail_url') {
    let text = ''
    selections.value.forEach((photo: any) => {
      let url = key === 'thumbnail_url' ? photo.thumbnail_url : photo.public_url
      
      // 如果用户设置了复制链接时需要编码URL
      if (userStore.profile?.options && 'encode_copied_url' in userStore.profile.options && userStore.profile.options.encode_copied_url) {
        url = encodeURI(url)
      }
      
      const content = {
        url: url,
        html: `<img src="${url}" alt="${photo.key}" />`,
        bbcode: `[img]${url}[/img]`,
        markdown: `![${photo.key}](${url})`,
        markdown_with_link: `[![${photo.key}](${url})](${url})`,
        thumbnail_url: photo.thumbnail_url
      }[key]

      text += content + '\r\n'
    })

    return str.copyText(text.replace(/\r\n$/, ''))
  }

  // 打开图片
  function openPhoto(photo: UserPhoto) {
    // 确保使用原始图片URL
    // TypeScript类型转换，因为UserPhoto类型可能没有包含所有API返回的字段
    const fullPhoto = photo as any;
    window.open(fullPhoto.public_url || photo.src)
  }

  // 删除选中项图片
  async function deleteSelectedPhotos() {
    return deleteUserPhotos({
      body: selections.value.map((photo: any) => photo.id),
    }).then((res) => {
      deletePhotos(selections.value.map(photo => photo.key))
      // 删除成功后更新用户信息，确保存储容量数据实时更新
      userStore.fetchUserProfile()
      return res
    })
  }

  // 设置编辑图片信息的 modal 显示状态
  function setEditModalActive(state: boolean) {
    editModalActive.value = state
  }

  // 设置分享操作的 modal 显示状态
  function setShareModalActive(state: boolean) {
    shareModalActive.value = state
  }

  // 设置相册选择器的 ref
  function setAlbumSelectorRef(albumRef: any) {
    albumSelectorRef.value = albumRef
  }

  // 将已选择的图片添加到相册中
  async function selectedToAlbum(albumSelections: HomeAlbum[]) {
    const promises = albumSelections.map((album: any) => {
      return postUserAlbumsByIdPhotos({
        path: {
          id: album.id,
        },
        body: selections.value.map((photo: any) => photo.id),
      })
    })
    
    return Promise.all(promises)
  }

  return {
    isLoading,
    hasMore,
    page,
    setPage,
    setQueries,
    queries,
    groups,
    selections,
    isAllSelected,
    fetchPhotos,
    resetPhotos,
    setGroups,
    toggleSelect,
    selectAll,
    clearGroups,
    pushGroup,
    toggleSelectAllByGroup,
    unselectAll,
    deletePhotos,
    copyImage,
    copyLink,
    openPhoto,
    deleteSelectedPhotos,
    editModalActive,
    setEditModalActive,
    shareModalActive,
    setShareModalActive,
    albumSelectorRef,
    setAlbumSelectorRef,
    selectedToAlbum,
  }
})

export const usePhotoSelectorStore = defineStore('photoSelector', () => {
  const isLoading = ref(false)
  const hasMore = ref(true)
  const queries = ref<GetUserPhotosData['query'] & any>({})
  const page = ref(1)
  const groups = ref<UserPhotoGroup[]>([])  // 图片分组数据
  const isAllSelected = computed(() => groups.value.every(group => group.selected)) // 是否全选
  const selections = computed(() => groups.value.flatMap(group => group.photos.filter(photo => photo.selected))) // 选中的图片

  const userStore = useUserStore()

  function setQueries(q: GetUserPhotosData['query']) {
    queries.value = q
  }

  // 获取图片列表
  function fetchPhotos(query: GetUserPhotosData['query'] & any = {}) {
    if (isLoading.value || !hasMore.value) {
      return;
    }

    isLoading.value = true;

    getUserPhotos({
      query: {
        ...{
          page: page.value,
          per_page: 40,
        },
        ...{ ...queries.value, ...query },
      },
    })
      .then((result) => {
        // 是否还有更多数据
        hasMore.value =
          result.data?.data.meta.current_page !== result.data?.data.meta.last_page;

        const photos = (result.data?.data.data || []) as GetUserPhotosResponse['data']['data'];

        // 判断 groups 中是否有该日期(created_at)的分组，有则push图片数据，否则创建分组
        for (const photo of photos) {
          const date = useDayjs(photo.created_at).format('L');
          const group = groups.value.find((group) => group.date === date);
          const data = {
            ...{
              key: photo.id.toString(),
              selected: false,
              src: userStore.profile!.options?.show_original_photos ? photo.public_url : (photo.thumbnail_url || photo.public_url),
            },
            ...photo,
          };

          if (group) {
            // 检查该图片是否已经存在于该分组，避免重复
            const isDuplicate = group.photos.some(
              (existingPhoto: any) => existingPhoto.id == photo.id
            );
            if (!isDuplicate) {
              group.photos.push(data);
            }
          } else {
            groups.value.push({
              date: date,
              photos: [data],
            });
          }
        }
      })
      .finally(() => {
        isLoading.value = false;
      });
  }

  // 重置并刷新列表
  function resetPhotos(query: GetUserPhotosData['query'] & any = {}) {
    isLoading.value = false
    hasMore.value = true
    queries.value = query || {}
    setPage(1)
    clearGroups()
    fetchPhotos()
  }

  // 设置分页页码
  function setPage(p: number) {
    page.value = p
  }

  // 设置分组数据
  function setGroups(groupList: UserPhotoGroup[]) {
    groups.value = groupList
  }

  // 清空分组数据
  function clearGroups() {
    groups.value = []
  }

  // 设置某个时间节点的图片
  function pushGroup(date: string, photos: UserPhoto[]) {
    groups.value.push({date, photos})
  }

  // 选择某条数据
  function toggleSelect(key?: string) {
    for (const group of groups.value) {
      const photo = group.photos.find(photo => photo.key === key)
      if (photo) {
        photo.selected = !photo.selected
        group.selected = group.photos.every(photo => photo.selected)
      }
    }
  }

  // 选择某个组里的所有图片
  function toggleSelectAllByGroup(date: string) {
    const group = groups.value.find(group => group.date === date)
    if (group) {
      group.selected = !group.selected
      group.photos.forEach(photo => photo.selected = group.selected)
    }
  }

  // 选择所有数据
  function selectAll() {
    const selectAll = !isAllSelected.value
    for (const group of groups.value) {
      group.selected = selectAll
      group.photos.forEach(photo => photo.selected = selectAll)
    }
  }

  // 取消所有选择
  function unselectAll() {
    groups.value.forEach(group => {
      group.selected = false
      group.photos.forEach(photo => photo.selected = false)
    })
  }

  return {
    isLoading,
    hasMore,
    page,
    setPage,
    setQueries,
    queries,
    groups,
    selections,
    isAllSelected,
    fetchPhotos,
    resetPhotos,
    setGroups,
    toggleSelect,
    selectAll,
    clearGroups,
    pushGroup,
    toggleSelectAllByGroup,
    unselectAll,
  }
})