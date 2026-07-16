<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\CopyrightReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * متحكم بلاغات حقوق النشر – يتيح للجمهور تقديم بلاغ.
 */
class CopyrightReportController extends Controller
{
    /**
     * POST /api/v1/books/{slug}/report-copyright
     *
     * تقديم بلاغ حقوق نشر لكتاب معين.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'reporter_name'  => ['required', 'string', 'max:100'],
            'reporter_email' => ['required', 'email', 'max:255'],
            'reason'         => ['required', 'string', 'min:20', 'max:2000'],
        ], [
            'reporter_name.required'  => 'اسم المُبلِّغ مطلوب.',
            'reporter_email.required' => 'البريد الإلكتروني مطلوب.',
            'reporter_email.email'    => 'البريد الإلكتروني غير صالح.',
            'reason.required'         => 'سبب البلاغ مطلوب.',
            'reason.min'              => 'يجب أن يحتوي السبب على 20 حرفاً على الأقل.',
        ]);

        CopyrightReport::create([
            'book_id'        => $book->id,
            'reporter_name'  => $validated['reporter_name'],
            'reporter_email' => $validated['reporter_email'],
            'reason'         => $validated['reason'],
            'status'         => \App\Enums\ReportStatus::Pending,
        ]);

        return response()->json([
            'message' => 'تم استلام بلاغك بنجاح وسيتم مراجعته من قِبل فريقنا.',
        ], 201);
    }
}
