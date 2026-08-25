import zhCN from './locales/zh-CN.json'
import enUS from './locales/en-US.json'
import {createI18n} from "vue-i18n";

// Type-define 'zh-CN' as the master schema for the resource
type MessageSchema = typeof zhCN

const locale = localStorage.getItem('locale') || 'zh-CN'

// 使用类型断言确保enUS符合必要的类型
const i18n = createI18n<{
  message: MessageSchema
  legacy: false 
}, 'en-US' | 'zh-CN'>({
  legacy: false, // 使用Composition API模式
  locale: locale,
  fallbackLocale: 'zh-CN',
  messages: {
    "zh-CN": zhCN,
    "en-US": enUS as unknown as MessageSchema, // 使用类型断言解决类型问题
  },
})

export default i18n