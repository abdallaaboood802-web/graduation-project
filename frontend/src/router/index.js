import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import ProfileView from '../views/ProfileView.vue'

const routes = [
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    // يفضل تفعيل حماية المسارات لمنع غير المسجلين من الدخول
    meta: { requiresAuth: true } 
  },
  {
    path: '/',
    name: 'home',
    component: () => import('../views/Home.vue')
  },
  {
    path: '/categories',
    name: 'categories',
    component: () => import('../views/CategoriesView.vue') // تم إضافة مسار صفحة التصنيفات هنا
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue')
  },
  {
    path: '/books/:slug',
    name: 'BookDetails',
    component: () => import('../views/BookDetails.vue')
  },
  {
    path: '/books',
    name: 'books',
    component: () => import('../views/Books.vue')
  },
  {
    path: '/upload',
    name: 'upload',
    component: () => import('../views/Upload.vue')
  },
  {
    path: '/admin/books',
    name: 'AdminPanel',
    component: () => import('../views/AdminPanel.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  const isLoggedIn = !!(token && token !== 'undefined' && token !== 'null')
  
  let user = null
  if (isLoggedIn) {
    try {
      user = JSON.parse(localStorage.getItem('user'))
    } catch (e) {
      console.error("Error parsing user from localStorage:", e)
    }
  }

  // 1. فحص إذا كان المسار يتطلب تسجيل دخول
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isLoggedIn) {
      next({ name: 'Login', query: { redirect: to.fullPath } })
      return
    }
  }

  // 2. فحص إذا كان المسار يتطلب صلاحيات الإدارة (Admin/Moderator)
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    const isAdmin = user && (user.role === 'admin' || user.role === 'moderator')
    if (!isAdmin) {
      next('/')
      return
    }
  }

  next()
})

export default router