<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import {NButton, NIcon, NInput, NSpace, NTag, NCard, useDialog, useMessage} from "naive-ui";
import {h, ref} from "vue";
import type {TableColumns} from "naive-ui/lib/data-table/src/interface";
import {getUserTickets} from "@/api";
import type {SortState} from "naive-ui/es/data-table/src/interface";
import DataTable from "@/components/DataTable/DataTable.vue";
import {PlusOutlined} from "@vicons/antd";
import {useRouter} from "vue-router";
import {SearchOutline} from "@vicons/ionicons5";
import {useTicketStore} from "@/stores/ticket";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const ticketStore = useTicketStore()
const router = useRouter()
const { t } = useI18n()
const message = useMessage()
const dialog = useDialog()
const tableRef = ref()

const columns: TableColumns = [
  {
    title: t('Ticket Number'),
    key: "issue_no",
    minWidth: 250,
  },
  {
    title: t('Ticket Title'),
    key: "title",
    minWidth: 300,
  },
  {
    title: t('Ticket Level'),
    key: "level",
    minWidth: 120,
    sorter: true,
    align: 'center',
    render(row: any) {
      const levels = {low: t('Low'), medium: t('Medium'), high: t('High')}
      const types = {low: 'info', medium: 'warning', high: 'danger'}
      const level = row.level as keyof typeof levels
      return h(NTag, {
        // @ts-ignore
        type: types[level],
      }, {
        default: () => {
          return levels[level] || t('Unknown')
        },
      })
    }
  },
  {
    title: t('Ticket Status'),
    key: "status",
    minWidth: 100,
    sorter: true,
    align: 'center',
    render(row: any) {
      const statuses = {in_progress: t('Processing'), completed: t('Completed')}
      const types = {in_progress: 'warning', completed: 'success'}
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
              router.push(`/user/tickets/${row.issue_no}`)
            }
          }, {default: () => t('Details')}),
          h(NButton, {
            disabled: row.status === 'completed',
            size: 'small',
            type: 'error',
            onClick: () => {
              ticketStore.closeTicket(row.issue_no, message, dialog, t).finally(() => {
                selections.value = []
                tableRef.value?.refresh()
              })
            }
          }, {default: () => t('Close Ticket')})
        ]
      })
    }
  }
]

const q = ref('')
const sort = ref('sort:latest')
const selections = ref<Array<string | number>>([])
const getData = (page: number) => {
  return getUserTickets({
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
    :header-title="$t('My Tickets')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NCard>
        <NSpace vertical>
          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
              <NButton tertiary @click="() => {
              router.push('/user/tickets/create')
            }">
                <template #icon>
                  <NIcon :component="PlusOutlined"/>
                </template>
                {{ $t('Create Ticket') }}
              </NButton>
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