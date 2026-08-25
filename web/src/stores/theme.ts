import {computed, ref} from 'vue'
import {defineStore} from 'pinia'

export const useThemeStore = defineStore('theme', () => {
  const theme = ref(localStorage.getItem('theme') || 'light')

  const isDark = computed(() => theme.value === 'dark')

  function toggleTheme() {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
    localStorage.setItem('theme', theme.value)
  }

  function setTheme(name: string) {
    theme.value = name
    localStorage.setItem('theme', theme.value)
  }

  return {
    theme,
    isDark,
    toggleTheme,
    setTheme,
  }
})