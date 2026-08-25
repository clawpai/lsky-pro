import {ref} from 'vue'
import {defineStore} from 'pinia'
import {useI18n} from "vue-i18n";

export const useLocaleStore = defineStore('language', () => {
  const language = ref(localStorage.getItem('locale') || 'zh-CN')

  const { locale } = useI18n();

  function getLocales() {
    return [
      {
        label: '简体中文',
        value: 'zh-CN',
      },
      {
        label: 'English',
        value: 'en-US',
      }
    ]
  }

  function setLocale(name: string) {
    language.value = name
    locale.value = name
    localStorage.setItem('locale', language.value)
  }

  return {
    locale: language,
    setLocale,
    getLocales,
  }
})