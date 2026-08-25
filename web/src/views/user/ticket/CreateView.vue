<script setup lang="ts">
import Layout from "@/components/Layout.vue";
import Content from "@/components/Content.vue";
import {type FormInst, NForm, NFormItem, NInput, NSelect, NCard, useMessage, NButton, NSpace} from "naive-ui";
import {ref} from "vue";
import type {FormRules} from "naive-ui/es/form/src/interface";
import type {SelectMixedOption} from "naive-ui/es/select/src/interface";
import {postUserTickets} from "@/api";
import {useRouter} from "vue-router";
import Back from "@/components/Page/Back.vue";
import {useI18n} from "vue-i18n";

const message = useMessage()
const router = useRouter()
const { t } = useI18n()

const loading = ref(false)
const formData = ref<any>({
  level: 'low'
})
const formRef = ref<FormInst | null>(null)
const formRules : FormRules = {
  title: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter the ticket title')
  },
  level: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please select the ticket level')
  },
  content: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please describe the issue you encountered')
  },
}
const levels: SelectMixedOption[] = [
  {
    label: t('Low'),
    value: 'low',
  },
  {
    label: t('Medium'),
    value: 'medium',
  },
  {
    label: t('High'),
    value: 'high',
  }
]

const onSubmit = async (e: Event) => {
  e.preventDefault()
  formRef.value?.validate(async (errors: any) => {
    if (!errors) {
      loading.value = true

      const response = await postUserTickets({body: formData.value}).finally(() => loading.value = false)

      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      loading.value = false

      return router.push(`/user/tickets/${response.data?.data.issue_no}`)
    }
  })
}
</script>

<template>
  <Layout
    :header-title="$t('Create Ticket')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NSpace vertical>
        <div class="space-x-2">
          <Back to="/user/tickets" />
        </div>
        <NCard>
          <NForm
            ref="formRef"
            label-placement="left"
            :label-width="100"
            label-align="left"
            :model="formData"
            :rules="formRules"
            @submit.prevent="onSubmit"
          >
            <NFormItem :label="$t('Ticket Title')" path="title">
              <NInput
                v-model:value="formData.title"
                :maxlength="20"
                :placeholder="$t('Please enter the ticket title')"
              />
            </NFormItem>
            <NFormItem :label="$t('Ticket Level')" path="level">
              <NSelect
                :options="levels"
                v-model:value="formData.level"
                :placeholder="$t('Please select the ticket level')"
              />
            </NFormItem>
            <NFormItem :label="$t('Issue Description')" path="content">
              <NInput
                type="textarea"
                :maxlength="2000"
                v-model:value="formData.content"
                :placeholder="$t('Please enter the issue description')"
              />
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
</template>