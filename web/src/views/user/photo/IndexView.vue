<script setup lang="ts">
import Content from "@/components/Content.vue";
import {usePhotoStore} from "@/stores/photo";
import Layout from "@/components/Layout.vue";
import {onMounted, onBeforeUnmount, ref, watch} from "vue";
import {NInput, NSelect, NModal, NForm, NFormItem, NDatePicker, NButton, useMessage} from "naive-ui"
import { debounce } from 'lodash'
import number from "@/utils/number";
import PhotoList from "@/components/Photo/PhotoList.vue";
import PhotoOperates from "@/components/PhotoOperates/PhotoOperates.vue";
import CopyLink from "@/components/PhotoOperates/PhotoCopyLink.vue";
import ToAlbum from "@/components/PhotoOperates/PhotoToAlbum.vue";
import Cancel from "@/components/PhotoOperates/PhotoCancel.vue";
import Share from "@/components/PhotoOperates/PhotoShare.vue";
import Delete from "@/components/PhotoOperates/PhotoDelete.vue";
import Edit from "@/components/PhotoOperates/PhotoEdit.vue";
import Select from "@/components/PhotoOperates/PhotoSelect.vue";
import {useConfigStore} from "@/stores/config";
import type {SelectMixedOption} from "naive-ui/es/select/src/interface";
import {useI18n} from "vue-i18n";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import str from "@/utils/str";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {postUserShares} from "@/api";

const configStore = useConfigStore()
const photoStore = usePhotoStore()
const { t } = useI18n()
const message = useMessage()

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

// 处理全局键盘事件
function handleKeyDown(event: KeyboardEvent) {
  // Ctrl+A 或 Command+A 全选功能 (Windows/Linux 使用 ctrlKey, macOS 使用 metaKey)
  if ((event.ctrlKey || event.metaKey) && event.key === 'a') {
    event.preventDefault(); // 阻止默认的全选行为
    photoStore.selectAll();
  }
}

const orderByOptions: SelectMixedOption[] = [
  {label: t('Newest'), value: 'latest'},
  {label: t('Oldest'), value: 'oldest'},
]

const filters = ref({
  q: '',
  storage_id: null,
  order_by: 'latest',
})

function getData() {
  photoStore.resetPhotos({
    storage_id: filters.value.storage_id,
    q: `sort:${filters.value.order_by} ${filters.value.q}`,
  })
}

// 分享提交
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

// 复制分享链接
function copyShareUrl() {
  str.copyText(shareUrl.value).then(() => {
    message.success(t('Copy successful'))
  }).catch(() => {
    message.error(t('Copy failed'))
  })
}

// 监听模态框状态变化
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

watch(filters, debounce(() => getData(), 500), {
  deep: true,
})

onMounted(() => {
  getData();
  
  // 添加全局键盘事件监听
  window.addEventListener('keydown', handleKeyDown);
})

onBeforeUnmount(() => {
  // 移除全局键盘事件监听，防止内存泄漏
  window.removeEventListener('keydown', handleKeyDown);
})
</script>

<template>
  <Layout
    :toggle-header="false"
    :show-footer="false"
    :header-title="`${photoStore.selections.length > 0 ? $t('{count} items selected', {count: number.format(photoStore.selections.length)}) : $t('Photo Management')}`"
    :on-scroll="onScroll"
  >
    <Content class="max-w-full bg-white dark:bg-[var(--n-color)]">
      <div class="flex items-center justify-between space-x-2 sticky top-0 z-[4] bg-gray-100 border-b border-transparent dark:bg-[var(--n-color)] dark:border-b-gray-800 p-2 w-full">
        <div></div>
        <div class="flex justify-end space-x-2 grow md:grow-0 md:w-2/3">
          <NInput v-model:value="filters.q" class="max-w-44" :placeholder="$t('Enter keywords to search...')" clearable />
          <NSelect
            class="max-w-52"
            filterable
            clearable
            :placeholder="$t('Storage Filter')"
            v-model:value="filters.storage_id"
            :options="(configStore.group?.storages || [])"
            label-field="name"
            value-field="id"
          />
          <NSelect
            class="w-32 shrink-0"
            :placeholder="$t('Sort')"
            :options="orderByOptions"
            v-model:value="filters.order_by"
          />
        </div>
      </div>
      <div class="flex flex-col">
        <div class="space-y-4 p-2">
          <PhotoList/>
        </div>

        <PhotoOperates>
          <Cancel/>
          <Select/>
          <CopyLink/>
          <Edit/>
          <Share/>
          <ToAlbum/>
          <Delete/>
        </PhotoOperates>
      </div>
    </Content>
    
    <!-- 独立的照片分享模态框 -->
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
