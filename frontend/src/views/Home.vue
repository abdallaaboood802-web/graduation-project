<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import BookCard from '../components/BookCard.vue'
import { booksApi } from '../services/api'

const featuredBooks = ref([])
const categories = ref([])
const stats = ref([
  { label: 'كتاب رقمي', value: null },
  { label: 'تصنيف أدبي وعلمي', value: null },
  { label: 'قارئ نشط', value: null },
  { label: 'مؤلف ومساهم', value: null }
])
const loading = ref(true)
const error = ref(null)

const coverClasses = ['c1', 'c2', 'c3', 'c4']

async function loadHomeData() {
  loading.value = true
  error.value = null
  try {
    const [booksRes, catsRes, statsRes] = await Promise.allSettled([
      booksApi.featured(),
      booksApi.categories(),
      booksApi.stats()
    ])

    if (booksRes.status === 'fulfilled') {
      featuredBooks.value = booksRes.value.data.data ?? booksRes.value.data
    }
    if (catsRes.status === 'fulfilled') {
      categories.value = catsRes.value.data.data ?? catsRes.value.data
    }
    if (statsRes.status === 'fulfilled') {
      const s = statsRes.value.data.data ?? statsRes.value.data
      stats.value = [
        { label: 'كتاب رقمي', value: s.books_count },
        { label: 'تصنيف أدبي وعلمي', value: s.categories_count },
        { label: 'قارئ نشط', value: s.readers_count },
        { label: 'مؤلف ومساهم', value: s.authors_count }
      ]
    }
  } catch (e) {
    error.value = 'تعذّر تحميل البيانات من الخادم. تحقق من تشغيل الـ backend.'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(loadHomeData)
</script>

<template>
  <section id="home">
    <div class="hero">
      <div class="container hero-grid">
        <div>
          <div class="eyebrow">مكتبتك الرقمية العربية</div>
          <h1>اقرأ بعمق... في <em>مكتبة</em> واسعة<br />بلا حدود</h1>
          <p>آلاف الكتب العربية في الأدب والفكر والعلوم، منظمة بعناية وجاهزة للتحميل، في تجربة قراءة أنيقة تليق بما تقرأ.</p>
          <div class="hero-actions">
            <RouterLink class="btn-gold" to="/books">استكشف المكتبة</RouterLink>
            <RouterLink class="btn-outline" to="/upload">ارفع كتابك الآن</RouterLink>
          </div>
        </div>

        <div class="book-stage">
          <div class="glow-ring"></div>
          <div class="book-stack">
            <div class="book b4"></div>
            <div class="book b3"></div>
            <div class="book b2"></div>
            <div class="book b1"><img src="" alt=""></div>
          </div>
        </div>
      </div>

      <div class="stats-bar">
        <div class="container stats-grid ui-font">
          <div v-for="s in stats" :key="s.label">
            <h3>{{ s.value != null ? '+' + s.value.toLocaleString('ar') : '—' }}</h3>
            <span>{{ s.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Books -->
    <div class="section">
      <div class="container">
        <div class="section-head">
          <div>
            <h2>كتب مقترحة لك</h2>
            <p>اختيارات هذا الأسبوع من قلب المكتبة</p>
          </div>
          <RouterLink class="see-all" to="/books">عرض جميع الكتب ←</RouterLink>
        </div>

        <p v-if="loading" class="state-msg">جارٍ تحميل الكتب...</p>
        <p v-else-if="error" class="state-msg">{{ error }}</p>
        <p v-else-if="!featuredBooks.length" class="state-msg">لا توجد كتب مقترحة حالياً.</p>
        <div v-else class="book-grid">
          <BookCard
            v-for="(book, i) in featuredBooks"
            :key="book.id"
            :book="book"
            :fallback-class="coverClasses[i % coverClasses.length]"
          />
        </div>
      </div>
    </div>

    <!-- Categories -->
    <div class="section cat-section">
      <div class="container">
        <div class="section-head">
          <div>
            <h2>تصفح حسب التصنيف</h2>
            <p>من الرواية إلى الفلسفة، اختر عالمك</p>
          </div>
        </div>
        <div class="chip-row">
          <RouterLink
            v-for="cat in categories"
            :key="cat.id"
            class="chip"
            :to="{ path: '/books', query: { category: cat.id } }"
          >
            {{ cat.name }} <span class="n">{{ cat.books_count?.toLocaleString('ar') }}</span>
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
