<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * متحكم الاقتباسات – إضافة وعرض اقتباسات الكتب.
 */
class QuoteController extends Controller
{
    /**
     * GET /api/v1/books/{slug}/quotes
     *
     * جلب اقتباسات كتاب معين.
     */
    public function index(string $slug): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();

        $quotes = $book->quotes()
            ->with('user:id,username,avatar')
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $quotes]);
    }

    /**
     * POST /api/v1/books/{slug}/quotes
     *
     * إضافة اقتباس جديد من كتاب.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'quote_text' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'quote_text.required' => 'نص الاقتباس مطلوب.',
            'quote_text.min'      => 'الاقتباس يجب أن يحتوي على 10 أحرف على الأقل.',
            'quote_text.max'      => 'الاقتباس لا يتجاوز 2000 حرف.',
        ]);

        $quote = Quote::create([
            'user_id'    => $request->user()->id,
            'book_id'    => $book->id,
            'quote_text' => $validated['quote_text'],
        ]);

        return response()->json([
            'message' => 'تم إضافة الاقتباس بنجاح.',
            'data'    => $quote->load('user:id,username,avatar'),
        ], 201);
    }

    /**
     * DELETE /api/v1/quotes/{quote}
     *
     * حذف اقتباس (المالك أو المشرف).
     */
    public function destroy(Quote $quote, Request $request): JsonResponse
    {
        $user = $request->user();

        if ($quote->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'غير مصرح لك بحذف هذا الاقتباس.',
            ], 403);
        }

        $quote->delete();

        return response()->json([
            'message' => 'تم حذف الاقتباس بنجاح.',
        ]);
    }
}
