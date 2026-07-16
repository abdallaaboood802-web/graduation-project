import axios from 'axios'

const api = axios.create({
  // دمج /v1 كبادئة أساسية للمشروع لتسهيل كتابة الطلبات
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// إرفاق توكن Sanctum تلقائياً إن وُجد في التخزين المحلي
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api

// =============================================================================
// ① خدمات المصادقة (Auth API)
// =============================================================================
export const authApi = {
  register: (userData) => api.post('/auth/register', userData),
  login: (credentials) => api.post('/auth/login', credentials),
  logout: () => api.post('/auth/logout'),
  getMe: () => api.get('/me')
}

export function useAuth() {
  async function register(userData) {
    try {
      const response = await authApi.register(userData)
      const token = response.data.token || response.data.access_token
      const user = response.data.user

      if (token) {
        localStorage.setItem('auth_token', token)
        localStorage.setItem('user', JSON.stringify(user))
      }
      return response.data
    } catch (error) {
      throw error
    }
  }
  return { register }
}

// =============================================================================
// ② خدمات الكتب (Books API)
// =============================================================================
export const booksApi = {
  list: (params) => api.get('/books', { params }),
  show: (slug) => api.get(`/books/${slug}`),
  incrementView: (slug) => api.post(`/books/${slug}/view`),
  upload: (formData, onUploadProgress) =>
    api.post('/books', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress
    }),

  // ✅ تعديل دالة التحميل البرمجية لتطلب المسار الخارجي الصحيح مع التوكن
  download(bookId) {
    const token = localStorage.getItem('auth_token')
    return axios.get(`http://localhost:8000/books/${bookId}/download`, {
      responseType: 'blob',
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    });
  },

  reportCopyright: (slug, reportData) => api.post(`/books/${slug}/report-copyright`, reportData),
  featured: () => api.get('/books?featured=1&limit=4'),
  categories: () => api.get('/categories'),
  stats: () => api.get('/stats')
};

// =============================================================================
// ③ التقييمات والاقتباسات (Reviews & Quotes API)
// =============================================================================
export const reviewsApi = {
  list: (slug, params) => api.get(`/books/${slug}/reviews`, { params }),
  store: (slug, reviewData) => api.post(`/books/${slug}/reviews`, reviewData),
  destroy: (reviewId) => api.delete(`/reviews/${reviewId}`)
}

export const quotesApi = {
  list: (slug, params) => api.get(`/books/${slug}/quotes`, { params }),
  store: (slug, quoteData) => api.post(`/books/${slug}/quotes`, quoteData),
  destroy: (quoteId) => api.delete(`/quotes/${quoteId}`)
}

// =============================================================================
// ④ خدمات المستخدمين والمكتبة الشخصية (Users & Bookshelf API)
// =============================================================================
export const usersApi = {
  getProfile: (username) => api.get(`/users/${username}`),
  getBooks: (username) => api.get(`/users/${username}/books`),
  getQuotes: (username) => api.get(`/users/${username}/quotes`),
  toggleFollow: (username) => api.post(`/users/${username}/follow`),
  getBookshelf: () => api.get('/me/bookshelf'),
  updateBookshelf: (slug, statusData) => api.put(`/me/bookshelf/${slug}`, statusData),
  removeFromBookshelf: (slug) => api.delete(`/me/bookshelf/${slug}`)
}

// =============================================================================
// ⑤ المؤلفون والأقسام (Authors & Categories API)
// =============================================================================
export const authorsApi = {
  list: (params) => api.get('/authors', { params }),
  show: (authorIdOrSlug) => api.get(`/authors/${authorIdOrSlug}`)
}

export const categoriesApi = {
  list: () => api.get('/categories'),
  show: (slug) => api.get(`/categories/${slug}`)
}

// =============================================================================
// ⑥ لوحة الإدارة (Admin API)
// =============================================================================
export const adminApi = {
  getPendingBooks: () => api.get('/admin/books/pending'),
  approveBook: (bookId) => api.patch(`/admin/books/${bookId}/approve`),
  rejectBook: (bookId) => api.patch(`/admin/books/${bookId}/reject`),
  getCopyrightReports: () => api.get('/admin/copyright-reports')
}