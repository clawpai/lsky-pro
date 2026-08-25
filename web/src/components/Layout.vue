<script lang="ts" setup>
import {NLayout, NLayoutContent} from 'naive-ui'
import {useThemeStore} from "@/stores/theme";
import Sidebar from "@/components/Sidebar.vue";
import Header from "@/components/Header.vue";
import {useUserStore} from "@/stores/user";
import {useConfigStore} from "@/stores/config";
import {type CSSProperties, type PropType, provide, ref, watch} from "vue";
import Footer from "@/components/Footer.vue";
import {useLayoutStore} from "@/stores/layout";

const layoutStore = useLayoutStore()
const themeStore = useThemeStore()
const userStore = useUserStore()
const configStore = useConfigStore()

const props = defineProps({
  // 是否显示 header
  showHeader: {
    type: Boolean,
    default: () => true,
  },
  // 是否显示 footer
  showFooter: {
    type: Boolean,
    default: () => true,
  },
  // header title
  headerTitle: {
    type: String,
    default: () => '',
  },
  // 滚动条切换显示 header
  toggleHeader: {
    type: Boolean,
    default: () => true,
  },
  // 滚动条滚动事件
  onScroll: {
    type: Function as PropType<(event: Event) => void>,
    default: () => {}
  },
  contentClass: {
    type: String,
    default: () => ''
  },
  contentStyle: {
    type: Object as PropType<CSSProperties>,
    default: () => ({})
  }
})

const headerClass = ref<string[]>([props.toggleHeader ? '!-top-60' : ''])

// 监听 layoutState.toggleHeader 的变化
watch(
  () => props.toggleHeader,
  (newValue) => {
    if (newValue) {
      headerClass.value = ['!-top-60']
    } else {
      headerClass.value = [] // 清除类名
    }
  }
)

// 处理滚动事件
const handleScroll = (e: Event) => {
  const target = e.target as HTMLElement
  if (props.toggleHeader) {
    headerClass.value = target.scrollTop > 50 ? ['top-0'] : ['!-top-60']
  }

  if (props.onScroll) props.onScroll(e)
}

const scrollContainerRef = ref<HTMLElement | null>(null);

provide('scrollContainerRef', scrollContainerRef);
</script>

<template>
  <NLayout :key="layoutStore.key" :class="{dark: themeStore.isDark}" class="h-full min-h-screen" has-sider>
    <NLayout class="inset-0 h-full" has-sider position="absolute">
      <Sidebar v-if="userStore.isLoggedIn" />
      <NLayout>
        <Header
          :class="headerClass"
          v-if="props.showHeader"
          :title="props.headerTitle"
          :show-upload-button="userStore.isLoggedIn || configStore.configs?.app?.guest_upload"
          :show-sidebar-button="userStore.isLoggedIn"
          :show-user-button="userStore.isLoggedIn"
        />

        <!-- main -->
        <NLayoutContent
          ref="scrollContainerRef"
          :native-scrollbar="true"
          class="w-full h-full"
          position="absolute"
          :on-scroll="handleScroll"
          :class="{'pt-16': props.showHeader && ! props.toggleHeader}"
          :content-class="['h-full w-full bg-gray-100 dark:bg-[var(--n-color)]', props.contentClass].join(' ')"
          :content-style="contentStyle"
        >

          <!-- content -->
          <slot/>

          <slot name="footer" v-if="props.showFooter">
            <Footer/>
          </slot>
        </NLayoutContent>
      </NLayout>
    </NLayout>
  </NLayout>
</template>
