<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Search, User, BookOpen } from 'lucide-vue-next'

// تعريف واحد فقط للـ router لتفادي أي تكرار
const router = useRouter()

const mobileOpen = ref(false)
const searchQuery = ref('')
const isLoggedIn = ref(false) // متغير آمن لمراقبة حالة تسجيل الدخول

// التحقق من التوكن عند تحميل المكون
onMounted(() => {
  checkLoginStatus()
})

// دالة لتحديث حالة تسجيل الدخول
function checkLoginStatus() {
  const token = localStorage.getItem('auth_token')
  isLoggedIn.value = !!(token && token !== 'undefined' && token !== 'null')
}

// دالة تسجيل الخروج المصححة
const handleLogout = () => {
  // 1. حذف التوكن والبيانات
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')

  // 2. تحديث الحالة فوراً لتختفي الأزرار من الواجهة
  isLoggedIn.value = false

  // 3. توجيه المستخدم لصفحة تسجيل الدخول
  router.push('/login')
}

// دالة التوجيه الذكية عند الضغط على أيقونة البروفايل
function handleProfileClick() {
  const token = localStorage.getItem('auth_token')
  console.log("التوكن الفعلي المكتشف هو:", token)

  if (token && token !== 'undefined' && token !== 'null') {
    router.push('/profile')
  } else {
    router.push('/login')
  }
}

function toggleMobile() {
  mobileOpen.value = !mobileOpen.value
}

function closeMobile() {
  mobileOpen.value = false
}

function onSearch() {
  if (searchQuery.value.trim()) {
    router.push({ path: '/books', query: { search: searchQuery.value } })
  }
}
</script>

<template>
  <nav class="navbar" dir="rtl">
    <div class="nav-inner">
      <!-- الشعار -->
      <RouterLink to="/" class="logo" @click="closeMobile">
        <span class="mark">
          <BookOpen :size="20" color="#0A3D36" />
        </span>
        إشراق <span class="gold">بوك</span>
      </RouterLink>

      <!-- روابط التنقل -->
      <ul class="nav-links ui-font" :class="{ 'mobile-open': mobileOpen }">
        <li>
          <RouterLink to="/" @click="closeMobile">الرئيسية</RouterLink>
        </li>
        <li>
          <RouterLink to="/books" @click="closeMobile">الكتب</RouterLink>
        </li>
        <li>
          <RouterLink to="/categories" @click="closeMobile">التصنيفات</RouterLink>
        </li>
        <li>
          <RouterLink to="/upload" @click="closeMobile">رفع كتاب</RouterLink>
        </li>
      </ul>

      <!-- شريط البحث -->
      <div class="search-container">
        <form @submit.prevent="onSearch" class="search-form">
          <input v-model="searchQuery" type="text" placeholder="ابحث عن كتاب، مؤلف، تصنيف..." class="search-input" />
          <button type="submit" class="search-btn">
            <Search :size="18" color="#64748b" />
          </button>
        </form>
      </div>

      <!-- الأيقونة الصفراء الذكية للملف الشخصي -->
      <div @click="handleProfileClick" class="profile-icon-btn" style="cursor: pointer;">
        <div class="yellow-circle">
          <User :size="18" color="#0A3D36" />
        </div>
      </div>

      <!-- استخدام المتغير الآمن isLoggedIn بدلاً من localStorage المباشر لمنع الخطأ -->
      <div v-if="isLoggedIn" class="nav-auth-actions">
        <!-- زر تسجيل الخروج -->
        <button @click="handleLogout" class="btn-logout" style="margin-right: 10px;">
          تسجيل الخروج
        </button>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* تنسيقات شريط البحث والمكونات */
.search-container {
  flex: 1;
  max-width: 300px;
  margin: 0 15px;
}

.search-form {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 100%;
  padding: 8px 16px 8px 40px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 14px;
  outline: none;
  transition: 0.3s;
}

.search-input::placeholder {
  color: #94a3b8;
}

.search-input:focus {
  background: white;
  color: #0A3D36;
  border-color: #f7c948;
}

.search-btn {
  position: absolute;
  left: 12px;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.yellow-circle {
  width: 40px;
  height: 40px;
  background-color: #f7c948;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.yellow-circle:hover {
  transform: scale(1.05);
  box-shadow: 0 0 10px rgba(247, 201, 72, 0.5);
}

.btn-logout {
  background: #f7c948;
  color: #0a3a30;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

.btn-logout:hover {
  background: #e2b430;
}
</style>