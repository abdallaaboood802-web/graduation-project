<script setup>
import { reactive, ref, computed } from 'vue'
import { UploadCloud } from 'lucide-vue-next'
import { booksApi } from '../services/api'

const currentStep = ref(1) // 1: معلومات الكتاب, 2: الملف والغلاف, 3: المراجعة والنشر

const form = reactive({
  title: '',
  author: '',
  language: 'العربية',
  category: 'رواية',
  description: '',
  bookFile: null,
  coverFile: null
})

const dragOver = ref(false)
const submitting = ref(false)
const submitError = ref(null)
const submitSuccess = ref(false)

const coverFileName = computed(() => form.coverFile?.name ?? '')

function goToStep(step) {
  currentStep.value = step
}

function onBookFileChange(e) {
  form.bookFile = e.target.files[0] ?? null
}
function onCoverFileChange(e) {
  form.coverFile = e.target.files[0] ?? null
}
function onDrop(e) {
  dragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file) form.bookFile = file
}

async function submitBook() {
  submitting.value = true
  submitError.value = null
  try {
    const fd = new FormData()
    
    // 1. البيانات النصية الأساسية
    fd.append('title', form.title)
    fd.append('description', form.description)

    // 2. تحويل اللغة إلى صيغة الرمز الثنائي (ISO-639-1) المتوقع في لاراڤيل
    const langMap = {
      'العربية': 'ar',
      'الإنجليزية': 'en',
      'الفرنسية': 'fr'
    }
    fd.append('language', langMap[form.language] || 'ar')

    // 3. مطابقة المعرفات الرقمية (تأكد من إرسال الـ IDs الصحيحة إذا كانت تتوفر لديك ديناميكياً)
    // مؤقتاً إذا كانت الحقول نصية في واجهتك، سنمرر ID افتراضي (مثال: 1) لكي يتقبله التحقق الصارم في لاراڤيل
    fd.append('author_id', form.authorId ?? 1) 
    fd.append('category_id', form.categoryId ?? 1) 

    // 4. مطابقة مسميات الملفات مع الـ Backend تماماً
    if (form.bookFile) {
      fd.append('pdf_file', form.bookFile) // 👈 تم التعديل من 'file' إلى 'pdf_file' لإنهاء مشكلة الملف المطلوب
    }
    if (form.coverFile) {
      fd.append('cover_image', form.coverFile) // 👈 تم التعديل من 'cover' إلى 'cover_image'
    }

    await booksApi.upload(fd)
    submitSuccess.value = true
  } catch (e) {
    submitError.value = e.response?.data?.message || 'حدث خطأ أثناء رفع الكتاب. حاول مرة أخرى.'
    console.error(e)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section id="upload">
    <div class="upload-head container">
      <h1>ارفع كتابك إلى المكتبة</h1>
      <p>ثلاث خطوات بسيطة وينضم كتابك إلى إشراق بوك</p>
    </div>

    <div class="stepper">
      <div class="step" :class="{ done: currentStep > 1, active: currentStep === 1 }">
        <div class="circle">{{ currentStep > 1 ? '✓' : '١' }}</div>
        <span class="label ui-font">معلومات الكتاب</span>
      </div>
      <div class="step-line" :class="{ done: currentStep > 1 }"></div>
      <div class="step" :class="{ done: currentStep > 2, active: currentStep === 2 }">
        <div class="circle">{{ currentStep > 2 ? '✓' : '٢' }}</div>
        <span class="label ui-font">الملف والغلاف</span>
      </div>
      <div class="step-line" :class="{ done: currentStep > 2 }"></div>
      <div class="step" :class="{ active: currentStep === 3 }">
        <div class="circle">٣</div>
        <span class="label ui-font">المراجعة والنشر</span>
      </div>
    </div>

    <!-- Step 1: معلومات الكتاب -->
    <div v-if="currentStep === 1" class="upload-card">
      <h3>معلومات الكتاب</h3>
      <p>أدخل التفاصيل الأساسية عن كتابك</p>

      <div class="field">
        <label>عنوان الكتاب</label>
        <input type="text" v-model="form.title" placeholder="اكتب عنوان الكتاب" />
      </div>
      <div class="field">
        <label>اسم المؤلف</label>
        <input type="text" v-model="form.author" placeholder="اكتب اسم المؤلف" />
      </div>
      <div class="field-row">
        <div class="field">
          <label>اللغة</label>
          <select v-model="form.language">
            <option>العربية</option><option>الإنجليزية</option><option>الفرنسية</option>
          </select>
        </div>
        <div class="field">
          <label>التصنيف</label>
          <select v-model="form.category">
            <option>رواية</option><option>شعر</option><option>تاريخ</option><option>فلسفة</option>
          </select>
        </div>
      </div>
      <div class="field">
        <label>نبذة مختصرة عن الكتاب</label>
        <textarea rows="3" v-model="form.description" placeholder="اكتب وصفاً موجزاً يساعد القراء على التعرف على كتابك..."></textarea>
      </div>

      <div class="step-actions">
        <span></span>
        <button class="btn-gold" :disabled="!form.title || !form.author" @click="goToStep(2)">المتابعة للخطوة التالية</button>
      </div>
    </div>

    <!-- Step 2: الملف والغلاف -->
    <div v-else-if="currentStep === 2" class="upload-card">
      <h3>رفع الملف والغلاف</h3>
      <p>أضف نسخة الكتاب وصورة الغلاف — نوصي بصيغة PDF أو EPUB</p>

      <label
        class="dropzone"
        :class="{ 'drag-over': dragOver }"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="onDrop"
      >
        <UploadCloud :size="38" color="#D4AF37" />
        <p v-if="!form.bookFile">اسحب ملف الكتاب هنا أو اضغط للاختيار</p>
        <p v-else>{{ form.bookFile.name }}</p>
        <span>PDF, EPUB — حتى 50MB</span>
        <input type="file" accept=".pdf,.epub" style="display:none" @change="onBookFileChange" />
      </label>

      <div class="field">
        <label>صورة الغلاف</label>
        <label style="cursor:pointer;">
          <input type="text" :value="coverFileName" placeholder="اختر صورة الغلاف (JPG أو PNG)" readonly />
          <input type="file" accept="image/*" style="display:none" @change="onCoverFileChange" />
        </label>
      </div>

      <div class="step-actions">
        <button class="btn-ghost" @click="goToStep(1)">← الخطوة السابقة</button>
        <button class="btn-gold" :disabled="!form.bookFile" @click="goToStep(3)">المتابعة للمراجعة</button>
      </div>
    </div>

    <!-- Step 3: المراجعة والنشر -->
    <div v-else class="upload-card">
      <h3>المراجعة والنشر</h3>
      <p>تأكد من صحة البيانات قبل النشر</p>

      <div v-if="submitSuccess" class="form-success">تم رفع كتابك بنجاح! سيظهر في المكتبة بعد المراجعة.</div>

      <template v-else>
        <div class="field"><label>العنوان</label><input type="text" :value="form.title" readonly /></div>
        <div class="field"><label>المؤلف</label><input type="text" :value="form.author" readonly /></div>
        <div class="field-row">
          <div class="field"><label>اللغة</label><input type="text" :value="form.language" readonly /></div>
          <div class="field"><label>التصنيف</label><input type="text" :value="form.category" readonly /></div>
        </div>
        <div class="field"><label>الملف</label><input type="text" :value="form.bookFile?.name ?? '—'" readonly /></div>

        <p v-if="submitError" class="form-error">{{ submitError }}</p>

        <div class="step-actions">
          <button class="btn-ghost" :disabled="submitting" @click="goToStep(2)">← الخطوة السابقة</button>
          <button class="btn-gold" :disabled="submitting" @click="submitBook">
            {{ submitting ? 'جارٍ النشر...' : 'نشر الكتاب' }}
          </button>
        </div>
      </template>
    </div>
  </section>
</template>
