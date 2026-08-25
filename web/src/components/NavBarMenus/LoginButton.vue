<script lang="ts" setup>
import {NButton, NIcon} from "naive-ui";
import {LoginOutlined as LoginOutlinedIcon} from "@vicons/antd";
import {useRouter} from "vue-router";

const router = useRouter()
</script>

<template>
  <NButton
    tertiary
    type="primary"
    @click="router.push({name: 'Login'})"
  >
    <template #icon>
      <NIcon :component="LoginOutlinedIcon" />
    </template>
    {{ $t('Go to Login') }}
  </NButton>
</template>