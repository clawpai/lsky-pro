import type {RouteRecordRaw} from "vue-router";
import { lazyLoadComponent } from "@/utils/lazyload";
import { useConfigStore } from "@/stores/config";

// 优化动态导入
const ExploreIndexView = lazyLoadComponent(() => import(/* webpackChunkName: "explore-index" */ '@/views/explore/IndexView.vue'));
const UserCenterView = lazyLoadComponent(() => import(/* webpackChunkName: "explore-user" */ '@/views/explore/UserCenterView.vue'));
const AlbumView = lazyLoadComponent(() => import(/* webpackChunkName: "explore-album" */ '@/views/explore/AlbumView.vue'));

// 导航守卫函数，检查广场是否启用
const checkGalleryEnabled = async (to: any, from: any, next: any) => {
  const configStore = useConfigStore()
  
  // 如果配置还没有加载，先等待加载
  if (!configStore.configs) {
    // 这里需要触发配置加载，但由于这是路由守卫，我们简化处理
    // 如果配置未加载，暂时允许通过，让页面组件自己处理
    next()
    return
  }
  
  if (!configStore.configs?.app?.enable_explore) {
    // 返回404页面
    next({ path: '/404' })
  } else {
    next()
  }
}

export default [
  {
    path: '/explore',
    name: 'explore',
    component: ExploreIndexView,
    beforeEnter: checkGalleryEnabled,
    meta: {
      title: 'Square'
    }
  },
  {
    path: '/explore/@:username',
    name: 'userCenter',
    component: UserCenterView,
    beforeEnter: checkGalleryEnabled,
    meta: {
      title: 'User Center'
    }
  },
  {
    path: '/explore/albums/:id',
    name: 'album',
    component: AlbumView,
    beforeEnter: checkGalleryEnabled,
    meta: {
      title: 'Album'
    }
  },
] as RouteRecordRaw[]