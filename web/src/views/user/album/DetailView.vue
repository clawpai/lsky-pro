<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import {NInput, NButton, NIcon, NSwitch, useMessage, NModal, NForm, NFormItem, NDatePicker} from "naive-ui";
import {type UserPhoto, usePhotoStore} from "@/stores/photo";
import {onMounted, ref, watch} from "vue";
import {debounce} from "lodash";
import {useRoute, useRouter} from "vue-router";
import PhotoList from "@/components/Photo/PhotoList.vue";
import {getUserAlbumsById, type GetUserAlbumsByIdResponse, postUserAlbumsByIdPhotos, putUserAlbumsById} from "@/api";
import PhotoOperates from "@/components/PhotoOperates/PhotoOperates.vue";
import Cancel from "@/components/PhotoOperates/PhotoCancel.vue";
import PhotoSelector from "@/components/PhotoSelector/PhotoSelector.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import AlbumDelete from "@/components/AlbumOperates/Delete.vue";
import Share from "@/components/AlbumOperates/Share.vue";
import Select from "@/components/PhotoOperates/PhotoSelect.vue";
import CopyLink from "@/components/PhotoOperates/PhotoCopyLink.vue";
import Edit from "@/components/PhotoOperates/PhotoEdit.vue";
import Remove from "@/components/PhotoOperates/PhotoRemove.vue";
import PhotoDelete from "@/components/PhotoOperates/PhotoDelete.vue";
import Back from "@/components/Page/Back.vue";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";
import str from "@/utils/str";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {postUserShares} from "@/api";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const { t } = useI18n()
const isInit = ref(false)
const photoSelectorRef = ref<InstanceType<typeof PhotoSelector>>()
const deleteRef = ref<InstanceType<typeof AlbumDelete>>()
const shareRef = ref<InstanceType<typeof Share>>()
const album = ref<GetUserAlbumsByIdResponse['data'] & any>({})
const photoStore = usePhotoStore()

// 新增分享模态框相关状态
const shareUrl = ref('')
const formData = ref<any>({
  type: 'photo',
  ids: [], // 会在submit时设置
  content: '',
  password: '',
  expired_at: null
})
const formRules: FormRules = {}

const onScroll = (event: Event) => {
  const container = event.target as HTMLElement;
  const scrollTop = container.scrollTop;
  const clientHeight = container.clientHeight;
  const scrollHeight = container.scrollHeight;

  if (100 + scrollTop + clientHeight >= scrollHeight) {
    if (! photoStore.isLoading) {
      photoStore.setPage(photoStore.page + 1)
      photoStore.fetchPhotos()
    }
  }
}

// 新增：分享提交
function submitShare() {
  // 检查是否有选中的图片
  if (photoStore.selections.length === 0) {
    message.warning(t('Please select photos first'));
    return;
  }
  
  // 更新ids数组为当前选中的图片
  const requestData = { ...formData.value };
  requestData.ids = photoStore.selections.map((photo: any) => photo.id);
  
  // 处理日期格式
  if (requestData.expired_at) {
    const date = new Date(requestData.expired_at);
    requestData.expired_at = date.toISOString().slice(0, 19).replace('T', ' '); // 格式化为 'yyyy-MM-dd HH:mm:ss'
  }
  
  postUserShares({
    body: requestData,
  }).then(response => {
    if (response.data?.status === 'error') {
      return message.error(response.data?.message)
    }

    shareUrl.value = `${window.location.origin}/shares/${response.data?.data.slug}`
  }).catch(() => {
    message.error(t('Failed to create share'))
  })
}

// 新增：复制分享链接
function copyShareUrl() {
  str.copyText(shareUrl.value).then(() => {
    message.success(t('Copy successful'))
  }).catch(() => {
    message.error(t('Copy failed'))
  })
}

// 新增：监听模态框状态变化
watch(() => photoStore.shareModalActive, (newVal) => {
  if (newVal) {
    // 当模态框打开时，重置状态
    shareUrl.value = '';
    formData.value = {
      type: 'photo',
      ids: [],
      content: '',
      password: '',
      expired_at: null
    };
  }
});

const updateAlbum = debounce(() => {
  putUserAlbumsById({
    path: {
      id: album.value.id,
    },
    body: {
      name: album.value.name,
      intro: album.value.intro,
      is_public: album.value.is_public,
    }
  })
}, 500)

watch(() => album.value, () => {
  if (! isInit.value) return
  updateAlbum()
}, {
  deep: true,
})

function openPhotoSelector() {
  photoSelectorRef.value?.open()
}

function getData() {
  photoStore.resetPhotos({q: 'sort:latest', album_id: album.value.id});
}

function onSelect(selections: UserPhoto[]) {
  postUserAlbumsByIdPhotos({
    path: {
      id: album.value.id,
    },
    body: selections.map((photo: any) => photo.id)
  }).then(() => {
    message.success(t('Added successfully'))
    getData()
  })
}

onMounted(() => {
  photoStore.clearGroups()

  getUserAlbumsById({
    path: {
      id: Number(route.params?.id),
    }
  }).then(result => {
    album.value = result.data?.data
    setTimeout(() => {
      isInit.value = true
      getData()
    }, 50)
  })
})

function deleteAlbum(album: any) {
  deleteRef.value?.open(album, () => {
    router.back()
    message.success(t('Deleted successfully'))
  })
}

function shareAlbum(album: any) {
  shareRef.value?.open(album)
}
</script>

<template>
  <Layout
    :toggle-header="false"
    :show-footer="false"
    :header-title="$t('Album Details')"
    content-class="bg-white dark:bg-[var(--n-color)]"
    :on-scroll="onScroll"
  >
    <PhotoSelector ref="photoSelectorRef" @onSelect="onSelect"/>
    <AlbumDelete ref="deleteRef" />
    <Share ref="shareRef" />

    <Content class="max-w-full">
      <div class="flex items-center justify-between sticky top-0 z-[4] bg-gray-50 border-b border-transparent dark:bg-[var(--n-color)] dark:border-b-gray-800 p-2 w-full">
        <div class="space-x-2">
          <Back />
        </div>
        <div class="space-x-2">
          <NButton tertiary type="error" @click="() => deleteAlbum(album)">
            <template #icon>
              <FontAwesomeIcon icon="fa-solid fa-trash" size="sm" />
            </template>
          </NButton>

          <NButton tertiary type="default" @click="() => shareAlbum(album)">
            <template #icon>
              <FontAwesomeIcon icon="fa-solid fa-share" size="sm" />
            </template>
          </NButton>

          <NButton secondary @click="openPhotoSelector">
            <template #icon>
              <NIcon>
                <FontAwesomeIcon icon="fa-solid fa-plus" size="sm" />
              </NIcon>
            </template>
            {{ $t('Add Photos') }}
          </NButton>
        </div>
      </div>
      <div class="flex flex-col">
        <div class="space-y-2 p-2 border-b dark:border-b-gray-600">
          <NInput size="large" v-model:value="album.name" :placeholder="$t('Add Title')" />
          <NInput resizable type="textarea" v-model:value="album.intro" :placeholder="$t('Add Description')" />
          <div class="flex items-center p-2">
            {{ useDayjs(album.created_at).format('L') }} -
            {{ $t('{count} items', {count: album.photo_count}) }}
          </div>
          <NSwitch v-model:value="album.is_public" size="large">
            <template #checked>
              {{ $t('Public') }}
            </template>
            <template #unchecked>
              {{ $t('Private') }}
            </template>
          </NSwitch>
        </div>

        <div class="space-y-4 p-2">
          <PhotoList />
        </div>

        <PhotoOperates>
          <Cancel/>
          <Select/>
          <CopyLink/>
          <Edit/>
          <Share/>
          <Remove :album-id="Number(route.params?.id)"/>
          <PhotoDelete/>
        </PhotoOperates>
      </div>
    </Content>
    
    <!-- 新增：独立的照片分享模态框 -->
    <NModal
      v-model:show="photoStore.shareModalActive"
      :title="$t('Share Photos')"
      preset="card"
      size="small"
      :bordered="false"
      class="max-w-screen-sm mx-4 md:mx-auto z-50"
    >
      <div class="flex flex-col space-y-4">
        <NForm :model="formData" :rules="formRules" :disabled="Boolean(shareUrl)">
          <NFormItem :label="$t('Share Description')" path="content">
            <NInput resizable type="textarea" v-model:value="formData.content" />
          </NFormItem>
          <NFormItem :label="$t('Password')" path="password">
            <NInput type="password" v-model:value="formData.password" />
          </NFormItem>
          <NFormItem :label="$t('Expiration Time')" path="expired_at">
            <NDatePicker
              v-model:value="formData.expired_at"
              type="datetime"
              class="w-full"
              :placeholder="$t('Select Expiration Time')"
              :is-date-disabled="(timestamp: number) => timestamp < Date.now()"
              clearable
            />
          </NFormItem>
        </NForm>
      </div>

      <template #action>
        <div class="flex justify-end" v-if="!shareUrl">
          <NButton tertiary @click="submitShare">{{ $t('Create Share Link') }}</NButton>
        </div>
        <div class="flex justify-between space-x-2" v-else>
          <NInput class="grow select-all" :default-value="shareUrl" readonly />
          <NButton tertiary type="primary" class="shrink-0" @click="copyShareUrl">
            <template #icon>
              <FontAwesomeIcon icon="fa-solid fa-copy" />
            </template>
          </NButton>
        </div>
      </template>
    </NModal>
  </Layout>
</template>