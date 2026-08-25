<script lang="ts" setup>
import {NButton, NIcon,} from 'naive-ui'
import {MenuSharp as MenuSharpIcon} from "@vicons/ionicons5";
import {useLayoutStore} from "@/stores/layout";

const layoutStore = useLayoutStore()
</script>

<template>
  <NButton circle quaternary @click="layoutStore.toggleSidebar()">
    <template #icon>
      <NIcon :component="MenuSharpIcon" :size="25"></NIcon>
    </template>
  </NButton>
</template>