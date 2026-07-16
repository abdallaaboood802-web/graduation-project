<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { SlidersHorizontal } from 'lucide-vue-next'
import BookCard from '../components/BookCard.vue'
import { booksApi } from '../services/api'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const books = ref([])
const total = ref(0)
const categories = ref([])
const loading = ref(true)
const error = ref(null)

const coverClasses = ['c1', 'c2', 'c3', 'c4']
const sizePills = [
  { label: 'أقل من 2MB', value: 'lt2' },
  { label: '2 – 5MB', value: '2to5' },
  { label: '5 – 10MB', value: '5to10' },
  { label: 'أكثر من 10MB', value: 'gt10' }
]

// تجهيز الفلاتر لتقرأ من الـ URL مباشرة عند تحميل الصفحة لأول مرة
const filters = reactive({
  search: route.query.search || '',
  languages: route.query.languages ? route.query.languages.split(',') : ['ar'],
  categories: route.query.categories ? route.query.categories.split(',').map(Number) : [],
  fileSize: route.query.file_size || null,
  minRating: route.query.min_rating ? Number(route.query.min_rating) : null,
  sort: route.query.sort || 'latest'
})

async function loadBooks() {
  loading.value = true
  error.value = null
  try {
    const { data } = await booksApi.list({
      search: filters.search || undefined,
      languages: filters.languages.join(','),
      categories: filters.categories.join(','),
      file_size: filters.fileSize || undefined,
      min_rating: filters.minRating || undefined,
      sort: filters.sort
    })
    books.value = data.data ?? data
    total.value = data.meta?.total ?? books.value.length
  } catch (e) {
    error.value = 'تعذّر تحميل الكتب من الخادم. تحقق من تشغيل الـ backend.'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadCategories() {
  try {
    const { data } = await booksApi.categories()
    categories.value = data.data ?? data
  } catch (e) {
    console.error(e)
  }
}

function toggleLanguage(lang) {
  const idx = filters.languages.indexOf(lang)
  if (idx > -1) filters.languages.splice(idx, 1)
  else filters.languages.push(lang)
}

function toggleCategory(catId) {
  const idx = filters.categories.indexOf(catId)
  if (idx > -1) {
    filters.categories.splice(idx, 1)
  } else {
    filters.categories.push(catId)
  }
}

function toggleRating(rating) {
  filters.minRating = filters.minRating === rating ? null : rating
}

function toggleFileSize(size) {
  filters.fileSize = filters.fileSize === size ? null : size
}

onMounted(() => {
  loadCategories()
  loadBooks()
})

// مراقبة الفلاتر لتحديث الـ URL تلقائياً وإعادة طلب البيانات من السيرفر فوراً
watch(filters, (newFilters) => {
  router.push({
    query: {
      search: newFilters.search || undefined,
      languages: newFilters.languages.length ? newFilters.languages.join(',') : undefined,
      categories: newFilters.categories.length ? newFilters.categories.join(',') : undefined,
      
      // 👇 تأكد أن اسم الحقل هنا يطابق تماماً ولا يحتوي على أي رموز غريبة
      file_size: newFilters.fileSize || undefined, 
      
      min_rating: newFilters.minRating || undefined,
      sort: newFilters.sort !== 'latest' ? newFilters.sort : undefined
    }
  })
  loadBooks()
}, { deep: true })

watch(() => route.query.search, (newSearch) => {
  filters.search = newSearch || ''
  loadBooks()
})
</script>

<template>
  <section id="books">
    <div class="browse-head">
      <div class="container">
        <h1>تصفح الكتب</h1>
        <p>+10,000 كتاب بانتظارك — رتّبها بالطريقة التي تناسبك</p>
      </div>
    </div>

    <div class="container browse-layout">
      <aside class="filter-card ui-font">
        <h3>
          <SlidersHorizontal :size="18" color="#0A3D36" />
          الفلاتر
        </h3>

        <!-- فلتر اللغة -->
        <div class="filter-group">
          <h4>اللغة</h4>
          <label class="filter-option">
            <input type="checkbox" :checked="filters.languages.includes('ar')" @change="toggleLanguage('ar')" />
            العربية
          </label>
          <label class="filter-option">
            <input type="checkbox" :checked="filters.languages.includes('en')" @change="toggleLanguage('en')" />
            الإنجليزية
          </label>
          <label class="filter-option">
            <input type="checkbox" :checked="filters.languages.includes('fr')" @change="toggleLanguage('fr')" />
            الفرنسية
          </label>
        </div>

        <!-- فلتر حجم الملف -->
        <div class="filter-group">
          <h4>حجم الملف</h4>
          <div class="size-pill-row">
            <span
              v-for="pill in sizePills"
              :key="pill.value"
              class="size-pill"
              :class="{ selected: filters.fileSize === pill.value }"
              @click="toggleFileSize(pill.value)"
            >{{ pill.label }}</span>
          </div>
        </div>

        <!-- فلتر التصنيف التفاعلي -->
        <div class="filter-group">
          <h4>التصنيف</h4>
          <label v-for="cat in categories" :key="cat.id" class="filter-option">
            <input 
              type="checkbox" 
              :checked="filters.categories.includes(cat.id)" 
              @change="toggleCategory(cat.id)" 
            />
            {{ cat.name }} 
            <span class="count">{{ cat.books_count?.toLocaleString('ar') }}</span>
          </label>
        </div>

        <!-- فلتر التقييم التفاعلي -->
        <div class="filter-group">
          <h4>التقييم</h4>
          <label class="filter-option" @click.prevent="toggleRating(4)">
            <input type="checkbox" :checked="filters.minRating === 4" readonly />
            <span class="stars" style="font-size:0.85rem">★★★★<span class="empty">★</span></span> فأعلى
          </label>
        </div>

        <!-- زر تطبيق الفلاتر (يمكنك حذفه أو إبقائه، لأن البيانات تتحدث الآن تلقائياً عند أي نقرة) -->
        <button class="btn-apply" @click="loadBooks">تطبيق الفلاتر</button>
      </aside>

      <div>
        <div class="browse-toolbar">
          <div class="result-count">عرض <b>{{ books.length }}</b> كتاباً من أصل <b>{{ total.toLocaleString('ar') }}</b></div>
          <select class="sort-select" v-model="filters.sort">
            <option value="latest">الأحدث أولاً</option>
            <option value="rating">الأعلى تقييماً</option>
            <option value="downloads">الأكثر تحميلاً</option>
            <option value="alpha">أبجدياً (أ-ي)</option>
          </select>
        </div>

        <p v-if="loading" class="state-msg">جارٍ تحميل الكتب...</p>
        <p v-else-if="error" class="state-msg">{{ error }}</p>
        <p v-else-if="!books.length" class="state-msg">لا توجد كتب مطابقة لهذه الفلاتر.</p>
        <div v-else class="browse-grid">
          <BookCard
            v-for="(book, i) in books"
            :key="book.id"
            :book="book"
            :fallback-class="coverClasses[i % coverClasses.length]"
          />
        </div>
      </div>
    </div>
  </section>
</template>