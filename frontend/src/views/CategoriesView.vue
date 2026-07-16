<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ChevronLeft, Layers, BookOpen } from 'lucide-vue-next'

const categories = ref([])
const loading = ref(true)
const error = ref(null)

// جلب التصنيفات من الباكيند المتوافق مع مسار الـ v1 والـ Resource
const fetchCategories = async () => {
  try {
    loading.value = true
    error.value = null
    
    // 💡 عدنا هنا إلى المسار الصحيح والمطابق للباكيند لديك
    const response = await axios.get('http://localhost:8000/api/v1/categories')
    
    // استخراج البيانات بأمان سواء كانت مغلفة بـ data أو مصفوفة مباشرة
    categories.value = response.data.data || response.data
  } catch (err) {
    console.error('Error fetching categories:', err)
    error.value = 'فشل جلب التصنيفات، يرجى المحاولة مرة أخرى لاحقاً.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCategories()
})
</script>

<template>
  <div class="categories-container" dir="rtl">
    <header class="categories-header">
      <div class="header-icon">
        <Layers :size="32" color="#f7c948" />
      </div>
      <h1>تصنيفات الكتب</h1>
      <p>استكشف مكتبة إشراق بوك من خلال الأقسام والتصنيفات المتنوعة</p>
    </header>

    <!-- حالة التحميل -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>جاري تحميل التصنيفات...</p>
    </div>

    <!-- حالة الخطأ -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="fetchCategories" class="btn-retry">إعادة المحاولة</button>
    </div>

    <!-- قائمة التصنيفات الشجرية -->
    <div v-else class="categories-grid">
      <div 
        v-for="category in categories" 
        :key="category.id" 
        class="category-card"
      >
        <div class="category-info">
          <!-- اسم القسم الرئيسي -->
          <div class="main-category-header">
            <h3>{{ category.name || category.title }}</h3>
            <span class="books-count" v-if="category.approved_books_count !== undefined">
              {{ category.approved_books_count }} كتاباً
            </span>
          </div>
          
          <p class="description">
            {{ category.description || 'استكشف الكتب المتنوعة في هذا القسم الرائع.' }}
          </p>

          <!-- عرض الأقسام الفرعية (Children) إن وجدت -->
          <div v-if="category.children && category.children.length > 0" class="subcategories-section">
            <h4>الأقسام الفرعية:</h4>
            <div class="subcategories-list">
              <router-link 
                v-for="sub in category.children" 
                :key="sub.id"
                :to="{ path: '/books', query: { category: sub.id } }"
                class="subcategory-tag"
              >
                <span>{{ sub.name }}</span>
                <span class="sub-count" v-if="sub.approved_books_count">
                  ({{ sub.approved_books_count }})
                </span>
              </router-link>
            </div>
          </div>
        </div>
        
        <!-- تصفح القسم الرئيسي بالكامل -->
        <router-link 
          :to="{ path: '/books', query: { category: category.id } }" 
          class="explore-link"
        >
          <span>تصفح كل كتب القسم</span>
          <ChevronLeft :size="18" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.categories-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
  min-height: 80vh;
  color: #f8fafc;
}

.categories-header {
  text-align: center;
  margin-bottom: 50px;
}

.header-icon {
  display: inline-flex;
  padding: 15px;
  background: rgba(247, 201, 72, 0.1);
  border-radius: 50%;
  margin-bottom: 15px;
}

.categories-header h1 {
  font-size: 2.5rem;
  color: #f7c948;
  margin-bottom: 10px;
}

.categories-header p {
  color: #94a3b8;
  font-size: 1.1rem;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 25px;
  direction: rtl;
  justify-content: start;
}

.category-card {
  background: #0a3a30;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.3s, box-shadow 0.3s;
  text-align: right;
}

.category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  border-color: rgba(247, 201, 72, 0.3);
}

.main-category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.category-info h3 {
  font-size: 1.4rem;
  color: #f7c948;
  font-weight: bold;
  margin: 0;
}

.category-info .description {
  color: #cbd5e1;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 20px;
}

.subcategories-section {
  margin-top: 15px;
  border-top: 1px dashed rgba(255, 255, 255, 0.1);
  padding-top: 15px;
}

.subcategories-section h4 {
  font-size: 0.9rem;
  color: #94a3b8;
  margin-bottom: 10px;
  font-weight: 600;
}

.subcategories-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 15px;
}

.subcategory-tag {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f8fafc;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.85rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all 0.2s;
}

.subcategory-tag:hover {
  background: rgba(247, 201, 72, 0.15);
  border-color: #f7c948;
  color: #f7c948;
}

.sub-count {
  font-size: 0.75rem;
  color: #94a3b8;
}

.books-count {
  display: inline-block;
  background: rgba(247, 201, 72, 0.15);
  color: #f7c948;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: bold;
}

.explore-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: #f7c948;
  text-decoration: none;
  font-weight: bold;
  transition: color 0.2s;
}

.explore-link:hover {
  color: #ffffff;
}

.loading-state, .error-state {
  text-align: center;
  padding: 80px 0;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(247, 201, 72, 0.1);
  border-top-color: #f7c948;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-retry {
  background: #f7c948;
  color: #0a3a30;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 15px;
}
</style>