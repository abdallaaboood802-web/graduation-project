<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // جلب الإحصائيات الحقيقية من قاعدة البيانات
            return response()->json([
                'status' => 'success',
                'data' => [
                    'books_count'      => Book::count(),
                    'categories_count' => Category::count(),
                    // حساب المستخدمين بناءً على دورهم (أو إجمالي المستخدمين إذا لم يكن لديك نظام أدوار مفصل بعد)
                    'readers_count'    => User::count(), 
                    'authors_count'    => User::where('role', 'author')->count() ?: 1, // قيمة افتراضية لتفادي الصفر إن لم يوجد
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}