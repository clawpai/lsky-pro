<script setup lang="ts">
import {
  NCard,
  NText,
  NSpace,
  NList,
  NListItem,
  NDivider,
  NTag,
  NIcon,
  NPopover,
  NButton,
  NCheckbox,
  NCollapseTransition,
  NCollapse,
  NCollapseItem,
  NBadge,
} from 'naive-ui';
import { defineProps, defineEmits, computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Permission {
  label: string;
  value: string;
  detail?: string;
  category?: string;
}

interface PermissionSubGroup {
  name: string;
  permissions: Permission[];
}

const props = defineProps<{
  title: string;
  description?: string;
  permissions: Permission[];
  selectedPermissions: string[];
  // 新增：权限子分组
  subGroups?: Record<string, PermissionSubGroup>;
}>();

const emit = defineEmits<{
  (e: 'update:selectedPermissions', values: string[]): void;
}>();

const expanded = ref(true);
const showDetails = ref(false);

const allSelected = computed(() => {
  return props.permissions.every(p => props.selectedPermissions.includes(p.value));
});

const someSelected = computed(() => {
  return props.permissions.some(p => props.selectedPermissions.includes(p.value)) && !allSelected.value;
});

// 获取已选择的权限数量
const selectedCount = computed(() => {
  return props.permissions.filter(p => props.selectedPermissions.includes(p.value)).length;
});

// 根据权限值获取权限对象
const getPermissionByValue = (value: string) => {
  return props.permissions.find(p => p.value === value);
};

// 选择单个权限
const togglePermission = (permissionValue: string) => {
  const newSelectedPermissions = [...props.selectedPermissions];
  const index = newSelectedPermissions.indexOf(permissionValue);
  
  if (index === -1) {
    newSelectedPermissions.push(permissionValue);
  } else {
    newSelectedPermissions.splice(index, 1);
  }
  
  emit('update:selectedPermissions', newSelectedPermissions);
};

// 选择所有权限
const selectAll = () => {
  const newSelectedPermissions = [...props.selectedPermissions];
  
  props.permissions.forEach(permission => {
    if (!newSelectedPermissions.includes(permission.value)) {
      newSelectedPermissions.push(permission.value);
    }
  });
  
  emit('update:selectedPermissions', newSelectedPermissions);
};

// 取消选择所有权限
const deselectAll = () => {
  const permissionValues = props.permissions.map(p => p.value);
  const newSelectedPermissions = props.selectedPermissions.filter(
    value => !permissionValues.includes(value)
  );
  
  emit('update:selectedPermissions', newSelectedPermissions);
};

// 切换详细信息显示
const toggleDetails = () => {
  showDetails.value = !showDetails.value;
};

// 检查分组是否全部选中
const isSubGroupAllSelected = (subGroup: string): boolean => {
  if (!props.subGroups || !props.subGroups[subGroup]) return false;
  const subGroupPermissionValues = props.subGroups[subGroup].permissions.map(p => p.value);
  return subGroupPermissionValues.every(value => props.selectedPermissions.includes(value));
};

// 检查分组是否部分选中
const isSubGroupPartiallySelected = (subGroup: string): boolean => {
  if (!props.subGroups || !props.subGroups[subGroup]) return false;
  const subGroupPermissionValues = props.subGroups[subGroup].permissions.map(p => p.value);
  const selected = subGroupPermissionValues.some(value => props.selectedPermissions.includes(value));
  return selected && !isSubGroupAllSelected(subGroup);
};

// 选择子分组所有权限
const selectSubGroup = (subGroup: string) => {
  if (!props.subGroups || !props.subGroups[subGroup]) return;
  
  const newSelectedPermissions = [...props.selectedPermissions];
  const subGroupPermissions = props.subGroups[subGroup].permissions;
  
  subGroupPermissions.forEach(permission => {
    if (!newSelectedPermissions.includes(permission.value)) {
      newSelectedPermissions.push(permission.value);
    }
  });
  
  emit('update:selectedPermissions', newSelectedPermissions);
};

// 取消选择子分组所有权限
const deselectSubGroup = (subGroup: string) => {
  if (!props.subGroups || !props.subGroups[subGroup]) return;
  
  const subGroupPermissionValues = props.subGroups[subGroup].permissions.map(p => p.value);
  const newSelectedPermissions = props.selectedPermissions.filter(
    value => !subGroupPermissionValues.includes(value)
  );
  
  emit('update:selectedPermissions', newSelectedPermissions);
};
</script>

<template>
  <NCard 
    :title="title" 
    class="permission-card mb-4" 
    size="small" 
    :bordered="true"
    :segmented="{ content: true, footer: 'soft' }"
  >
    <template #header-extra>
      <div class="flex gap-2 items-center">
        <NBadge :value="selectedCount" :max="99" :show-zero="false" :offset="[2, 2]">
          <NTag 
            :type="allSelected ? 'success' : someSelected ? 'warning' : 'default'" 
            size="small"
            :bordered="false"
          >
            {{ allSelected ? t('All Selected') : someSelected ? t('Partially Selected') : t('None Selected') }}
          </NTag>
        </NBadge>
        <div class="flex gap-1">
          <NButton size="tiny" @click="selectAll" type="primary" secondary>{{ t('Select All') }}</NButton>
          <NButton size="tiny" @click="deselectAll" secondary>{{ t('Cancel') }}</NButton>
          <NButton size="tiny" @click="toggleDetails" tertiary>
            {{ showDetails ? t('Hide Details') : t('Show Details') }}
          </NButton>
        </div>
      </div>
    </template>

    <div v-if="description" class="text-sm text-gray-500 mb-3">
      {{ description }}
    </div>
    
    <!-- 带子分组的权限列表 -->
    <template v-if="subGroups && Object.keys(subGroups).length > 0">
      <NCollapse class="mb-2">
        <NCollapseItem 
          v-for="(group, groupKey) in subGroups" 
          :key="groupKey" 
          :title="group.name"
        >
          <template #header-extra>
            <div class="flex gap-1 mr-2" @click.stop>
              <NButton 
                size="tiny" 
                @click="selectSubGroup(groupKey)" 
                type="primary" 
                secondary
                :disabled="isSubGroupAllSelected(groupKey)"
              >
                {{ t('Select All') }}
              </NButton>
              <NButton 
                size="tiny" 
                @click="deselectSubGroup(groupKey)" 
                secondary
                :disabled="!isSubGroupPartiallySelected(groupKey) && !isSubGroupAllSelected(groupKey)"
              >
                {{ t('Cancel') }}
              </NButton>
            </div>
          </template>
          
          <NList hoverable>
            <NListItem v-for="permission in group.permissions" :key="permission.value">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 flex-1 cursor-pointer" @click="togglePermission(permission.value)">
                  <NCheckbox :checked="selectedPermissions.includes(permission.value)" />
                  <NText :type="selectedPermissions.includes(permission.value) ? 'primary' : undefined">
                    {{ permission.label }}
                  </NText>
                </div>
                
                <NPopover v-if="permission.detail" trigger="hover" placement="top">
                  <template #trigger>
                    <NIcon class="cursor-help text-gray-400 hover:text-primary">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em">
                        <path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" />
                      </svg>
                    </NIcon>
                  </template>
                  {{ permission.detail }}
                </NPopover>
              </div>
            </NListItem>
          </NList>
        </NCollapseItem>
      </NCollapse>
    </template>
    
    <!-- 标准权限列表 -->
    <template v-else>
      <NList hoverable>
        <NListItem v-for="permission in permissions" :key="permission.value">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 flex-1 cursor-pointer" @click="togglePermission(permission.value)">
              <NCheckbox :checked="selectedPermissions.includes(permission.value)" />
              <NText :type="selectedPermissions.includes(permission.value) ? 'primary' : undefined">
                {{ permission.label }}
              </NText>
            </div>
            
            <NPopover v-if="permission.detail" trigger="hover" placement="top">
              <template #trigger>
                <NIcon class="cursor-help text-gray-400 hover:text-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em">
                    <path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z" />
                  </svg>
                </NIcon>
              </template>
              {{ permission.detail }}
            </NPopover>
          </div>
        </NListItem>
      </NList>
    </template>
    
    <!-- 权限详细信息区域 -->
    <NCollapseTransition>
      <div v-if="showDetails" class="mt-4 px-2">
        <NDivider>{{ t('Permission Details') }}</NDivider>
        <div class="space-y-2">
          <div v-for="permission in permissions" :key="`detail-${permission.value}`" class="p-2 rounded hover:bg-gray-50">
            <div class="font-medium">{{ permission.label }} <NTag size="small">{{ permission.value }}</NTag></div>
            <div class="text-sm text-gray-500 mt-1">{{ permission.detail }}</div>
          </div>
        </div>
      </div>
    </NCollapseTransition>
  </NCard>
</template>

<style scoped>
.permission-card {
  transition: all 0.3s ease;
}

/* 添加移动设备上的样式调整 */
@media (max-width: 640px) {
  .permission-card :deep(.n-card-header) {
    flex-direction: column;
    align-items: flex-start;
  }

  .permission-card :deep(.n-card-header-extra) {
    margin-top: 8px;
    margin-left: 0;
    display: flex;
    flex-direction: column;
    width: 100%;
  }

  .permission-card :deep(.n-card-header-extra .flex.gap-2) {
    width: 100%;
    flex-wrap: wrap;
    margin-top: 4px;
  }

  .permission-card :deep(.n-collapse-item__header-extra) {
    flex-wrap: wrap;
  }
}
</style> 