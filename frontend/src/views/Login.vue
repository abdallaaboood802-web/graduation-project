<template>
  <section class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo">
        إشراق <span class="gold">بوك</span> 📖
      </div>
      <p class="auth-sub">سجّل دخولك لتتمكن من رفع الكتب وإضافتها لمكتبتك الشخصية</p>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="field">
          <label for="email">البريد الإلكتروني</label>
          <input 
            type="email" 
            id="email"
            v-model="email" 
            placeholder="example@mail.com" 
            required
          />
        </div>
        
        <div class="field">
          <label for="password">كلمة المرور</label>
          <input 
            type="password" 
            id="password"
            v-model="password" 
            placeholder="••••••••" 
            required
          />
        </div>

        <p v-if="errorMessage" class="form-error">{{ errorMessage }}</p>

        <button class="btn-gold" :disabled="isLoading">
          {{ isLoading ? 'جارٍ الدخول...' : 'تسجيل الدخول' }}
        </button>
      </form>

      <p class="auth-switch">
        ليس لديك حساب؟
        <RouterLink to="/register">أنشئ حساباً جديداً</RouterLink>
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '../services/api' // الربط مع الـ API الذي جهزناه[cite: 5]

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isLoading = ref(false)
const router = useRouter()

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await authApi.login({
      email: email.value,
      password: password.value
    })
    
    // تخزين التوكن في التخزين المحلي للـ Browser[cite: 5]
    localStorage.setItem('auth_token', response.data.token)
    
    // التوجيه للرئيسية بعد نجاح العملية
    router.push('/')
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.auth-wrap {
  min-height: calc(100vh - 76px - 200px);
  display: flex; 
  align-items: center; 
  justify-content: center;
  padding: 70px 20px;
  background: var(--bg, #091d1a);
  direction: rtl;
}

.auth-card {
  width: 100%; 
  max-width: 480px;
  background: var(--primary, #0d2a23);
  border-radius: 20px;
  box-shadow: var(--shadow-lg, 0 10px 25px rgba(0,0,0,0.3));
  padding: 44px 40px;
  color: var(--white, #ffffff);
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
  color: var(--white, #ffffff); 
  margin-bottom: 14px;
}

.auth-logo .gold {
  color: var(--accent-bright, #ffcc00);
}

.auth-sub {
  color: rgba(255, 255, 255, 0.75); 
  font-size: 0.92rem; 
  margin-bottom: 30px; 
  line-height: 1.8;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.auth-card .field {
  text-align: right;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.auth-card .field label {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.9rem;
}

.auth-card .field input {
  background: rgba(255, 255, 255, 0.06); 
  border: 1px solid rgba(255, 255, 255, 0.2); 
  color: var(--white, #ffffff);
  padding: 12px;
  border-radius: 8px;
  outline: none;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.auth-card .field input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.auth-card .field input:focus {
  border-color: var(--accent-bright, #ffcc00); 
  background: rgba(255, 255, 255, 0.1);
}

.form-error {
  color: #e74c3c;
  font-size: 0.85rem;
  margin: 0;
  text-align: center;
}

.btn-gold {
  background: var(--accent-bright, #ffcc00);
  color: #0d2a23;
  border: none;
  padding: 14px;
  font-weight: bold;
  font-size: 1rem;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  transition: background 0.3s;
}

.btn-gold:hover:not(:disabled) {
  background: #e6b800;
}

.btn-gold:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-switch {
  margin-top: 22px; 
  font-size: 0.9rem; 
  color: rgba(255, 255, 255, 0.75);
}

.auth-switch a {
  color: var(--accent-bright, #ffcc00); 
  font-weight: 700;
  text-decoration: none;
}
</style>