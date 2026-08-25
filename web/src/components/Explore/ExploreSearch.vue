<script setup lang="ts">
import {NInput, NIcon, NForm} from "naive-ui";
import {SearchOutline as SearchOutlineIcon} from "@vicons/ionicons5";
import {onMounted} from "vue";
import {useRoute, useRouter} from "vue-router";
import i18n from '@/i18n'
import {useI18n} from "vue-i18n";

const { t } = useI18n()

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Enter keywords and press Enter to search...',
  },
})

const value = defineModel('value', {
  type: String,
  required: true,
})

const route = useRoute()
const router = useRouter()

onMounted(() => {
  value.value = route.query.q as string || ''
})

const submit = () => {
  router.push(`/explore?q=${value.value}`)
}
</script>

<template>
  <div class="flex w-full justify-center my-10">
    <NForm @submit.prevent="submit">
      <NInput
        v-model:value="value"
        round
        size="large"
        :placeholder="$t(props.placeholder)"
        class="min-w-[300px] md:min-w-[600px]"
      >
        <template #suffix>
          <NIcon :component="SearchOutlineIcon" />
        </template>
      </NInput>
    </NForm>
  </div>
</template>