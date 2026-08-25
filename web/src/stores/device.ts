import {defineStore} from "pinia";
import {onMounted, onUnmounted, ref} from "vue";
import device from "@/utils/device";

export const useDeviceStore = defineStore('device', () => {
  const isMobile = ref(device.isMobileDevice())

  // 函数来更新isMobile状态
  const updateIsMobile = () => isMobile.value = device.isMobileDevice()

  onMounted(() => window.addEventListener('resize', updateIsMobile))

  onUnmounted(() => window.removeEventListener('resize', updateIsMobile))

  return {
    isMobile,
  }
})