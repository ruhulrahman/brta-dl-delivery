const childRoutes = (prop) => [
  {
    path: 'dashboard',
    name: prop + 'dashboard',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/Dashboard.vue')
  },
  {
    path: 'template-one',
    name: prop + 'template_one',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/template-one/List.vue')
  },
  {
    path: 'subscriber',
    name: prop + 'subscriber',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/subscriber/List.vue')
  },
  {
    path: 'admin',
    name: prop + 'admin',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/admin/List.vue')
  },
  {
    path: 'cart',
    name: prop + 'cart',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/cart/List.vue')
  },
  {
    path: 'permission',
    name: prop + 'permission',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/user-management/permission/List.vue')
  },
  {
    path: 'role',
    name: prop + 'role',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/user-management/role/List.vue')
  },
  {
    path: 'add-or-update-role/:role_id?',
    name: prop + 'add_or_update_role',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/user-management/role/Form.vue')
  },
  {
    path: 'user',
    name: prop + 'user',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/user-management/user/List.vue')
  },
  {
    path: 'subscription-plan',
    name: prop + 'subscription_plan',
    meta: { auth: true, name: 'Editable' },
    component: () => import('../pages/subscription-plan/List.vue')
  },
  {
    path: 'change-password',
    name: prop + 'change_password',
    meta: {
      auth: true,
      name: 'Editable'
    },
    component: () => import('../pages/security/ChangePassword.vue')
  }
]

const routes = [
  {
    path: '/',
    name: 'designer_service.configuration',
    component: () => import('@/layouts/MainLayout.vue'),
    // meta: { auth: true },
    children: childRoutes('')
  }
]

export default routes
