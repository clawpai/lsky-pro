<script lang="ts" setup>
import type {SelectOption} from "naive-ui";
import {NButton, NIcon, NPopover, NPopselect} from "naive-ui";
import {
  LanguageOutline as LanguageOutlineIcon,
  ListCircleOutline as ListCircleOutlineIcon,
  MoonOutline as MoonOutlineIcon,
  SunnyOutline as SunnyOutlineIcon,
} from "@vicons/ionicons5";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {useThemeStore} from "@/stores/theme";
import {computed, ref, watch} from "vue";
import {useConfigStore} from "@/stores/config";
import router from "@/router";
import {type GetPagesResponse, postUserSetting} from "@/api";
import {useLocaleStore} from "@/stores/locale";
import {useLayoutStore} from "@/stores/layout";
import {useI18n} from "vue-i18n";

const i18n = useI18n()
const layoutStore = useLayoutStore()
const configStore = useConfigStore()
const themeStore = useThemeStore()
const localeStore = useLocaleStore()
const isDark = computed(() => themeStore.isDark)
const popoverRef = ref()
const language = ref(localeStore.locale)
const languageOptions: SelectOption[] = localeStore.getLocales()
const openPage = (page: GetPagesResponse['data']['data'][number]) => {
  popoverRef.value.setShow(false)
  if (page.type === 'internal') {
    router.push(`/pages/${page.slug}`)
  } else {
    window.open(page.url)
  }
}

watch(language, () => {
  postUserSetting({
    body: {
      language: language.value,
    },
  }).then(_ => {
    localeStore.setLocale(language.value)
    layoutStore.refresh()
  })
})

</script>

<template>
  <NPopover
    ref="popoverRef"
    class="!rounded-lg min-w-[200px] md:w-[280px] max-w-[300px]"
    :content-style="{padding: '6px 8px'}"
    placement="bottom-end"
    show-arrow
    trigger="click"
  >
    <template #trigger>
      <NButton circle secondary strong :title="i18n.t('More')">
        <template #icon>
          <NIcon :component="ListCircleOutlineIcon" :size="25"/>
        </template>
      </NButton>
    </template>

    <div class="flex flex-col space-y-1 divide-y divide-amber-400">
      <NButton
        block
        quaternary
        class="justify-start px-2"
        v-for="page in configStore.pages"
        @click="openPage(page)"
      >
        <template #icon>
          <FontAwesomeIcon :icon="page.icon || ''" size="sm" />
        </template>
        <p class="truncate">{{ page.name }}</p>
      </NButton>
    </div>

    <template #footer>
      <div class="flex justify-between text-sm">
        <div class="flex space-x-1">
          <NButton size="small" text @click="themeStore.toggleTheme()">
            <template #icon>
              <NIcon :component="isDark ? SunnyOutlineIcon : MoonOutlineIcon" :size="18"/>
            </template>
            {{ isDark ? $t('Light Mode') : $t('Dark Mode') }}
          </NButton>
        </div>
        <div class="flex space-x-1">
          <NPopselect
            v-model:value="language"
            :options="languageOptions"
            trigger="click"
          >
            <NButton size="small" text>
              <template #icon>
                <NIcon :component="LanguageOutlineIcon" :size="18"/>
              </template>
              {{ languageOptions.find(item => item.value === language)?.label }}
            </NButton>
          </NPopselect>
        </div>
      </div>
    </template>
  </NPopover>
</template>