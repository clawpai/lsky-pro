import {createRouter, createWebHistory} from 'vue-router'
import routes from "@/router/routes";
import { useUserStore } from "@/stores/user";
import {useLocaleStore} from "@/stores/locale";
import { usePageTitle } from "@/composables/usePageTitle";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()
  const localeStore = useLocaleStore()

  if (to.meta.auth && ! userStore.token && to.name !== 'Login') {
    return next({ name: 'Login' })
  } else {
    // TODO 在什么时候刷新用户信息？
    if (userStore.token && ! userStore.isLoggedIn) {
      await userStore.fetchUserProfile()
      // 根据用户配置的语言设置语言
      localeStore.setLocale(userStore.profile?.options.language || 'zh-CN')
    }
  }

  next()
})

router.afterEach((to) => {
  // 设置页面标题
  const { setTitle } = usePageTitle()
  if (to.meta.title) {
    setTitle('', to.meta.title as string)
  }
})


export default router
