<script setup lang="ts">
import {NButton, NDynamicTags, NForm, NFormItem, NInput, NModal, NSwitch, useMessage} from 'naive-ui';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {usePhotoStore} from "@/stores/photo";
import {computed, onMounted, ref, watch} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {putUserPhotosUpdate} from "@/api";
import {useI18n} from "vue-i18n";

const photoStore = usePhotoStore()
const selections = computed(() => photoStore.selections)
const isBatchEdit = computed(() => selections.value.length > 1)
const photo = computed<any>(() => photoStore.selections[0] || {tags: []})
const formRef = ref()
const formData = ref<any>({})
const formRules: FormRules = {}
const message = useMessage()
const { t } = useI18n()

// 控制哪些字段需要更新
const fieldsToUpdate = ref({
  name: !isBatchEdit.value,
  intro: !isBatchEdit.value,
  is_public: !isBatchEdit.value,
  tags: !isBatchEdit.value
})

onMounted(() => {
  initFormData()
})

watch(() => photoStore.editModalActive, (val) => {
  if (val) {
    initFormData()
  }
})

function initFormData() {
  const { name, intro, is_public } = photo.value
  const tags = photo.value.tags?.map((item: any) => item.name) || []
  formData.value = { name, intro, is_public, tags }
  
  // 当单个图片编辑时，默认更新所有字段
  fieldsToUpdate.value = {
    name: !isBatchEdit.value,
    intro: !isBatchEdit.value,
    is_public: !isBatchEdit.value,
    tags: !isBatchEdit.value
  }
}

async function submit() {
  // 构建要提交的数据，只包含需要更新的字段
  const submitData: any = {
    ids: selections.value.map((photo: any) => photo.id)
  }
  
  if (fieldsToUpdate.value.name) {
    submitData.name = formData.value.name
  }
  
  if (fieldsToUpdate.value.intro) {
    submitData.intro = formData.value.intro
  }
  
  if (fieldsToUpdate.value.is_public) {
    submitData.is_public = formData.value.is_public
  }
  
  if (fieldsToUpdate.value.tags) {
    submitData.tags = formData.value.tags
  }
  
  await putUserPhotosUpdate({
    body: submitData,
  }).then(() => {
    message.success(t('Successfully updated'))
    photoStore.setEditModalActive(false)
    
    // 更新本地数据
    selections.value.forEach((selectedPhoto: any) => {
      if (fieldsToUpdate.value.name) {
        selectedPhoto.name = formData.value.name
      }
      
      if (fieldsToUpdate.value.intro) {
        selectedPhoto.intro = formData.value.intro
      }
      
      if (fieldsToUpdate.value.is_public) {
        selectedPhoto.is_public = formData.value.is_public
      }
      
      if (fieldsToUpdate.value.tags) {
        selectedPhoto.tags = formData.value.tags.map((item: any) => {
          return { name: item }
        })
      }
    })
  }).catch((error) => {
    console.error('更新失败', error)
    message.error(t('Update failed'))
  })
}
</script>

<template>
  <NModal
    v-model:show="photoStore.editModalActive"
    :title="isBatchEdit ? t('Batch Edit Information') : t('Edit Information')"
    preset="card"
    size="small"
    :bordered="false"
    class="max-w-screen-sm mx-4 md:mx-auto"
  >
    <div class="flex flex-col space-y-4">
      <NForm ref="formRef" :model="formData" :rules="formRules">
        <div v-if="isBatchEdit" class="text-sm dark:text-gray-300 mb-2 text-center p-2 bg-gray-50 dark:bg-gray-800 rounded">
          {{ t('Selected {count} photos for batch editing', { count: selections.length }) }}
        </div>
        
        <div v-if="isBatchEdit" class="mb-4 bg-gray-50 dark:bg-gray-800 p-3 rounded">
          <div class="text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">{{ t('Select fields to update') }}:</div>
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
              <NSwitch v-model:value="fieldsToUpdate.name" size="small" />
              <span class="text-sm">{{ t('Custom Name') }}</span>
            </div>
            <div class="flex items-center gap-2">
              <NSwitch v-model:value="fieldsToUpdate.intro" size="small" />
              <span class="text-sm">{{ t('Description') }}</span>
            </div>
            <div class="flex items-center gap-2">
              <NSwitch v-model:value="fieldsToUpdate.tags" size="small" />
              <span class="text-sm">{{ t('Tags') }}</span>
            </div>
            <div class="flex items-center gap-2">
              <NSwitch v-model:value="fieldsToUpdate.is_public" size="small" />
              <span class="text-sm">{{ t('Is Public') }}</span>
            </div>
          </div>
        </div>
        
        <NFormItem :label="t('Custom Name')" path="name">
          <NInput 
            v-model:value="formData.name" 
            :disabled="isBatchEdit && !fieldsToUpdate.name"
            :placeholder="isBatchEdit && !fieldsToUpdate.name ? t('Field not selected') : ''" 
          />
        </NFormItem>
        
        <NFormItem :label="t('Description')" path="intro">
          <NInput 
            resizable 
            type="textarea" 
            v-model:value="formData.intro" 
            :disabled="isBatchEdit && !fieldsToUpdate.intro"
            :placeholder="isBatchEdit && !fieldsToUpdate.intro ? t('Field not selected') : ''"
          />
        </NFormItem>
        
        <NFormItem :label="t('Tags')" path="tags">
          <NDynamicTags 
            v-model:value="formData.tags" 
            :disabled="isBatchEdit && !fieldsToUpdate.tags"
            :placeholder="isBatchEdit && !fieldsToUpdate.tags ? t('Field not selected') : ''"
          />
        </NFormItem>
        
        <NFormItem :label="t('Is Public')" path="is_public">
          <NSwitch 
            v-model:value="formData.is_public" 
            :disabled="isBatchEdit && !fieldsToUpdate.is_public" 
          />
        </NFormItem>
      </NForm>
    </div>

    <template #action>
      <div class="flex justify-end space-x-2">
        <NButton tertiary type="default" @click="photoStore.setEditModalActive(false)">{{ t('Cancel') }}</NButton>
        <NButton tertiary type="primary" @click="submit">{{ t('Save') }}</NButton>
      </div>
    </template>
  </NModal>

  <!-- 修改信息 -->
  <NButton
    tertiary
    circle
    size="small"
    type="default"
    :title="selections.length > 1 ? t('Batch Edit Information') : t('Edit Information')"
    @click="photoStore.setEditModalActive(true)"
  >
    <template #icon>
      <FontAwesomeIcon icon="fa-solid fa-edit" />
    </template>
  </NButton>
</template>