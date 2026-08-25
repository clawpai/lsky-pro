import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import i18n from "@/i18n";

// 样式导入
import 'vfonts/FiraCode.css'
import './assets/main.css'
import './index.css'

// 导入字体图标
import { library } from '@fortawesome/fontawesome-svg-core'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// 导入字体
library.add(far)
library.add(fas)
library.add(fab)

// 创建应用
const app = createApp(App)

// 全局注册FontAwesomeIcon组件
app.component('FontAwesomeIcon', FontAwesomeIcon)

// 使用插件
app.use(createPinia())
app.use(router)
app.use(i18n)

// 挂载应用
app.mount('#app')
