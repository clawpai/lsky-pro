<script lang="ts" setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <div class="truncate text-lg">
    {{ props.title }}
  </div>
</template>