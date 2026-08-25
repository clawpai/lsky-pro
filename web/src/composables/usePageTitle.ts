import { ref } from 'vue'
import { useConfigStore } from '@/stores/config'
import i18n from '@/i18n'

const pageTitle = ref<string>('')

export function usePageTitle() {
  const configStore = useConfigStore()

  const setTitle = (title: string, translateKey?: string) => {
    // 使用全局 i18n 实例进行翻译
    const translatedTitle = translateKey ? i18n.global.t(translateKey) : title
    pageTitle.value = translatedTitle
    
    // 获取网站名称
    const siteName = configStore.configs?.site.title || 'Lsky Pro+ - 2x.nz特供离线版'
    
    // 设置文档标题
    document.title = translatedTitle ? `${translatedTitle} - ${siteName}` : siteName
  }

  const getTitle = () => pageTitle.value

  return {
    pageTitle,
    setTitle,
    getTitle
  }
}