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
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

export default router