<?php

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/books/{book}/download', function (Book $book) {
    // 1. التحقق من حالة الكتاب (فقط المعتمد متاح للعامة، إلا إذا كان الرافع هو نفسه المستخدم أو أدمن)
    $user = auth('sanctum')->user();

    if ($book->status !== 'approved') {
        $isUploader = $user && $user->id === $book->uploader_id;
        $isAdmin = $user && in_array($user->role, ['admin', 'moderator']);

        if (!$isUploader && !$isAdmin) {
            return response()->json(['message' => 'هذا الكتاب غير متاح للتحميل حالياً.'], 403); // 403 Forbidden
        }
    }

    // 2. تعقيم المسار واستخراج اسم الملف فقط لحماية النظام من الـ Path Traversal
    $fileName = basename($book->pdf_path);
    $filePath = 'books/pdfs/' . $fileName;

    if (!Storage::disk('public')->exists($filePath)) {
        return response()->json(['message' => 'الملف غير موجود في خوادمنا.'], 404); // 404 Not Found
    }

    // زيادة عداد التحميلات بأمان
    $book->increment('downloads_count');

    // 3. إرجاع الملف بأمان من مجلد التخزين العام
    return response()->download(storage_path('app/public/' . $filePath), $book->title . '.pdf');
})->middleware('throttle:10,1'); // حماية المسار بـ Rate Limiting (10 تحميلات في الدقيقة كحد أقصى)