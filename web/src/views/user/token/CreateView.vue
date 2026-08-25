<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import {
  type FormInst,
  NForm,
  NFormItem,
  NInput,
  NDatePicker,
  NCard,
  useMessage,
  NButton,
  NSpace,
  NModal,
  NAlert,
  NCheckboxGroup,
  NCheckbox,
  NDivider,
  NText,
  NTooltip,
  NIcon,
} from "naive-ui";
import {onMounted, ref} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {useRouter} from "vue-router";
import Back from "@/components/Page/Back.vue";
import {useI18n} from "vue-i18n";
import {postUserTokens, getTokenPermissions} from "@/api";
import str from "@/utils/str";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import PermissionCard from "@/components/Token/PermissionCard.vue";

const message = useMessage()
const router = useRouter()
const { t } = useI18n()

const loading = ref(false)
const formData = ref<any>({abilities: []})
const formRef = ref<FormInst | null>(null)
const modalActive = ref(false)
const token = ref('')
const permissionOptions = ref<{ label: string; value: string; detail?: string; category?: string }[]>([])
const categorizedPermissions = ref<Record<string, { label: string; value: string; detail?: string }[]>>({})
const permissionGroups = ref<Record<string, Record<string, string[]>>>({})
const permissionSubGroups = ref<Record<string, Record<string, { name: string; permissions: any[] }>>>({})

const formRules : FormRules = {
  name: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter the token name')
  },
  expires_at: {
    type: 'string',
    required: false,
    trigger: ['blur', 'input'],
    message: t('Please select the token expiration time')
  },
}

// 获取所有权限
const fetchPermissions = async () => {
  try {
    const response = await getTokenPermissions()
    if (response.data?.status === 'success') {
      const permissions = response.data?.data?.permissions || []
      const groups = response.data?.data?.groups || {}
      
      permissionOptions.value = permissions
      permissionGroups.value = groups
      
      // 按前缀对权限进行分类
      const groupedPermissions: Record<string, { label: string; value: string; detail?: string }[]> = {}
      permissions.forEach((permission) => {
        const category = permission.category || permission.value.split(':')[0]
        if (!groupedPermissions[category]) {
          groupedPermissions[category] = []
        }
        groupedPermissions[category].push(permission)
      })
      
      categorizedPermissions.value = groupedPermissions
      
      // 处理权限子分组
      const subGroups: Record<string, Record<string, { name: string; permissions: any[] }>> = {}
      
      // 遍历分组数据
      Object.entries(groups).forEach(([category, categoryGroups]) => {
        subGroups[category] = {}
        
        // 针对每个分类，处理其下的子分组
        Object.entries(categoryGroups as Record<string, string[]>).forEach(([subGroupName, permissionKeys]) => {
          // 查找该子分组下的所有权限对象
          const subGroupPermissions = permissionKeys
            .map(key => permissions.find(p => p.value === key))
            .filter(p => p !== undefined) as any[]
          
          if (subGroupPermissions.length > 0) {
            subGroups[category][subGroupName] = {
              name: subGroupName,
              permissions: subGroupPermissions
            }
          }
        })
      })
      
      permissionSubGroups.value = subGroups
    }
  } catch (error) {
    message.error(t('Failed to get permissions'))
  }
}

// 选择所有权限
const selectAllPermissions = () => {
  formData.value.abilities = permissionOptions.value.map(option => option.value)
}

// 取消选择所有权限
const deselectAllPermissions = () => {
  formData.value.abilities = []
}

// 选择分类下的所有权限
const selectCategoryPermissions = (category: string) => {
  const categoryPermissionValues = categorizedPermissions.value[category].map(p => p.value)
  
  // 移除该分类下的所有权限
  formData.value.abilities = formData.value.abilities.filter(
    (value: string) => !categoryPermissionValues.includes(value)
  )
  
  // 添加该分类下的所有权限
  formData.value.abilities = [...formData.value.abilities, ...categoryPermissionValues]
}

// 取消选择分类下的所有权限
const deselectCategoryPermissions = (category: string) => {
  const categoryPermissionValues = categorizedPermissions.value[category].map(p => p.value)
  formData.value.abilities = formData.value.abilities.filter(
    (value: string) => !categoryPermissionValues.includes(value)
  )
}

// 检查分类是否全部选中
const isCategoryAllSelected = (category: string): boolean => {
  const categoryPermissionValues = categorizedPermissions.value[category].map(p => p.value)
  return categoryPermissionValues.every(value => formData.value.abilities.includes(value))
}

// 检查分类是否部分选中
const isCategoryPartiallySelected = (category: string): boolean => {
  const categoryPermissionValues = categorizedPermissions.value[category].map(p => p.value)
  const selected = categoryPermissionValues.some(value => formData.value.abilities.includes(value))
  return selected && !isCategoryAllSelected(category)
}

// 检查一个分类是否有子分组
const hasCategorySubGroups = (category: string): boolean => {
  return permissionSubGroups.value && 
    permissionSubGroups.value[category] && 
    Object.keys(permissionSubGroups.value[category]).length > 0;
}

// 获取一个分类的子分组
const getCategorySubGroups = (category: string) => {
  return permissionSubGroups.value?.[category] || {}
}

// 获取分组的中文名称
const getGroupDisplayName = (group: string): string => {
  // 对分组名称进行翻译或映射
  const groupNames: Record<string, string> = {
    'user': '用户管理',
    'content': '内容管理',
    'service': '服务管理',
    'integration': '集成功能',
    'basic': '基础功能',
    'oauth': 'OAuth认证',
    'explore': '探索功能',
    'upload': '上传功能',
  }
  return groupNames[group] || group
}

// 获取权限组的描述
const getGroupDescription = (group: string): string => {
  const descriptions: Record<string, string> = {
    'user': '用户资料、令牌、角色组和容量等用户相关权限',
    'content': '相册、照片、分享和图片上传等内容管理权限',
    'service': '工单和订单等服务相关权限',
    'integration': 'OAuth绑定和内容探索等集成功能权限',
    'basic': '基础功能权限，所有令牌默认拥有',
    'oauth': 'OAuth第三方账号绑定相关权限',
    'explore': '内容探索和浏览相关权限',
    'upload': '图片上传相关权限',
  }
  return descriptions[group] || ''
}

const onSubmit = async (e: Event) => {
  e.preventDefault()
  formRef.value?.validate(async (errors: any) => {
    if (!errors) {
      loading.value = true
      
      try {
        // 确保abilities是纯数组
        const abilitiesArray = Array.isArray(formData.value.abilities) && formData.value.abilities.length > 0 
          ? [...formData.value.abilities] 
          : ['*']
        
        // 构造请求数据
        const requestData: {
          name: string;
          abilities: string[];
          expires_at?: string;
        } = {
          name: formData.value.name,
          abilities: abilitiesArray
        }
        
        if (formData.value.expires_at) {
          requestData.expires_at = formData.value.expires_at
        }
        
        // 构造FormData对象
        const formDataObj = new FormData()
        formDataObj.append('name', requestData.name)
        
        if (requestData.expires_at) {
          formDataObj.append('expires_at', requestData.expires_at)
        }
        
        // 正确处理数组字段
        for (const ability of requestData.abilities) {
          formDataObj.append('abilities[]', ability)
        }
        
        // 使用项目标准API调用
        const response = await postUserTokens({
          body: formDataObj as any // 使用类型断言解决类型不匹配问题
        })
  
        if (response.data?.status === 'error') {
          return message.error(response.data?.message)
        }
  
        token.value = response.data?.data.token || ''
        modalActive.value = true
      } catch (error: any) {
        console.error('创建令牌错误:', error.response?.data || error)
        message.error(error.response?.data?.message || t('Failed to create token'))
      } finally {
        loading.value = false
      }
    }
  })
}

const close = () => {
  modalActive.value = false
  router.push(`/user/tokens`)
}

// 修改复制功能，复制成功后也会导航回列表页面
const copyAndNavigate = () => {
  str.copyText(token.value).then(() => {
    message.success(t('Copy successful'))
    // 复制成功后延迟导航，让用户看到成功消息
    setTimeout(() => {
      router.push('/user/tokens')
    }, 500)
  }).catch(() => {
    message.error(t('Copy failed'))
  })
}

onMounted(() => {
  fetchPermissions()
})
</script>

<template>
  <Layout
    :header-title="$t('Create Token')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10 overflow-hidden">
      <NSpace vertical>
        <div class="space-x-2">
          <Back to="/user/tokens" />
        </div>
        <NCard>
          <NForm
            ref="formRef"
            label-placement="left"
            :label-width="200"
            label-align="left"
            :model="formData"
            :rules="formRules"
            @submit.prevent="onSubmit"
            class="form-container"
          >
            <NFormItem :label="$t('Token Name')" path="name">
              <NInput
                v-model:value="formData.name"
                :maxlength="20"
                :placeholder="$t('Please enter the token name')"
              />
            </NFormItem>

            <NFormItem :label="$t('Token Expires Time')" path="expires_at">
              <NDatePicker
                v-model:formatted-value="formData.expires_at"
                type="datetime"
                class="w-full"
                :is-date-disabled="(timestamp: number) => timestamp < Date.now()"
                value-format="yyyy-MM-dd HH:mm:ss"
                clearable
                :placeholder="$t('Please select the token expiration time')"
              />
            </NFormItem>
            
            <NFormItem :label="$t('Token Permissions')" path="abilities">
              <div class="flex flex-col gap-4">
                <NAlert type="info" show-icon>
                  {{ $t('The token created without choosing any permissions will have full access rights. Please select the permissions carefully based on your needs.') }}
                </NAlert>
                
                <div class="flex justify-between items-center mb-2 flex-wrap gap-2">
                  <NText>{{ $t('Permissions') }}</NText>
                  <div class="flex gap-2">
                    <NButton size="small" @click="selectAllPermissions">{{ $t('Select All') }}</NButton>
                    <NButton size="small" @click="deselectAllPermissions">{{ $t('Deselect All') }}</NButton>
                  </div>
                </div>
                
                <NSpace vertical>
                  <!-- 使用权限卡片组件展示权限 -->
                  <div v-for="(permissions, category) in categorizedPermissions" :key="category">
                    <PermissionCard 
                      :title="getGroupDisplayName(category)" 
                      :description="getGroupDescription(category)"
                      :permissions="permissions"
                      :selected-permissions="formData.abilities"
                      :sub-groups="hasCategorySubGroups(category) ? getCategorySubGroups(category) : undefined"
                      @update:selected-permissions="newValues => formData.abilities = newValues"
                    />
                  </div>

                  <!-- 显示说明 -->
                  <NAlert type="info" class="mt-4">
                    {{ $t('Permission Principle') }}
                  </NAlert>
                </NSpace>
              </div>
            </NFormItem>

            <NFormItem class="flex justify-end">
              <NButton
                attr-type="submit"
                type="primary"
                :loading="loading"
              >{{ $t('Confirm Creation') }}</NButton>
            </NFormItem>
          </NForm>
        </NCard>
      </NSpace>
    </Content>
  </Layout>

  <NModal
    v-model:show="modalActive"
    preset="card"
    :title="$t('Created successfully')"
    size="small"
    :bordered="false"
    class="max-w-screen-sm mx-4 md:mx-auto max-h-[90vh] overflow-auto"
  >
    <div class="flex flex-col space-y-4">
      <NAlert type="warning" show-icon>{{ $t('Please keep it safe. This Token will no longer be displayed after the window is closed.') }}</NAlert>
      <NInput class="select-all" :value="token" readonly />
    </div>

    <template #action>
      <div class="flex justify-between space-x-2">
        <NButton tertiary @click="close">{{ $t('Close') }}</NButton>

        <NButton tertiary type="primary" class="shrink-0" @click="copyAndNavigate">
          <template #icon>
            <FontAwesomeIcon icon="fa-solid fa-copy" />
          </template>
        </NButton>
      </div>
    </template>
  </NModal>
</template>

<style scoped>
/* 响应式表单样式 */
@media (max-width: 640px) {
  :deep(.form-container .n-form-item-label) {
    width: 100% !important;
    margin-bottom: 8px;
    text-align: left !important;
  }
  
  :deep(.form-container .n-form-item) {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  
  :deep(.form-container .n-form-item-blank) {
    width: 100%;
  }
}
</style>