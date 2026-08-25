<script setup lang="ts">
import Content from "@/components/Content.vue";
import Layout from "@/components/Layout.vue";
import {NButton, NIcon, NInput, NSpace, NAlert, NTag, NCard, NPopover, NList, NListItem, useMessage, useDialog} from "naive-ui";
import {h, ref} from "vue";
import type {TableColumns} from "naive-ui/lib/data-table/src/interface";
import {getUserTokens} from "@/api";
import type {SortState} from "naive-ui/es/data-table/src/interface";
import DataTable from "@/components/DataTable/DataTable.vue";
import {PlusOutlined} from "@vicons/antd";
import {useRouter} from "vue-router";
import {SearchOutline} from "@vicons/ionicons5";
import {useDayjs} from "@/hooks/useDayjs";
import {useI18n} from "vue-i18n";
import {useTokenStore} from "@/stores/token";

const router = useRouter()
const { t } = useI18n()
const tableRef = ref()
const tokenStore = useTokenStore()
const message = useMessage()
const dialog = useDialog()

const columns: TableColumns = [
  {
    title: t('Token Name'),
    key: "name",
    minWidth: 200,
  },
  {
    title: t('Permissions'),
    key: "abilities",
    minWidth: 150,
    render(row: any) {
      if (!row.abilities || (Array.isArray(row.abilities) && row.abilities.includes('*'))) {
        return h(NTag, { type: 'warning' }, { default: () => t('Full Access') })
      }
      
      return h(NPopover, {
        trigger: 'hover',
        placement: 'bottom',
        width: 300,
      }, {
        trigger: () => h(NTag, { type: 'info' }, { 
          default: () => h('span', {}, `${row.abilities.length} ${t('Permissions')}`)
        }),
        default: () => h(NList, { 
          hoverable: true, 
          bordered: false,
          style: 'max-height: 400px; overflow-y: auto;'
        }, {
          header: () => h('div', { class: 'font-bold pb-2' }, t('Granted Permissions')),
          default: () => row.abilities.map((ability: string) => 
            h(NListItem, { key: ability }, {
              default: () => h('div', { class: 'flex items-center gap-2' }, [
                h(NIcon, { size: 16, color: '#18a058' }, {
                  default: () => h('svg', {
                    xmlns: "http://www.w3.org/2000/svg",
                    viewBox: "0 0 512 512",
                    fill: "currentColor"
                  }, [
                    h('path', {
                      d: "M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"
                    })
                  ])
                }),
                h('span', {}, getPermissionLabel(ability))
              ])
            })
          )
        })
      })
    }
  },
  {
    title: t('Last Used Time'),
    minWidth: 150,
    key: "last_used_at",
    sorter: true,
    align: 'center',
    render(row: any) {
      return row.last_used_at ? useDayjs(row.last_used_at).format('L') : '-'
    }
  },
  {
    title: t('Expires Time'),
    minWidth: 150,
    key: "expires_at",
    sorter: true,
    align: 'center',
    render(row: any) {
      return row.expires_at ? useDayjs(row.expires_at).format('L') : '-'
    }
  },
  {
    title: t('Creation Time'),
    minWidth: 150,
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
    minWidth: 150,
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
            type: 'error',
            onClick: () => {
              tokenStore.deleteToken(
                row.id, 
                {}, 
                { message, dialog }
              ).finally(() => {
                selections.value = []
                tableRef.value?.refresh()
              })
            }
          }, {default: () => t('Delete Token')})
        ]
      })
    }
  }
]

const q = ref('')
const sort = ref('sort:latest')
const selections = ref<Array<string | number>>([])
const getData = (page: number) => {
  return getUserTokens({
    query: {
      page: page,
      per_page: 40,
      q: `${q.value} ${sort.value}`.trim()
    }
  } as any)
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

// 获取权限标签
const getPermissionLabel = (permissionValue: string) => {
  const permissionMap: Record<string, string> = {
    'basic': '基础权限',
    'user:profile:read': '读取用户资料',
    'user:profile:write': '更新用户资料',
    'user:token:read': '查看个人令牌',
    'user:token:write': '管理个人令牌',
    'user:group:read': '查看用户角色组',
    'user:group:write': '管理用户角色组',
    'user:capacity:read': '查看用户容量',
    'user:capacity:write': '管理用户容量',
    'user:album:read': '查看相册',
    'user:album:write': '管理相册',
    'user:photo:read': '查看照片',
    'user:photo:write': '管理照片',
    'user:share:read': '查看分享',
    'user:share:write': '管理分享',
    'user:ticket:read': '查看工单',
    'user:ticket:write': '管理工单',
    'user:order:read': '查看订单',
    'user:order:write': '管理订单',
    'oauth:read': '查看OAuth信息',
    'oauth:write': '管理OAuth绑定',
    'explore:read': '浏览探索内容',
    'explore:write': '管理探索互动',
    'upload:write': '上传图片',
  };
  
  return permissionMap[permissionValue] || permissionValue;
};
</script>

<template>
  <Layout
    :header-title="$t('My Tokens')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NCard>
        <NSpace vertical>
          <NAlert type="info" show-icon>{{ $t('API tokens are used to authenticate API requests. Create tokens with specific permissions to improve security. Tokens with no permissions will have full access.') }}</NAlert>

          <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
              <NButton tertiary @click="() => {
              router.push('/user/tokens/create')
            }">
                <template #icon>
                  <NIcon :component="PlusOutlined"/>
                </template>
                {{ $t('Create Token') }}
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