<?php

use App\Models\Book;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController; // 👈 تم إضافة الاستدعاء هنا لتجنب خطأ الـ Undefined Class
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── مسار آمن ذكي لقراءة واستعراض ملفات الـ PDF وتجاوز الـ 403 ──────────────────
Route::get('/books/{book}/download', function (Book $book) {
    // 1. التحقق من حالة الكتاب (فقط المعتمد متاح للعامة، إلا إذا كان الرافع هو نفسه المستخدم أو أدمن)
    $user = auth('sanctum')->user();

    if ($book->status->value !== 'approved') {
        $isUploader = $user && $user->id === $book->uploader_id;
        $isAdmin = $user && in_array($user->role, ['admin', 'moderator']);

        if (!$isUploader && !$isAdmin) {
            return response()->json(['message' => 'هذا الكتاب غير متاح للتحميل حالياً.'], Response::HTTP_FORBIDDEN);
        }
    }

    // 2. تعقيم المسار واستخراج اسم الملف فقط لحماية النظام من الـ Path Traversal
    $fileName = basename($book->pdf_path);
    $filePath = 'books/pdfs/' . $fileName;

    if (!Storage::disk('public')->exists($filePath)) {
        return response()->json(['message' => 'الملف غير موجود في خوادمنا.'], Response::HTTP_NOT_FOUND);
    }

    // زيادة عداد التحميلات بأمان
    $book->increment('downloads_count');

    // 3. إرجاع الملف بأمان
    return response()->download(storage_path('app/public/' . $filePath), $book->title . '.pdf');
})->middleware('throttle:10,1'); // حماية المسار بـ Rate Limiting (10 تحميلات في الدقيقة كحد أقصى)


// =============================================================================
// مسارات الإصدار الأول للـ API (v1)
// =============================================================================
// =============================================================================
// مسارات الإصدار الأول للـ API (v1) - بعد التعديل والنقل
// =============================================================================
Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── مسار تسجيل الدخول الصحيح ──
    Route::post('auth/login', [AuthController::class, 'login']);

    // ── المسارات العامة (متاحة للجميع بدون صلاحيات) ───────────────────────────
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{slug}', [BookController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('stats', [StatsController::class, 'index']);
    Route::match(['get', 'post', 'patch'], 'books/{slug}/view', [BookController::class, 'incrementView']);

    // ── مسارات الأعضاء المسجلين (أي مستخدم يمتلك حساب وتحت حماية Sanctum) ─────
    Route::middleware('auth:sanctum')->group(function () {
        // رفع كتاب جديد للمستخدمين المسجلين
        Route::post('books', [BookController::class, 'store']);

        // 👈 نقلنا مسار البروفايل وتحديثه إلى هنا ليعمل لكل الأعضاء!
        Route::get('user/profile', [AuthController::class, 'profile']);
        Route::put('user/profile', [AuthController::class, 'updateProfile']);
    });

    // -------------------------------------------------------------------------
    // مسارات الإدارة فقط - مقيدة بـ admin و moderator حصراً
    // -------------------------------------------------------------------------
    Route::middleware(['auth:sanctum', 'role:admin,moderator'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            // إدارة الكتب بانتظار المراجعة
            Route::prefix('books')->name('books.')->group(function () {
                Route::get('pending',          [AdminController::class, 'pendingBooks'])->name('pending');
                Route::patch('{book}/approve', [AdminController::class, 'approveBook'])->name('approve');
                Route::patch('{book}/reject',  [AdminController::class, 'rejectBook'])->name('reject');
            });

            // بلاغات حقوق النشر
            Route::get('copyright-reports', [AdminController::class, 'copyrightReports'])
                ->name('copyright-reports');
        });
});

// =============================================================================
// Fallback - مسار احتياطي لأي endpoint غير معرفة
// =============================================================================
Route::fallback(function () {
    return response()->json([
        'message' => 'المسار المطلوب غير موجود.',
        'status' => 404,
    ], 404);
});
