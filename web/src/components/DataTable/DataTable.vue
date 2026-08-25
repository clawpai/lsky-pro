<script setup lang="ts">
import {NDataTable, type PaginationProps} from "naive-ui";
import {onMounted, type PropType, reactive, ref} from "vue";
import type {RowData, TableColumns} from "naive-ui/lib/data-table/src/interface";
import {useI18n} from "vue-i18n";

const { t } = useI18n()

interface PaginationResult {
  current_page: number;
  data: any[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: { url: null | string; label: string; active: boolean }[];
  next_page_url: null | string;
  path: string;
  per_page: number;
  prev_page_url: null | string;
  to: number;
  total: number;
}

interface ResourcePaginationResult {
  data: any[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  },
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    links: { url: null | string; label: string; active: boolean }[];
    path: string;
    per_page: number;
    to: number;
    total: number;
  }
}


const props = defineProps({
  columns: {
    type: Object as PropType<TableColumns<any>>,
    required: true,
  },
  fetch: {
    type: Function as PropType<(page: number) => Promise<any>>,
    required: true,
  },
})

const tableRef = ref()
const loading = ref(false)
const data = ref<RowData[]>([])
const paginationReactive: PaginationProps = reactive({
  pageCount: 1,
  prefix({ itemCount }) {
    return t('Total {count} records', {count: itemCount})
  }
})

function getData(page: number) {
  if (! loading.value) {
    loading.value = true
    props.fetch(page).then((response: any) => {
      const result = response.data.data as (PaginationResult & ResourcePaginationResult)

      data.value = result.data
      const total = (result.total || result.meta.total)
      const per_page = (result.per_page || result.meta.per_page)
      paginationReactive.page = result.current_page || result.meta.current_page
      paginationReactive.pageCount = Math.ceil(total / per_page)
      paginationReactive.itemCount = total
      paginationReactive.pageSize = per_page

    }).finally(() => {
      loading.value = false
    })
  }
}

onMounted(() => {
  getData(1)
})

function refresh() {
  getData(paginationReactive.page || 1)
}

defineExpose({
  refresh,
})
</script>

<template>
  <NDataTable
    class="rounded-lg overflow-hidden"
    remote
    ref="tableRef"
    :data="data"
    :columns="columns"
    :loading="loading"
    :pagination="paginationReactive"
    @update:page="getData"
  />
</template>