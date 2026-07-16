<script setup>
import { RouterLink } from 'vue-router'
import { BookOpen } from 'lucide-vue-next'

const year = new Date().getFullYear()
</script>

<template>
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="logo" style="margin-bottom:14px;">
            <span class="mark"><BookOpen :size="20" color="#0A3D36" /></span>
            إشراق <span class="gold">بوك</span>
          </div>
          <p style="font-size:0.88rem; max-width:280px;">مكتبة رقمية عربية تجمع آلاف الكتب في مكان واحد، بتجربة قراءة أنيقة وواسعة.</p>
        </div>
        <div class="ui-font">
          <h5>روابط</h5>
          <ul>
            <li><RouterLink to="/">الرئيسية</RouterLink></li>
            <li><RouterLink to="/books">الكتب</RouterLink></li>
            <li><RouterLink to="/">التصنيفات</RouterLink></li>
            <li><RouterLink to="/upload">رفع كتاب</RouterLink></li>
          </ul>
        </div>
        <div class="ui-font">
          <h5>الدعم</h5>
          <ul><li><a>الأسئلة الشائعة</a></li><li><a>تواصل معنا</a></li><li><a>سياسة الخصوصية</a></li></ul>
        </div>
        <div class="ui-font">
          <h5>تابعنا</h5>
          <ul><li><a>X (تويتر)</a></li><li><a>إنستغرام</a></li><li><a>تيليغرام</a></li></ul>
        </div>
      </div>
      <div class="footer-bottom ui-font">
        <span>© {{ year }} إشراق بوك. جميع الحقوق محفوظة.</span>
        <span>صُنع بحب لعشاق القراءة 📖</span>
      </div>
    </div>
  </footer>
</template>
