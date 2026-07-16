# إشراق بوك — واجهة Vue.js

نسخة Vue 3 (Vite + Vue Router) من تصميم "إشراق بوك"، جاهزة للربط مع الـ Laravel backend.

## التشغيل

```bash
npm install
cp .env.example .env   # عدّل VITE_API_BASE_URL ليطابق عنوان الـ Laravel API لديك
npm run dev
```

## البنية

```
src/
  assets/styles/main.css     كل متغيرات التصميم (الألوان، الخطوط) والأنماط
  components/
    Navbar.vue                شريط التنقل العلوي
    AppFooter.vue              الفوتر
    BookCard.vue                بطاقة كتاب واحدة (تُستخدم في الرئيسية والتصفح)
  views/
    Home.vue                   الصفحة الرئيسية (هيرو + كتب مقترحة + تصنيفات)
    Books.vue                  صفحة تصفح الكتب مع الفلاتر
    Upload.vue                 معالج رفع كتاب من 3 خطوات
  services/api.js              طبقة axios + دوال booksApi جاهزة للربط بالـ backend
  router/index.js              مسارات Vue Router (/, /books, /upload)
```

## الربط مع Laravel backend

كل نداءات الـ API موجودة في مكان واحد: `src/services/api.js`. عدّل المسارات هناك لتطابق
`routes/api.php` لديك بالضبط. المسارات المفترضة حالياً:

| الفعل | المسار | الاستخدام |
|---|---|---|
| GET | `/api/books?featured=1` | الكتب المقترحة في الرئيسية |
| GET | `/api/books` | تصفح الكتب مع فلاتر (`languages`, `file_size`, `min_rating`, `sort`) |
| GET | `/api/books/{id}` | تفاصيل كتاب |
| GET | `/api/categories` | التصنيفات وعدد الكتب في كل تصنيف |
| GET | `/api/stats` | إحصائيات شريط الأرقام في الرئيسية |
| POST | `/api/books` | رفع كتاب جديد (multipart/form-data) |

حقول الكتاب المتوقعة من الـ backend (متوافقة مع الأسماء التي استخدمتها سابقاً في `AdminController`):
`id, title, author, cover_url, rating, file_type, file_size, download_url, is_top, is_new`.

إذا كان الـ backend يستخدم Laravel Sanctum، التوكن يُقرأ تلقائياً من `localStorage.auth_token`
ويُرفق في كل طلب عبر interceptor في `api.js`.

## ملاحظة CORS

فعّل CORS في Laravel (`config/cors.php`) للسماح بطلبات من عنوان الـ Vite dev server
(افتراضياً `http://localhost:5173`).
