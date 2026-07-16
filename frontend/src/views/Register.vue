<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { BookOpen } from 'lucide-vue-next'
import { useAuth } from '../services/api' // استدعاء الـ API الذي جهزناه

const router = useRouter()
const { register } = useAuth()

// حقول مطابقة تماماً لقواعد تحقق Laravel
const form = reactive({ 
  name: '', 
  username: '', 
  email: '', 
  password: '', 
  password_confirmation: '' 
})

const error = ref(null)
const loading = ref(false)

async function submit() {
  if (form.password !== form.password_confirmation) {
    error.value = 'كلمتا المرور غير متطابقتين.'
    return
  }
  
  loading.value = true
  error.value = null
  
  try {
    await register(form)
    router.push('/')
  } catch (e) {
    if (e.response?.data?.errors) {
      // جلب أول رسالة خطأ من مصفوفة الأخطاء التي يرجعها Laravel
      const validationErrors = e.response.data.errors
      const firstErrorKey = Object.keys(validationErrors)[0]
      error.value = validationErrors[firstErrorKey][0]
    } else {
      error.value = e.response?.data?.message || 'تعذّر إنشاء الحساب. تحقق من البيانات.'
    }
  } finally {
    loading.value = false
  }
} // <--- تم إضافة قوس الإغلاق هنا بنجاح!
</script>

<template>
  <section class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo">
        إشراق <span class="gold">بوك</span>
        <BookOpen :size="26" color="#F6C90E" />
      </div>
      <p class="auth-sub">أنشئ حسابك لتشارك كتبك واقتباساتك وتقييماتك مع مجتمع القراء</p>

      <div class="field">
        <label>الاسم الكامل</label>
        <input type="text" v-model="form.name" placeholder="اسمك الكامل" />
      </div>
      
      <div class="field">
        <label>اسم المستخدم</label>
        <input type="text" v-model="form.username" placeholder="username" />
      </div>
      
      <div class="field">
        <label>البريد الإلكتروني</label>
        <input type="email" v-model="form.email" placeholder="example@mail.com" />
      </div>
      
      <div class="field">
        <label>كلمة المرور</label>
        <input type="password" v-model="form.password" placeholder="••••••••" />
      </div>
      
      <div class="field">
        <label>تأكيد كلمة المرور</label>
        <input type="password" v-model="form.password_confirmation" placeholder="••••••••" />
      </div>

      <p v-if="error" class="form-error" style="text-align:center; color:#ff4d4d; margin: 15px 0; font-size: 0.9rem;">
        {{ error }}
      </p>

      <button class="btn-gold" style="width:100%;" :disabled="loading" @click="submit">
        {{ loading ? 'جارٍ الإنشاء...' : 'إنشاء الحساب' }}
      </button>

      <p class="auth-switch">
        لديك حساب بالفعل؟
        <RouterLink to="/login">سجّل دخولك</RouterLink>
      </p>
    </div>
  </section>
</template>

<style scoped>
.auth-wrap {
  min-height: calc(100vh - 76px - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 70px 20px;
  background: var(--bg);
}

.auth-card {
  width: 100%;
  max-width: 480px;
  background: var(--primary);
  border-radius: 20px;
  box-shadow: var(--shadow-lg);
  padding: 44px 40px;
  color: var(--white);
  text-align: center;
}

.auth-logo {
  font-family: 'Amiri', serif;
  font-weight: 700;
  font-size: 1.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: var(--white);
  margin-bottom: 14px;
}

.auth-logo .gold {
  color: var(--accent-bright);
}

.auth-sub {
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.92rem;
  margin-bottom: 30px;
  line-height: 1.8;
}

.auth-card .field {
  text-align: right;
  margin-bottom: 15px;
}

.auth-card .field label {
  color: rgba(255, 255, 255, 0.85);
  display: block;
  margin-bottom: 5px;
  font-size: 0.9rem;
}

.auth-card .field input {
  width: 100%;
  padding: 12px 15px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.06);
  color: var(--white);
  box-sizing: border-box;
}

.auth-card .field input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.auth-card .field input:focus {
  border-color: var(--accent-bright);
  background: rgba(255, 255, 255, 0.1);
  outline: none;
}

.auth-switch {
  margin-top: 22px;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.75);
}

.auth-switch a {
  color: var(--accent-bright);
  font-weight: 700;
  text-decoration: none;
}

.auth-switch a:hover {
  text-decoration: underline;
}

.btn-gold {
  background: #F6C90E;
  color: #1a1a1a;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-gold:hover:not(:disabled) {
  background: #e0b50d;
}

.btn-gold:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>