<script lang="ts" setup>
import {NButton, NIcon} from "naive-ui";
import {CompassOutline as CompassOutlineIcon} from "@vicons/ionicons5";
import {useRouter} from "vue-router";
import {useI18n} from "vue-i18n";

const router = useRouter()
const { t } = useI18n()
</script>

<template>
  <NButton
    circle secondary strong
    :title="t('Square')"
    @click="router.push({name: 'explore'})"
  >
    <template #icon>
      <NIcon :component="CompassOutlineIcon" size="25" />
    </template>
  </NButton>
</template>