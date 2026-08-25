<script lang="ts" setup>
import {NLayoutHeader} from 'naive-ui'
import MoreButton from "@/components/NavBarMenus/MoreButton.vue";
import UserButton from "@/components/NavBarMenus/UserButton.vue";
import UploadButton from "@/components/NavBarMenus/UploadButton.vue";
import SidebarButton from "@/components/NavBarMenus/SidebarButton.vue";
import HeaderTitle from "@/components/NavBarMenus/HeaderTitle.vue";
import LoginButton from "@/components/NavBarMenus/LoginButton.vue";
import {useUserStore} from "@/stores/user";
import ExploreButton from "@/components/NavBarMenus/ExploreButton.vue";
import {useConfigStore} from "@/stores/config";

const props = defineProps({
  title: {
    type: String,
    required: false,
  },
  showSidebarButton: {
    type: Boolean,
    default: () => false,
  },
  showUploadButton: {
    type: Boolean,
    default: () => false,
  },
  showUserButton: {
    type: Boolean,
    default: () => false,
  },
  bordered: {
    type: Boolean,
    default: () => false,
  },
})

const userStore = useUserStore()
const configStore = useConfigStore()
</script>

<template>
  <NLayoutHeader
    :bordered="props.bordered"
    class="flex text-md p-4 items-center justify-between transition-all duration-400 border-b dark:border-b-gray-600 w-full h-16 z-[2]"
    position="absolute"
  >
    <div class="flex items-center w-1/2 md:w-[70%]">
      <SidebarButton class="mr-4" v-if="props.showSidebarButton"/>

      <HeaderTitle v-if="props.title" :title="props.title"/>
    </div>

    <div class="flex flex-wrap justify-end shrink-0 space-x-4">
      <ExploreButton v-if="configStore.configs?.app?.enable_explore" />

      <UploadButton v-if="showUploadButton"/>

      <MoreButton/>

      <LoginButton v-if="! userStore.isLoggedIn"/>

      <UserButton v-if="showUserButton && userStore.isLoggedIn"/>
    </div>
  </NLayoutHeader>
</template>

<style scoped>

</style>