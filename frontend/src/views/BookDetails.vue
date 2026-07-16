<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { booksApi } from '../services/api'

const route = useRoute()
const router = useRouter()
const book = ref(null)
const loading = ref(true)
const error = ref(null)

// حالة القراءة/التحميل الفعلية (منفصلة عن حالة جلب بيانات الكتاب)
const pdfUrl = ref('')
const isBlobUrl = ref(false) // لمعرفة متى نُلغي الـ object URL عند مغادرة الصفحة
const readingLoading = ref(false)
const readingError = ref('')

const authorName = computed(() => {
  if (!book.value || !book.value.author) return 'مؤلف مجهول'
  if (typeof book.value.author === 'object') {
    return book.value.author.name || 'مؤلف مجهول'
  }
  return book.value.author
})

// التحقق من تسجيل الدخول
const isLoggedIn = computed(() => {
  const token = localStorage.getItem('auth_token')
  return !!(token && token !== 'undefined' && token !== 'null')
})

// 🌟 مسار التحميل المباشر الصحيح طبقاً لموقع الـ Route في الباكيند (خارج بادئة v1)
const directDownloadUrl = computed(() => {
  if (!book.value) return ''
  return `http://localhost:8000/books/${book.value.id}/download`
})

onMounted(async () => {
  try {
    loading.value = true
    error.value = null

    const response = await booksApi.show(route.params.slug)

    // استلام كائن الكتاب من الـ BookResource
    book.value = response.data.data

    if (book.value) {
      // الكتب المعتمدة متاحة للجميع مباشرة عبر رابط GET الآمن، بلا حاجة لطلب مسبق
      if (book.value.status === 'approved') {
        pdfUrl.value = directDownloadUrl.value
        isBlobUrl.value = false
      }

      // ✅ تم تصحيح الـ 404: الباكيند يتوقع الـ slug لتحديث عداد المشاهدات
      booksApi.incrementView(book.value.slug).catch(err => {
        console.error("فشل تحديث العداد:", err)
      })
    } else {
      error.value = 'لم يتم العثور على بيانات هذا الكتاب.'
    }
  } catch (err) {
    console.error("خطأ أثناء استدعاء بيانات الكتاب:", err)
    error.value = 'تعذر تحميل بيانات الكتاب حالياً.'
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => {
  // تنظيف الذاكرة: إلغاء الـ object URL إن كان قد أُنشئ من blob
  if (isBlobUrl.value && pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value)
  }
})

// للكتب غير المعتمدة: جلب الملف كـ blob مصادَق عليه
const startReading = async () => {
  if (!book.value) return

  if (!isLoggedIn.value) {
    router.push({ name: 'Login', query: { redirect: route.fullPath } })
    return
  }

  readingError.value = ''
  readingLoading.value = true
  try {
    const res = await booksApi.download(book.value.id)
    const blobUrl = URL.createObjectURL(res.data)
    pdfUrl.value = blobUrl
    isBlobUrl.value = true
  } catch (err) {
    console.error('فشل جلب رابط القراءة/التحميل:', err)
    if (err.response?.status === 401 || err.response?.status === 403) {
      readingError.value = 'لا تملك صلاحية الوصول لهذا الكتاب (غير معتمد بعد، ومتاح فقط لصاحب الرفع أو المشرفين).'
    } else if (err.response?.status === 404) {
      readingError.value = 'لا يوجد ملف مرفوع لهذا الكتاب حالياً.'
    } else {
      readingError.value = 'حدث خطأ أثناء تجهيز الملف للقراءة.'
    }
  } finally {
    readingLoading.value = false
  }
}
</script>

<template>
  <div class="container book-details-page" style="padding: 40px 20px; color: #fff; text-align: right; direction: rtl;">
    <button @click="router.back()" class="btn-back">← العودة للخلف</button>

    <div v-if="loading" class="state-msg">جاري تجهيز وضع القراءة وقارئ الـ PDF...</div>
    <div v-else-if="error" class="state-msg error">{{ error }}</div>
    <div v-else class="details-grid">

      <!-- معلومات الكتاب الجانبية -->
      <div class="info-panel">
        <h1 class="title">{{ book.title }}</h1>
        <p class="author">تأليف: {{ authorName }}</p>
        <p class="desc">{{ book.description || 'لا يوجد وصف متاح لهذا الكتاب حالياً.' }}</p>
      </div>

      <!-- قارئ ملفات الـ PDF المتكامل -->
      <div class="reader-panel">
        <h2 style="margin-bottom: 15px; font-family: 'Amiri', serif; color: #F6C90E;">وضع القراءة المباشرة 📖</h2>

        <!-- الملف جاهز: نعرض الـ iframe + رابط تحميل حقيقي -->
        <div v-if="pdfUrl">
          <div class="iframe-container">
            <iframe :src="pdfUrl" width="100%" height="650px" style="border: none; border-radius: 8px;"></iframe>
          </div>
          <a :href="pdfUrl" target="_blank" rel="noopener" class="btn-download-file">
            ⬇ تحميل الملف مباشرة
          </a>
        </div>

        <!-- جاري تجهيز رابط القراءة -->
        <div v-else-if="readingLoading" class="no-file-msg">جاري تجهيز الملف للقراءة...</div>

        <!-- حدث خطأ ودّي -->
        <div v-else-if="readingError" class="no-file-msg error">
          <p>{{ readingError }}</p>
          <button v-if="!isLoggedIn" class="btn-start-reading"
            @click="router.push({ name: 'Login', query: { redirect: route.fullPath } })">
            تسجيل الدخول
          </button>
          <button v-else class="btn-start-reading" @click="startReading">إعادة المحاولة</button>
        </div>

        <!-- الحالة الافتراضية: زر لبدء القراءة -->
        <div v-else class="no-file-msg">
          <p>اضغط على الزر لبدء القراءة أو تحميل الكتاب.</p>
          <button class="btn-start-reading" @click="startReading">ابدأ القراءة الآن</button>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.btn-back {
  background: transparent;
  border: 1px solid #F6C90E;
  color: #F6C90E;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  margin-bottom: 30px;
  font-family: inherit;
}

.btn-back:hover {
  background: rgba(246, 201, 14, 0.1);
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 40px;
}

@media (max-width: 900px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}

.info-panel {
  background: #104c3e;
  padding: 30px;
  border-radius: 12px;
  height: fit-content;
}

.title {
  font-family: 'Amiri', serif;
  font-size: 2rem;
  color: #F6C90E;
  margin-bottom: 10px;
}

.author {
  font-size: 1.1rem;
  opacity: 0.8;
  margin-bottom: 20px;
}

.desc {
  line-height: 1.8;
  opacity: 0.9;
}

.reader-panel {
  background: #0d3b30;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.iframe-container {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.no-file-msg,
.state-msg {
  text-align: center;
  padding: 50px;
  font-size: 1.2rem;
}

.no-file-msg.error {
  color: #ffb4a8;
}

.btn-start-reading,
.btn-download-file {
  display: inline-block;
  margin-top: 16px;
  background: linear-gradient(135deg, #D4AF37, #F6C90E);
  color: #0A3D36;
  font-weight: 800;
  font-family: 'Cairo', sans-serif;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  cursor: pointer;
  font-size: 0.95rem;
  text-decoration: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-start-reading:hover,
.btn-download-file:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(212, 175, 55, 0.35);
}

.btn-download-file {
  display: block;
  width: fit-content;
  margin: 14px auto 0;
}
</style>