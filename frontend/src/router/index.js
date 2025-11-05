import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/courses',
    name: 'Courses',
    component: () => import('../views/Courses.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/courses/:id',
    name: 'CourseDetail',
    component: () => import('../views/CourseDetail.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/quizzes/:courseId/create',
    name: 'CreateQuiz',
    component: () => import('../views/CreateQuiz.vue'),
    meta: { requiresAuth: true, roles: ['teacher'] }
  },
  {
    path: '/quizzes/:id',
    name: 'QuizDetail',
    component: () => import('../views/QuizDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/forums/:courseId',
    name: 'Forum',
    component: () => import('../views/Forum.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/certificates',
    name: 'Certificates',
    component: () => import('../views/Certificates.vue'),
    meta: { requiresAuth: true, roles: ['student'] }
  },
  {
    path: '/videos/:courseId/create',
    name: 'CreateVideoContent',
    component: () => import('../views/CreateVideoContent.vue'),
    meta: { requiresAuth: true, roles: ['teacher'] }
  },
  {
    path: '/videos/:id',
    name: 'VideoDetail',
    component: () => import('../views/VideoDetail.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Check authentication requirement
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
    return
  }
  
  // Check role requirement (user must be authenticated at this point)
  if (to.meta.roles && authStore.isAuthenticated) {
    const userRole = authStore.userRole || authStore.user?.role
    if (!userRole || !to.meta.roles.includes(userRole)) {
      next({ name: 'Dashboard' }) // Redirect to dashboard if role doesn't match
      return
    }
  }
  
  // If accessing root and not authenticated, go to login
  if (to.path === '/' && !authStore.isAuthenticated) {
    next({ name: 'Login' })
    return
  }
  
  next()
})

export default router

