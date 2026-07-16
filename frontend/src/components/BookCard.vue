<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Download } from 'lucide-vue-next'
import { booksApi } from '../services/api'

const props = defineProps({
  book: { type: Object, required: true },
  fallbackClass: { type: String, default: 'c1' }
})

const router = useRouter()
const isDownloading = ref(false)

const authorName = computed(() => {
  if (!props.book.author) return 'مؤلف مجهول'
  
  if (typeof props.book.author === 'object') {
    return props.book.author.name || 'مؤلف مجهول'
  }
  
  try {
    const parsed = JSON.parse(props.book.author)
    return parsed.name || 'مؤلف مجهول'
  } catch (e) {
    return props.book.author
  }
})

const fileSizeDisplay = computed(() => {
  return props.book.file_size || 'غير محدد'
})

const fullStars = computed(() => Math.round(props.book.rating ?? 0))
const emptyStars = computed(() => 5 - fullStars.value)

function goToBook() {
  router.push(`/books/${props.book.slug}`)
}

async function handleDownload(event) {
  event.stopPropagation() // لمنع تداخل الأحداث والانتقال لصفحة القراءة عند الضغط على الزر
  
  if (isDownloading.value) return
  isDownloading.value = true
  
  try {
    // إرسال الطلب (الاستجابة تأتي من نوع blob)
    const response = await booksApi.download(props.book.slug)
    
    // تحويل البيانات الخام (Blob) القادمة من الباك-إند إلى رابط تحميل مباشر في المتصفح
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `${props.book.title}.pdf`)
    document.body.appendChild(link)
    link.click()
    
    // إزالة الرابط المؤقت لتوفير موارد الذاكرة
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('فشل تحميل الملف:', error)
    alert('عذراً، حدثت مشكلة أثناء محاولة تحميل ملف الـ PDF.')
  } finally {
    isDownloading.value = false
  }
}
</script>

<template>
  <div class="book-card" @click="goToBook" style="cursor: pointer;">
    <div class="cover" :class="!book.cover_url ? fallbackClass : ''">
      <!-- ستظهر الأغلفة الحقيقية هنا طالما أن السيرفر يُرسل الـ URL مكتملاً ومربوطاً -->
      <img v-if="book.cover_url" :src="book.cover_url" :alt="book.title" />
      <span v-if="book.is_top" class="ribbon">الأكثر تحميلاً</span>
      <span v-else-if="book.is_new" class="ribbon">جديد</span>
      <div v-if="!book.cover_url" class="title-on-cover">{{ book.title }}</div>
    </div>
    <div class="book-info">
      <h4>{{ book.title }}</h4>
      <div class="author">تأليف: {{ authorName }}</div>
      <div class="stars">
        <span v-for="n in fullStars" :key="'f' + n">★</span><span
          v-for="n in emptyStars" :key="'e' + n" class="empty">★</span>
      </div>
      <div class="book-foot">
        <span class="tag">{{ book.file_type || 'PDF' }} · {{ fileSizeDisplay }}</span>
        <button 
          class="btn-download" 
          @click="handleDownload" 
          :disabled="isDownloading"
          style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
        >
          <Download :size="14" />
          {{ isDownloading ? 'تحميل...' : 'تحميل' }}
        </button>
      </div>
    </div>
  </div>
</template>