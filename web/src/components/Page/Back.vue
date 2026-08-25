<script setup lang="ts">
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {NButton} from "naive-ui";
import {type RouteLocationRaw, useRouter} from "vue-router";
import type {PropType} from "vue";

const router = useRouter()

const props = defineProps({
  to: {
    type: String as PropType<RouteLocationRaw>,
    required: false,
  }
})
</script>

<template>
  <NButton tertiary circle type="default" :title="$t('Back')" @click="props.to ? router.push(props.to) : router.back()">
    <template #icon>
      <FontAwesomeIcon icon="fa-solid fa-arrow-left" />
    </template>
  </NButton>
</template>