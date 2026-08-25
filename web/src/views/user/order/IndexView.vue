<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import {NButton, NIcon, NSpace, NTag, NInput, NCard, useDialog} from "naive-ui";
import {h, ref} from "vue";
import type {TableColumns} from "naive-ui/lib/data-table/src/interface";
import {getUserOrders} from "@/api";
import type {SortState} from "naive-ui/es/data-table/src/interface";
import DataTable from "@/components/DataTable/DataTable.vue";
import {useRouter} from "vue-router";
import {SearchOutline} from "@vicons/ionicons5";
import {useOrderStore} from "@/stores/order";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const router = useRouter()
const orderStore = useOrderStore()
const { t } = useI18n()
const dialog = useDialog()
const tableRef = ref()

const columns: TableColumns = [
  {
    title: t('Order Number'),
    key: "trade_no",
    minWidth: 250,
  },
  {
    title: t('Product'),
    key: "snapshot.name",
    minWidth: 300,
  },
  {
    title: t('Payment Cycle'),
    key: "product.name",
    minWidth: 200,
  },
  {
    title: t('Order Amount'),
    key: "amount",
    minWidth: 100,
    sorter: true,
    align: 'center'
  },
  {
    title: t('Order Status'),
    key: "status",
    minWidth: 100,
    sorter: true,
    align: 'center',
    render(row: any) {
      const statuses = {cancelled: t('Cancelled'), unpaid: t('Unpaid'), paid: t('Paid')}
      const types = {cancelled: 'danger', unpaid: 'warning', paid: 'success'}
      const status = row.status as keyof typeof statuses
      return h(NTag, {
        // @ts-ignore
        type: types[status],
      }, {
        default: () => {
          return statuses[status] ?? t('Unknown')
        },
      })
    }
  },
  {
    title: t('Creation Time'),
    minWidth: 200,
    key: "created_at",
    sorter: true,
    align: 'center',
    render(row: any) {
      return useDayjs(row.created_at).format('L')
    }
  },
  {
    title: t('Actions'),
    key: 'actions',
    minWidth: 170,
    fixed: 'right',
    align: 'center',
    render(row: any) {
      return h(NSpace, {
        vertical: false,
        size: 5,
        justify: 'center',
      }, {
        default: () => [
          h(NButton, {
            size: 'small',
            onClick: () => {
              router.push(`/user/orders/${row.trade_no}`)
            }
          }, {default: () => t('Details')}),
          h(NButton, {
            disabled: row.status !== 'unpaid',
            size: 'small',
            type: 'error',
            onClick: async () => {
              orderStore.cancelOrder(row.trade_no, dialog, t).then(() => {
                selections.value = []
                tableRef.value?.refresh()
              })

            }
          }, {default: () => t('Cancel Order')})
        ]
      })
    }
  }
]

const q = ref('')
const sort = ref('sort:latest')
const selections = ref<Array<string | number>>([])
const getData = (page: number) => {
  return getUserOrders({
    query: {
      page: page,
      per_page: 40,
      q: `${q.value} ${sort.value}`.trim()
    }
  })
}

function handleSorter(sorter: SortState) {
  if (sorter.order) {
    sort.value = `sort:${sorter.columnKey}:${sorter.order}`
  } else {
    sort.value = 'sort:latest'
  }

  tableRef.value?.refresh()
}

function handleSearch() {
  tableRef.value?.refresh()
}
</script>

<template>
  <Layout
    :header-title="$t('Order Management')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NCard>
        <NSpace vertical>
          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
              <!-- -->
            </div>
            <div class="grow md:grow-0 md:basis-1/2">
              <NInput v-model:value="q" :placeholder="$t('Enter keywords to search...')" @keyup.enter.native="handleSearch">
                <template #prefix>
                  <NIcon :component="SearchOutline" />
                </template>
              </NInput>
            </div>
          </div>
          <DataTable
            striped
            ref="tableRef"
            :bordered="false"
            :fetch="getData"
            :columns="columns"
            :row-key="(row: any) => row.id"
            v-model:checked-row-keys="selections"
            @update-sorter="handleSorter"
          />
        </NSpace>
      </NCard>
    </Content>
  </Layout>
</template>