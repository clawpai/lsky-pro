<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import DataTable from "@/components/DataTable/DataTable.vue";
import {deleteUserShares, getUserShares, putUserSharesById} from "@/api";
import type {TableColumns} from "naive-ui/lib/data-table/src/interface";
import {NButton, NIcon, NInput, NSpace, NTag, NCard, useDialog, useMessage} from "naive-ui";
import {h, ref} from "vue";
import str from "@/utils/str";
import Formatters from "@/components/DataTable/Formatters";
import type {SortState} from "naive-ui/es/data-table/src/interface";
import number from "@/utils/number";
import {DeleteOutlined} from "@vicons/antd";
import type {DialogOptions} from "naive-ui/es/dialog/src/DialogProvider";
import {SearchOutline} from "@vicons/ionicons5";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";

const message = useMessage()
const dialog = useDialog()
const { t } = useI18n()
const tableRef = ref()

const columns: TableColumns = [
  {
    type: 'selection',
  },
  {
    title: t('Type'),
    key: "type",
    width: 100,
    align: 'center',
    render(row: any) {
      return h(NTag, null, {default: () => row.type === 'photo' ? t('Photo') : t('Album')})
    }
  },
  {
    title: t('Password'),
    key: "password",
    minWidth: 180,
    render(row: any) {
      if (!row.password) {
        return t('None')
      }

      return Formatters.password(row.password, {
        size: 'small',
        onChange(value) {
          putUserSharesById({
            path: {
              id: row.id,
            },
            body: {
              password: value
            }
          }).then(() => {
            message.success(t('Successfully updated'))
          }).catch(() => {
            message.error(t('Modification failed'))
          })
        },
      })
    }
  },
  {
    title: t('Views'),
    key: "view_count",
    minWidth: 100,
    align: 'center',
    sorter: true,
    render(row: any) {
      return number.format(row.view_count)
    }
  },
  {
    title: t('Expiration Time'),
    minWidth: 200,
    key: "expired_at",
    sorter: true,
    align: 'center',
    render(row: any) {
      if (!row.expired_at) {
        return t('None')
      }

      return useDayjs(row.expired_at).format('LLL')
    }
  },
  {
    title: t('Creation Time'),
    minWidth: 200,
    key: "created_at",
    sorter: true,
    align: 'center',
    render(row: any) {
      return useDayjs(row.created_at).format('LLL')
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
              str.copyText(`${window.location.origin}/shares/${row.slug}`).then(() => {
                message.success(t('Copy successful'))
              }).catch(() => {
                message.error(t('Copy failed'))
              })
            }
          }, {default: () => t('Copy Link')}),
          h(NButton, {
            size: 'small',
            type: 'error',
            onClick: () => {
              handleDelete([row.id], {
                title: t('Delete Share'),
                content: t('Are you sure you want to delete this share?'),
              })
            }
          }, {default: () => t('Delete')})
        ]
      })
    }
  }
]

const q = ref('')
const sort = ref('sort:latest')
const selections = ref<Array<string | number>>([])
const getData = (page: number) => {
  return getUserShares({
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

function handleDelete(ids: number[], options: DialogOptions) {
  const d = dialog.warning({
    ...{
      positiveText: t('Confirm'),
      negativeText: t('Cancel'),
      onPositiveClick: () => {
        d.loading = true
        return new Promise((resolve) => {
          deleteUserShares({
            body: ids,
          }).then(() => {
            message.success(t('Deleted successfully'))
            selections.value = []
            tableRef.value?.refresh()
            resolve(true)
          }).finally(() => {
            d.loading = false
          })
        })
      },
    },
    ...options
  })
}

function handleSearch() {
  tableRef.value?.refresh()
}
</script>

<template>
  <Layout
    :header-title="$t('My Shares')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NCard>
        <NSpace vertical>
          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
              <NButton
                type="error"
                size="small"
                :disabled="selections.length <= 0"
                @click="() => {
                handleDelete(selections.map((item: any) => item), {
                  title: t('Delete Share'),
                  content: t('Are you sure you want to delete the selected {count} shares?', {count: selections.length}),
                })
              }"
              >
                <template #icon>
                  <NIcon :component="DeleteOutlined"/>
                </template>
                {{ $t('Delete') }}
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