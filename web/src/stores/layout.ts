import {defineStore} from "pinia";
import {ref} from "vue";

export const useLayoutStore = defineStore('layout', () => {
  const key = ref(0)
  const isSidebarOpen = ref(localStorage.getItem('isSidebarOpen') || false)

  function setSidebarOpen(value: boolean) {
    isSidebarOpen.value = value
    if (value) {
      localStorage.setItem('isSidebarOpen', value.toString())
    } else {
      localStorage.removeItem('isSidebarOpen')
    }
  }

  function toggleSidebar() {
    setSidebarOpen(!isSidebarOpen.value)
  }

  function refresh() {
    key.value++
  }

  return {
    key,
    isSidebarOpen,
    toggleSidebar,
    setSidebarOpen,
    refresh,
  }
})