<script lang="ts" setup>
import {
  darkTheme,
  dateZhCN,
  dateEnUS,
  lightTheme,
  NConfigProvider,
  NGlobalStyle,
  NModalProvider,
  NDialogProvider,
  NMessageProvider,
  NNotificationProvider,
  zhCN,
  enUS,
  type GlobalThemeOverrides,
} from 'naive-ui';
import {useThemeStore} from "@/stores/theme";
import {computed, onMounted, watch} from "vue";
import Boot from "@/Boot.vue";
import {useLocaleStore} from "@/stores/locale";

const themeStore = useThemeStore()
const localeStore = useLocaleStore()
const theme = computed(() => themeStore.isDark ? darkTheme : lightTheme)
const themeOverrides : GlobalThemeOverrides = {
  common: {
    fontWeightStrong: '600',
    primaryColor: '#0094c5',
    borderRadius: '8px',
    borderRadiusSmall: '5px',
  },
}

const setBodyTheme = (isDark: boolean) => {
  if (isDark) {
    document.body.classList.add('dark')
  } else {
    document.body.classList.remove('dark')
  }
}

onMounted(() => {
  // 设置 body 上的 dark 类
  // 这么做是因为 native ui 中使用编程式 api 的组件时，dom 在 app 外，会导致 tailwindcss 的 dark 失效
  setBodyTheme(themeStore.isDark)
  watch(() => themeStore.isDark, value => setBodyTheme(value))
})

</script>

<template>
  <NConfigProvider
    :date-locale="localeStore.locale === 'zh-CN' ? dateZhCN : dateEnUS"
    :locale="localeStore.locale === 'zh-CN' ? zhCN : enUS"
    :theme="theme"
    :theme-overrides="themeOverrides"
    abstract
  >
    <NGlobalStyle/>

    <NModalProvider>
      <NNotificationProvider placement="top-right" :max="6">
        <NMessageProvider placement="bottom-right">
          <NDialogProvider>
            <Boot/>
          </NDialogProvider>
        </NMessageProvider>
      </NNotificationProvider>
    </NModalProvider>
  </NConfigProvider>
</template>
