<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { adminApi, booksApi } from '../services/api'
import { Check, X, FileText, Download, Eye, AlertCircle } from 'lucide-vue-next'

const pendingBooks = ref([])
const loading = ref(true)
const error = ref(null)

// حالة معاينة الكتاب المحدد
const selectedBook = ref(null)
const previewLoading = ref(false)
const previewError = ref('')
const pdfUrl = ref('')
const isBlobUrl = ref(false)

// تحميل الكتب بانتظار المراجعة
async function fetchPendingBooks() {
  loading.value = true
  error.value = null
  try {
    const { data } = await adminApi.getPendingBooks()
    pendingBooks.value = data.data ?? data
  } catch (e) {
    error.value = 'تعذّر تحميل الكتب بانتظار المراجعة. تحقق من الاتصال بالخادم.'
    console.error(e)
  } finally {
    loading.value = false
  }
}

// بدء معاينة كتاب معين
async function startPreview(book) {
  selectedBook.value = book
  previewError.value = ''
  previewLoading.value = true
  pdfUrl.value = ''
  
  if (isBlobUrl.value && pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value)
  }
  isBlobUrl.value = false

  try {
    const res = await booksApi.download(book.id)
    const blobUrl = URL.createObjectURL(res.data)
    pdfUrl.value = blobUrl
    isBlobUrl.value = true
  } catch (e) {
    console.error('فشل جلب ملف المعاينة:', e)
    previewError.value = 'حدث خطأ أثناء تحميل ملف الـ PDF للمعاينة.'
  } finally {
    previewLoading.value = false
  }
}

// إغلاق المعاينة
function closePreview() {
  selectedBook.value = null
  previewError.value = ''
  if (isBlobUrl.value && pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value)
  }
  pdfUrl.value = ''
  isBlobUrl.value = false
}

// قبول كتاب
async function approveBook(bookId) {
  if (!confirm('هل أنت متأكد من قبول ونشر هذا الكتاب؟')) return
  try {
    await adminApi.approveBook(bookId)
    // إزالة الكتاب من القائمة محلياً
    pendingBooks.value = pendingBooks.value.filter(b => b.id !== bookId)
    if (selectedBook.value && selectedBook.value.id === bookId) {
      closePreview()
    }
    alert('تم قبول ونشر الكتاب بنجاح!')
  } catch (e) {
    console.error(e)
    alert('فشل قبول الكتاب. يرجى المحاولة لاحقاً.')
  }
}

// رفض كتاب
async function rejectBook(bookId) {
  if (!confirm('هل أنت متأكد من رفض هذا الكتاب وإزالته؟')) return
  try {
    await adminApi.rejectBook(bookId)
    // إزالة الكتاب من القائمة محلياً
    pendingBooks.value = pendingBooks.value.filter(b => b.id !== bookId)
    if (selectedBook.value && selectedBook.value.id === bookId) {
      closePreview()
    }
    alert('تم رفض الكتاب بنجاح!')
  } catch (e) {
    console.error(e)
    alert('فشل رفض الكتاب. يرجى المحاولة لاحقاً.')
  }
}

onMounted(() => {
  fetchPendingBooks()
})

onBeforeUnmount(() => {
  if (isBlobUrl.value && pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value)
  }
})
</script>

<template>
  <section class="admin-panel container">
    <div class="panel-header">
      <h1>لوحة إدارة الكتب بانتظار المراجعة</h1>
      <p>مراجعة الكتب المرفوعة حديثاً من الأعضاء والموافقة على نشرها أو رفضها</p>
    </div>

    <!-- رسائل الحالة -->
    <div v-if="loading" class="state-msg">جاري تحميل قائمة الكتب بانتظار المراجعة...</div>
    <div v-else-if="error" class="state-msg error">{{ error }}</div>
    <div v-else-if="!pendingBooks.length" class="state-msg empty">
      <AlertCircle :size="48" color="#b9b4a2" />
      <p>لا توجد كتب بانتظار المراجعة والموافقة حالياً.</p>
    </div>

    <!-- جدول وقائمة الكتب -->
    <div v-else class="panel-layout">
      <div class="books-list-card">
        <table class="admin-table">
          <thead>
            <tr>
              <th>عنوان الكتاب</th>
              <th>القسم / التصنيف</th>
              <th>الرافع</th>
              <th>حجم الملف</th>
              <th>اللغة</th>
              <th>التحكم</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="book in pendingBooks" :key="book.id" :class="{ active: selectedBook && selectedBook.id === book.id }">
              <td>
                <div class="book-title-cell">
                  <FileText :size="18" color="#0A3D36" />
                  <div>
                    <strong>{{ book.title }}</strong>
                    <span class="author-name">تأليف: {{ book.author?.name || 'مجهول' }}</span>
                  </div>
                </div>
              </td>
              <td>{{ book.category?.name || 'غير محدد' }}</td>
              <td>{{ book.uploader?.name || 'غير معروف' }}</td>
              <td>{{ book.file_size || 'غير محدد' }}</td>
              <td>{{ book.language === 'ar' ? 'العربية' : book.language === 'en' ? 'الإنجليزية' : book.language }}</td>
              <td>
                <div class="action-buttons">
                  <button class="btn-action preview" @click="startPreview(book)" title="معاينة الملف">
                    <Eye :size="15" /> معاينة
                  </button>
                  <button class="btn-action approve" @click="approveBook(book.id)" title="موافقة ونشر">
                    <Check :size="15" /> موافقة
                  </button>
                  <button class="btn-action reject" @click="rejectBook(book.id)" title="رفض وإزالة">
                    <X :size="15" /> رفض
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- معاينة تفاصيل وملف الكتاب -->
      <div v-if="selectedBook" class="preview-panel">
        <div class="preview-header">
          <h3>معاينة كتاب: {{ selectedBook.title }}</h3>
          <button class="btn-close" @click="closePreview"><X :size="18" /></button>
        </div>
        
        <div class="preview-body">
          <div class="book-details-short">
            <p><strong>الوصف:</strong> {{ selectedBook.description || 'لا يوجد وصف متاح.' }}</p>
            <p><strong>بواسطة العضو:</strong> {{ selectedBook.uploader?.name || 'غير معروف' }} ({{ selectedBook.uploader?.email }})</p>
          </div>

          <!-- عارض الملف -->
          <div class="pdf-preview-box">
            <div v-if="previewLoading" class="preview-loading-msg">جاري تحميل وتجهيز ملف الكتاب للمعاينة الآمنة...</div>
            <div v-else-if="previewError" class="preview-error-msg">{{ previewError }}</div>
            <iframe v-else-if="pdfUrl" :src="pdfUrl" class="preview-iframe"></iframe>
          </div>

          <!-- قرارات المعاينة الجانبية -->
          <div class="preview-actions">
            <button class="btn-gold" @click="approveBook(selectedBook.id)">
              <Check :size="16" /> موافقة ونشر الكتاب للجميع
            </button>
            <button class="btn-outline" @click="rejectBook(selectedBook.id)">
              <X :size="16" /> رفض وحذف طلب الرفع
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.admin-panel {
  padding: 40px 20px 80px;
  direction: rtl;
  text-align: right;
  color: #333;
}

.panel-header {
  margin-bottom: 30px;
}

.panel-header h1 {
  font-family: 'Amiri', serif;
  font-size: 2.2rem;
  color: #0A3D36;
  margin-bottom: 8px;
}

.panel-header p {
  color: #6d6d66;
  font-size: 0.95rem;
}

.state-msg {
  background: #fff;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(10, 61, 54, 0.05);
  font-family: 'Cairo', sans-serif;
  color: #6d6d66;
}

.state-msg.error {
  color: #b3261e;
  border: 1px solid #f9dedc;
  background: #fdf4f3;
}

.state-msg.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.panel-layout {
  display: grid;
  grid-template-columns: 3fr 2fr;
  gap: 26px;
  align-items: start;
}

@media(max-width: 1024px) {
  .panel-layout {
    grid-template-columns: 1fr;
  }
}

.books-list-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(10, 61, 54, 0.06);
  overflow-x: auto;
  border: 1px solid #ece7d8;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  font-family: 'Cairo', sans-serif;
  font-size: 0.88rem;
}

.admin-table th {
  background: #0A3D36;
  color: #fff;
  padding: 14px 16px;
  text-align: right;
  font-weight: 700;
}

.admin-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f0edf0;
  vertical-align: middle;
}

.admin-table tbody tr:hover {
  background-color: #f7f9f8;
}

.admin-table tbody tr.active {
  background-color: #f0f7f5;
  border-right: 4px solid #0A3D36;
}

.book-title-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.author-name {
  display: block;
  font-size: 0.76rem;
  color: #6d6d66;
  margin-top: 2px;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: 0.2s;
}

.btn-action.preview {
  background: #f1f5f9;
  color: #475569;
}

.btn-action.preview:hover {
  background: #e2e8f0;
}

.btn-action.approve {
  background: #d1fae5;
  color: #065f46;
}

.btn-action.approve:hover {
  background: #a7f3d0;
}

.btn-action.reject {
  background: #fee2e2;
  color: #991b1b;
}

.btn-action.reject:hover {
  background: #fecaca;
}

/* لوحة المعاينة */
.preview-panel {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #ece7d8;
  box-shadow: 0 10px 30px rgba(10, 61, 54, 0.06);
  overflow: hidden;
  position: sticky;
  top: 96px;
}

.preview-header {
  background: #0A3D36;
  color: #fff;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.preview-header h3 {
  font-family: 'Amiri', serif;
  font-size: 1.25rem;
  margin: 0;
  color: #F6C90E;
}

.btn-close {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: 0.2s;
}

.btn-close:hover {
  color: #fff;
}

.preview-body {
  padding: 20px;
}

.book-details-short {
  font-family: 'Cairo', sans-serif;
  font-size: 0.85rem;
  line-height: 1.6;
  margin-bottom: 16px;
  color: #4a4a44;
  background: #f9f9f7;
  padding: 14px;
  border-radius: 8px;
  border-right: 3px solid #D4AF37;
}

.book-details-short p {
  margin-bottom: 8px;
}

.book-details-short p:last-child {
  margin-bottom: 0;
}

.pdf-preview-box {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  height: 380px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  margin-bottom: 20px;
}

.preview-loading-msg, .preview-error-msg {
  font-family: 'Cairo', sans-serif;
  font-size: 0.85rem;
  color: #64748b;
  text-align: center;
  padding: 20px;
}

.preview-error-msg {
  color: #b3261e;
}

.preview-iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.preview-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-gold {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: linear-gradient(145deg, var(--accent-bright), var(--accent));
  color: var(--primary-dark);
  font-family: 'Cairo', sans-serif;
  font-weight: 800;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(212,175,55,0.25);
  transition: transform 0.2s, box-shadow 0.2s;
  border: none;
}

.btn-gold:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(212,175,55,0.35);
}

.btn-outline {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: transparent;
  color: #991b1b;
  border: 1.5px solid rgba(153, 27, 27, 0.3);
  font-family: 'Cairo', sans-serif;
  font-weight: 700;
  padding: 11px;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: 0.2s;
}

.btn-outline:hover {
  background: #fee2e2;
  border-color: #991b1b;
}
</style>
