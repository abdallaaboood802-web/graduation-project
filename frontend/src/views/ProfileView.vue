<script setup>
import { ref, onMounted } from 'vue'
import { User, Mail, Calendar, BookOpen, Edit2, Check, Loader } from 'lucide-vue-next'
import axios from 'axios'
import BookCard from '../components/BookCard.vue'

// تعديل القيمة الابتدائية لتفادي مشكلة الـ undefined أثناء التحميل الأول
const user = ref({
    name: '',
    email: '',
    role: 'user',
    created_at: '',
    bio: ''
})
const myBooks = ref([])
const isLoading = ref(true)
const isSaving = ref(false)
const isEditing = ref(false)

// نموذج التعديل
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    bio: ''
})

const message = ref({ text: '', type: '' })

// 1. دالة جلب البيانات (تستخدم GET ورابط لارافيل كاملاً المنفذ 8000)
const fetchProfileData = async () => {
    try {
        isLoading.value = true
        const token = localStorage.getItem('auth_token')

        // 👈 أضف هذا السطر لمراقبة التوكن في الكونسول
        console.log("التوكن المسترجع من المتصفح هو:", token)

        const response = await axios.get('http://localhost:8000/api/v1/user/profile', {
            headers: { Authorization: `Bearer ${token}` }
        })

        console.log("البيانات القادمة من السيرفر بالكامل:", response.data)

        if (response.data) {
            // قراءة الكائن سواء كان راجعاً داخل user أو data أو بشكل مباشر
            const rawUser = response.data.user || response.data.data || response.data;

            user.value = {
                id: rawUser.id,
                name: rawUser.name || rawUser.username || 'مستخدم',
                email: rawUser.email || '',
                role: rawUser.role || 'user',
                created_at: rawUser.created_at || 'غير محدد',
                bio: rawUser.bio || ''
            }

            myBooks.value = response.data.books || response.data.data?.books || []

            // تعبئة النموذج للتعديل تلقائياً
            form.value.name = user.value.name
            form.value.email = user.value.email
            form.value.bio = user.value.bio
        }
    } catch (error) {
        console.error('فشل جلب بيانات البروفايل:', error)
        message.value = { text: 'فشل جلب البيانات من السيرفر.', type: 'error' }
    } finally {
        isLoading.value = false
    }
}

// 2. دالة تحديث وحفظ البيانات (تستخدم PUT ورابط لارافيل كاملاً المنفذ 8000)
const handleUpdateProfile = async () => {
    isSaving.value = true
    message.value = { text: '', type: '' }

    try {
        const token = localStorage.getItem('auth_token')

        const response = await axios.put('http://localhost:8000/api/v1/user/profile', form.value, {
            headers: { Authorization: `Bearer ${token}` }
        })

        if (response.data && (response.data.user || response.data.data)) {
            const rawUser = response.data.user || response.data.data;
            user.value = {
                ...user.value,
                name: rawUser.name || rawUser.username || user.value.name,
                email: rawUser.email || user.value.email,
                bio: rawUser.bio || user.value.bio
            }
            isEditing.value = false
            message.value = { text: 'تم تحديث بياناتك الشخصية بنجاح!', type: 'success' }

            // تفريغ حقول كلمة المرور بعد النجاح
            form.value.password = ''
            form.value.password_confirmation = ''
        }
    } catch (error) {
        console.error('فشل التحديث:', error)
        message.value = {
            text: error.response?.data?.message || 'حدث خطأ أثناء التحديث، يرجى المحاولة لاحقاً.',
            type: 'error'
        }
    } finally {
        isSaving.value = false
    }
}

onMounted(() => {
    fetchProfileData()
})
</script>

<template>
    <div class="profile-container" dir="rtl">
        <!-- شاشة التحميل اللطيفة -->
        <div v-if="isLoading" class="loading-state">
            <Loader class="spinner" :size="40" />
            <p>جاري تحميل ملفك الشخصي...</p>
        </div>

        <div v-else class="profile-layout">
            <!-- 1. بطاقة معلومات المستخدم الجانبية -->
            <aside class="profile-sidebar">
                <div class="avatar-section">
                    <div class="avatar-placeholder">
                        {{ (user.name ? user.name.charAt(0).toUpperCase() : 'U') }}
                    </div>
                    <h3>{{ user.name || 'مستخدم' }}</h3>
                    <span class="role-badge">{{ user.role === 'admin' ? 'مدير عام' : 'عضو مسجل' }}</span>
                </div>

                <hr class="separator" />

                <div class="info-list">
                    <div class="info-item">
                        <Mail :size="18" />
                        <div>
                            <span class="label">البريد الإلكتروني</span>
                            <span class="val">{{ user.email }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <Calendar :size="18" />
                        <div>
                            <span class="label">تاريخ الانضمام</span>
                            <span class="val">{{ user.created_at || 'غير محدد' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <BookOpen :size="18" />
                        <div>
                            <span class="label">الكتب المرفوعة</span>
                            <span class="val">{{ myBooks.length }} كتب</span>
                        </div>
                    </div>
                </div>

                <button @click="isEditing = !isEditing" class="btn-toggle-edit">
                    <Edit2 :size="16" />
                    {{ isEditing ? 'إلغاء التعديل' : 'تعديل بياناتي' }}
                </button>
            </aside>

            <!-- 2. الجزء الرئيسي للتعديل أو استعراض الكتب -->
            <main class="profile-main">
                <!-- قسم تعديل البيانات -->
                <section v-if="isEditing" class="card edit-card">
                    <h2>تحديث الحساب الشخصي</h2>

                    <div v-if="message.text" :class="['alert', message.type]">
                        {{ message.text }}
                    </div>

                    <form @submit.prevent="handleUpdateProfile" class="profile-form">
                        <div class="form-group">
                            <label>الاسم الكامل (اسم المستخدم)</label>
                            <input v-model="form.name" type="text" required placeholder="أدخل اسمك الكامل" />
                        </div>

                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input v-model="form.email" type="email" required placeholder="example@mail.com" />
                        </div>

                        <div class="form-group">
                            <label>نبذة تعريفية (Bio)</label>
                            <textarea v-model="form.bio" placeholder="اكتب نبذة قصيرة عنك وعن اهتماماتك القرائية..."
                                rows="3" class="bio-textarea"></textarea>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>كلمة المرور الجديدة (اختياري)</label>
                                <input v-model="form.password" type="password" placeholder="••••••••" />
                            </div>
                            <div class="form-group">
                                <label>تأكيد كلمة المرور</label>
                                <input v-model="form.password_confirmation" type="password" placeholder="••••••••" />
                            </div>
                        </div>

                        <button type="submit" :disabled="isSaving" class="btn-save">
                            <span v-if="isSaving">جاري الحفظ...</span>
                            <template v-else>
                                <Check :size="18" />
                                <span>حفظ التغييرات</span>
                            </template>
                        </button>
                    </form>
                </section>

                <!-- قسم عرض كتب المستخدم المرفوعة -->
                <section class="uploaded-books-section">
                    <div class="section-header">
                        <h2>مكتبتي الخاصة <span class="counter">({{ myBooks.length }})</span></h2>
                        <p>الكتب التي قمت برفعها ومشاركتها مع مجتمع إشراق بوك</p>
                    </div>

                    <div v-if="myBooks.length === 0" class="empty-books">
                        <BookOpen :size="48" class="muted-icon" />
                        <p>لم تقم برفع أي كتب بعد. هل تود مشاركة كتابك الأول؟</p>
                        <router-link to="/upload" class="btn-upload-now">رفع كتاب جديد</router-link>
                    </div>

                    <div v-else class="books-grid">
                        <BookCard v-for="(book, index) in myBooks" :key="book.id" :book="book"
                            :fallbackClass="`c${(index % 4) + 1}`" />
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* التنسيقات العامة والجمالية للملف الشخصي */
.profile-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 0;
    color: #0a3a30;
}

.spinner {
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    100% {
        transform: rotate(360deg);
    }
}

.profile-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 30px;
    align-items: start;
}

@media (max-width: 900px) {
    .profile-layout {
        grid-template-columns: 1fr;
    }
}

/* السايد بار الجانبي */
.profile-sidebar {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
}

.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.avatar-placeholder {
    width: 90px;
    height: 90px;
    background: #0a3a30;
    color: #f7c948;
    font-size: 36px;
    font-weight: bold;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border: 4px solid #f7c948;
}

.avatar-section h3 {
    margin: 0 0 8px 0;
    color: #1e293b;
    font-size: 20px;
}

.role-badge {
    background: #f1f5f9;
    color: #475569;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.separator {
    border: 0;
    height: 1px;
    background: #f1f5f9;
    margin: 24px 0;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 24px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0a3a30;
}

.info-item div {
    display: flex;
    flex-direction: column;
}

.info-item .label {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 2px;
}

.info-item .val {
    font-size: 14px;
    color: #334155;
    font-weight: 600;
}

.btn-toggle-edit {
    width: 100%;
    padding: 12px;
    border: 1px solid #0a3a30;
    color: #0a3a30;
    background: transparent;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: 0.3s;
}

.btn-toggle-edit:hover {
    background: #0a3a30;
    color: white;
}

/* الماين سكشن */
.profile-main {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
}

.edit-card h2 {
    margin: 0 0 20px 0;
    color: #0a3a30;
    font-size: 22px;
}

/* النماذج */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

label {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
}

input,
.bio-textarea {
    padding: 12px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
    transition: 0.3s;
}

.bio-textarea {
    resize: vertical;
    font-family: inherit;
}

input:focus,
.bio-textarea:focus {
    border-color: #0a3a30;
    box-shadow: 0 0 0 3px rgba(10, 58, 48, 0.1);
}

.btn-save {
    background: #0a3a30;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: 0.3s;
    margin-top: 10px;
}

.btn-save:hover {
    background: #072922;
}

/* التنبيهات */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
}

.alert.success {
    background: #dcfce7;
    color: #15803d;
}

.alert.error {
    background: #fee2e2;
    color: #b91c1c;
}

/* قسم عرض الكتب */
.uploaded-books-section .section-header {
    margin-bottom: 24px;
}

.uploaded-books-section h2 {
    color: #0a3a30;
    font-size: 24px;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.uploaded-books-section .counter {
    background: #e2e8f0;
    font-size: 14px;
    padding: 2px 10px;
    border-radius: 20px;
    color: #475569;
}

.uploaded-books-section p {
    color: #64748b;
    margin: 0;
    font-size: 15px;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.empty-books {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: #f8fafc;
    border-radius: 16px;
    border: 2px dashed #cbd5e1;
    text-align: center;
}

.muted-icon {
    color: #94a3b8;
    margin-bottom: 16px;
}

.empty-books p {
    color: #64748b;
    margin-bottom: 20px;
}

.btn-upload-now {
    background: #f7c948;
    color: #0a3a30;
    text-decoration: none;
    font-weight: bold;
    padding: 12px 24px;
    border-radius: 8px;
    transition: 0.3s;
}

.btn-upload-now:hover {
    background: #e2b430;
}
</style>