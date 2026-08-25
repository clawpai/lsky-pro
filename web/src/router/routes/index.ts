import auth from "@/router/routes/auth";
import user from "@/router/routes/user";
import type {RouteRecordRaw} from "vue-router";
import explore from "@/router/routes/explore";
import { lazyLoadComponent } from "@/utils/lazyload";

// 优化动态导入，添加注释以便进行自动代码分割
const HomeView = lazyLoadComponent(() => import(/* webpackChunkName: "home" */ '@/views/HomeView.vue'));
const PageView = lazyLoadComponent(() => import(/* webpackChunkName: "page" */ '@/views/PageView.vue'));
const ShareView = lazyLoadComponent(() => import(/* webpackChunkName: "share" */ '@/views/ShareView.vue'));
const NotFoundView = lazyLoadComponent(() => import(/* webpackChunkName: "error" */ '@/views/NotFoundView.vue'));

export default [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: {
      title: 'Home'
    }
  },
  {
    path: '/pages/:slug',
    name: 'page',
    component: PageView,
  },
  {
    path: '/shares/:slug',
    name: 'share',
    component: ShareView,
    meta: {
      title: 'Share'
    }
  },
  ...explore,
  ...auth,
  ...user,
  // 回退路由
  {
    path: '/:pathMatch(.*)',
    component: NotFoundView,
    meta: {
      title: 'Page Not Found'
    }
  }
] as RouteRecordRaw[]