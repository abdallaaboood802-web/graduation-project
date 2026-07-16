<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * متحكم التقييمات – إضافة وعرض تقييمات الكتب.
 */
class ReviewController extends Controller
{
    /**
     * GET /api/books/{slug}/reviews
     *
     * جلب جميع تقييمات كتاب معين مع بيانات المستخدمين.
     */
    public function index(string $slug): AnonymousResourceCollection
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();

        $reviews = Review::query()
            ->where('book_id', $book->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return ReviewResource::collection($reviews);
    }

    /**
     * POST /api/books/{slug}/reviews
     *
     * إضافة تقييم جديد على كتاب.
     *
     * القيود:
     *  - يجب أن يكون المستخدم مسجّلاً للدخول.
     *  - لا يُسمح بتقييم نفس الكتاب مرتين.
     *  - لا يستطيع رافع الكتاب تقييم كتابه.
     */
    public function store(StoreReviewRequest $request, string $slug): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        // ── منع تقييم الكتاب مرتين ──────────────────────────────────────────
        $alreadyReviewed = Review::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'لقد قمت بتقييم هذا الكتاب مسبقاً.',
            ], 409); // 409 Conflict
        }

        // ── منع رافع الكتاب من تقييم كتابه ─────────────────────────────────
        if ($book->uploader_id === $user->id) {
            return response()->json([
                'message' => 'لا يمكنك تقييم كتاب قمت برفعه.',
            ], 403);
        }

        $review = Review::create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'rating'      => $request->validated('rating'),
            'review_text' => $request->validated('review_text'),
        ]);

        return response()->json([
            'message' => 'تم إضافة تقييمك بنجاح.',
            'data'    => new ReviewResource($review->load('user')),
        ], 201);
    }

    /**
     * DELETE /api/reviews/{review}
     *
     * حذف تقييم (المستخدم صاحب التقييم أو المشرف فقط).
     */
    public function destroy(Review $review): JsonResponse
    {
        $user = request()->user();

        if ($review->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'غير مصرح لك بحذف هذا التقييم.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'تم حذف التقييم بنجاح.',
        ]);
    }
}
