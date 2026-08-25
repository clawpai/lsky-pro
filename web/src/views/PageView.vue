<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import {useRoute, useRouter} from "vue-router";
import {onMounted, ref, watch} from "vue";
import {NSpin, useMessage} from "naive-ui";
import {getPagesBySlug, type GetPagesBySlugResponse} from "@/api";
import {useI18n} from "vue-i18n";

const route = useRoute()
const router = useRouter()
const message = useMessage()
const { t } = useI18n()
const loading = ref(true)
const page = ref<GetPagesBySlugResponse['data']>()

const fetchPageData = async (slug: string) => {
  loading.value = true
  try {
    const result = await getPagesBySlug({path: {slug}})
    page.value = result.data?.data
    window.document.title = page.value?.title + " - " + window.document.title
  } catch (error) {
    router.push('/404').then(() => {
      message.error(t('Page Not Found'))
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPageData(route.params?.slug as string || '')
})

// 监听路由参数变化，重新获取页面数据
watch(() => route.params?.slug, (newSlug) => {
  if (newSlug) {
    fetchPageData(newSlug as string)
  }
}, { immediate: false })
</script>

<template>
  <Layout
    :header-title="page?.title || ''"
    :toggle-header="false"
    show-footer
  >
    <div v-if="loading" class="w-full h-full flex items-center justify-center">
      <NSpin size="small" description="loading..."/>
    </div>
    <div v-else class="min-h-full flex py-10">
      <div class="container mx-auto px-4 max-w-screen-lg md:px-20 overflow-hidden">
        <article 
          class="prose dark:prose-invert max-w-none overflow-x-auto prose-img:mx-auto prose-headings:font-bold prose-a:text-blue-600 dark:prose-a:text-blue-400" 
          v-html="page?.content"
        ></article>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.prose :deep(img) {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 1rem auto;
}

.prose :deep(a) {
  text-decoration: underline;
}

.prose :deep(table) {
  border-collapse: collapse;
  width: 100%;
}

.prose :deep(table td), 
.prose :deep(table th) {
  border: 1px solid #ddd;
  padding: 8px;
}

.prose :deep(pre) {
  background-color: #f0f0f0;
  border-radius: 4px;
  padding: 1rem;
  overflow-x: auto;
}

.prose :deep(code) {
  background-color: rgba(175, 184, 193, 0.2);
  padding: 0.2em 0.4em;
  border-radius: 3px;
}

.dark .prose :deep(pre) {
  background-color: #282c34;
}

.dark .prose :deep(code) {
  background-color: rgba(110, 118, 129, 0.4);
}
</style>