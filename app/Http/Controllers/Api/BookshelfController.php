<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookshelfRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\UserBookshelf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * متحكم المكتبة الشخصية – إدارة قائمة قراءة المستخدم.
 */
class BookshelfController extends Controller
{
    /**
     * GET /api/me/bookshelf
     *
     * جلب قائمة كتب المكتبة الشخصية للمستخدم الحالي مع إمكانية الفلترة بالحالة.
     *
     * Query Params:
     *  - status: want_to_read | reading | read
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = $user->shelfBooks()
            ->with(['author', 'category'])
            ->withCount('reviews');

        if ($status = $request->string('status')->value()) {
            $query->wherePivot('status', $status);
        }

        $books = $query->paginate(20);

        return BookResource::collection($books);
    }

    /**
     * POST /api/me/bookshelf/{slug}
     *
     * إضافة كتاب إلى المكتبة الشخصية أو تحديث حالته.
     * يُضاف الكتاب إذا لم يكن موجوداً، أو يُحدَّث إذا كان موجوداً.
     */
    public function update(UpdateBookshelfRequest $request, string $slug): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $shelf = UserBookshelf::updateOrCreate(
            [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ],
            [
                'status' => $request->validated('status'),
            ]
        );

        $statusLabel = $shelf->status->label();
        $wasCreated  = $shelf->wasRecentlyCreated;

        return response()->json([
            'message' => $wasCreated
                ? "تمت إضافة الكتاب إلى مكتبتك بحالة: {$statusLabel}"
                : "تم تحديث حالة الكتاب إلى: {$statusLabel}",
            'data'    => [
                'book_id'   => $book->id,
                'book_slug' => $book->slug,
                'status'    => $shelf->status->value,
                'label'     => $statusLabel,
                'updated_at' => $shelf->updated_at?->toIso8601String(),
            ],
        ], $wasCreated ? 201 : 200);
    }

    /**
     * DELETE /api/me/bookshelf/{slug}
     *
     * إزالة كتاب من المكتبة الشخصية.
     */
    public function destroy(string $slug, Request $request): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        $deleted = UserBookshelf::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'الكتاب غير موجود في مكتبتك.',
            ], 404);
        }

        return response()->json([
            'message' => 'تمت إزالة الكتاب من مكتبتك بنجاح.',
        ]);
    }
}
