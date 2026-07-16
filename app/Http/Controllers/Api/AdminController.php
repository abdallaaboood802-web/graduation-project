<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\CopyrightReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * متحكم لوحة الإدارة – مقيّد بـ admin و moderator.
 */
class AdminController extends Controller
{
    /**
     * GET /api/admin/books/pending
     *
     * جلب الكتب قيد المراجعة.
     */
    public function pendingBooks(): AnonymousResourceCollection
    {
        $books = Book::query()
            ->where('status', \App\Enums\BookStatus::Pending)
            ->with(['author', 'category', 'uploader'])
            ->latest()
            ->paginate(20);

        return BookResource::collection($books);
    }

    /**
     * PATCH /api/admin/books/{book}/approve
     *
     * اعتماد كتاب.
     */
    public function approveBook(Book $book): JsonResponse
    {
        $book->update(['status' => \App\Enums\BookStatus::Approved]);

        return response()->json([
            'message' => "تم اعتماد كتاب \"{$book->title}\" بنجاح.",
            'data'    => new BookResource($book->fresh()),
        ]);
    }

    /**
     * PATCH /api/admin/books/{book}/reject
     *
     * رفض كتاب مع ذكر السبب.
     */
    public function rejectBook(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $book->update(['status' => \App\Enums\BookStatus::Rejected]);

        return response()->json([
            'message' => "تم رفض كتاب \"{$book->title}\".",
            'data'    => new BookResource($book->fresh()),
        ]);
    }

    /**
     * GET /api/admin/copyright-reports
     *
     * جلب بلاغات حقوق النشر.
     */
    public function copyrightReports(): JsonResponse
    {
        $reports = CopyrightReport::query()
            ->with('book')
            ->where('status', \App\Enums\ReportStatus::Pending)
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $reports]);
    }
}
