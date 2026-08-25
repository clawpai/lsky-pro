<script setup lang="ts">
import {useRouter} from "vue-router";
import {NButton} from "naive-ui";

const router = useRouter()
function goHome() {
  router.push('/')
}
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300 px-4">
    <h1 class="text-6xl font-extrabold text-blue-500 dark:text-blue-400">404</h1>
    <h2 class="text-lg md:text-xl font-medium mt-4">{{ $t('Sorry, page not found') }}</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center max-w-xs">
      {{ $t('The page you are looking for does not exist.') }}<br />
      {{ $t('Please check if the URL is correct or return to the homepage.') }}
    </p>
    <NButton class="mt-2" @click="goHome">{{ $t('Return to Homepage') }}</NButton>
  </div>
</template>