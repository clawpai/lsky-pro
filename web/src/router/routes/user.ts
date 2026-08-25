import type {RouteRecordRaw} from "vue-router";

export default [
  {
    path: '/user/dashboard',
    name: 'userDashboard',
    component: () => import('@/views/user/dashboard/IndexView.vue'),
    meta: {
      auth: true,
      title: 'Dashboard'
    }
  },
  {
    path: '/user/photos',
    name: 'userPhoto',
    component: () => import('@/views/user/photo/IndexView.vue'),
    meta: {
      auth: true,
      title: 'My Photos'
    }
  },
  {
    path: '/user/albums',
    name: 'userAlbum',
    component: () => import('@/views/user/album/IndexView.vue'),
    meta: {
      auth: true,
      title: 'My Albums'
    }
  },
  {
    path: '/user/albums/:id',
    name: 'userAlbumDetail',
    component: () => import('@/views/user/album/DetailView.vue'),
    meta: {
      auth: true,
      title: 'Album Detail'
    }
  },
  {
    path: '/user/shares',
    name: 'userShare',
    component: () => import('@/views/user/share/IndexView.vue'),
    meta: {
      auth: true,
      title: 'My Shares'
    }
  },
  {
    path: '/user/plans',
    name: 'userPlan',
    component: () => import('@/views/user/plan/IndexView.vue'),
    meta: {
      auth: true,
      title: 'Purchase Subscription'
    }
  },
  {
    path: '/user/plans/:id',
    name: 'userPlanDetail',
    component: () => import('@/views/user/plan/DetailView.vue'),
    meta: {
      auth: true,
      title: 'Plan Detail'
    }
  },
  {
    path: '/user/orders',
    name: 'userOrder',
    component: () => import('@/views/user/order/IndexView.vue'),
    meta: {
      auth: true,
      title: 'My Orders'
    }
  },
  {
    path: '/user/orders/:trade_no',
    name: 'userOrderDetail',
    component: () => import('@/views/user/order/DetailView.vue'),
    meta: {
      auth: true,
      title: 'Order Detail'
    }
  },
  {
    path: '/user/tickets',
    name: 'userTicket',
    component: () => import('@/views/user/ticket/IndexView.vue'),
    meta: {
      auth: true,
      title: 'My Tickets'
    }
  },
  {
    path: '/user/tickets/create',
    name: 'userTicketCreate',
    component: () => import('@/views/user/ticket/CreateView.vue'),
    meta: {
      auth: true,
      title: 'Create Ticket'
    }
  },
  {
    path: '/user/tickets/:issue_no',
    name: 'userTicketDetail',
    component: () => import('@/views/user/ticket/DetailView.vue'),
    meta: {
      auth: true,
      title: 'Ticket Detail'
    }
  },
  {
    path: '/user/tokens',
    name: 'userToken',
    component: () => import('@/views/user/token/IndexView.vue'),
    meta: {
      auth: true,
      title: 'API Tokens'
    }
  },
  {
    path: '/user/tokens/create',
    name: 'userTokenCreate',
    component: () => import('@/views/user/token/CreateView.vue'),
    meta: {
      auth: true,
      title: 'Create Token'
    }
  },
  {
    path: '/user/settings',
    name: 'userSetting',
    component: () => import('@/views/user/setting/IndexView.vue'),
    meta: {
      auth: true,
      title: 'Settings'
    }
  },
  {
    path: '/user/profile',
    name: 'userProfile',
    meta: {
      auth: true,
      title: 'Profile'
    },
    component: () => import('@/views/user/profile/IndexView.vue')
  }
] as RouteRecordRaw[]