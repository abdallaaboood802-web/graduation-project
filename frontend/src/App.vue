<script setup>
import Navbar from './components/Navbar.vue'
import AppFooter from './components/AppFooter.vue'
</script>

<template>
  <!-- شريط التنقل العلوي -->
  <Navbar />

  <!-- منطقة عرض محتوى الصفحات مع تأثير الانتقال التدريجي الأنيق -->
  <router-view v-slot="{ Component }">
    <transition name="page-fade" mode="out-in">
      <component :is="Component" />
    </transition>
  </router-view>

  <!-- تذييل الصفحة (الفوتر) -->
  <AppFooter />
</template>

<style>
/* تأثير الانتقال السلس بين الصفحات (Page Transitions) */
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>