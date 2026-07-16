<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\BookDownload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * متحكم الكتب – يتعامل مع جميع عمليات الكتب عبر API.
 */
class BookController extends Controller
{
    // =========================================================================
    // Public Endpoints (لا تحتاج مصادقة)
    // =========================================================================

    /**
     * GET /api/books
     *
     * جلب قائمة الكتب المعتمدة فقط مع إمكانية الفلترة والبحث.
     *
     * Query Params:
     *  - search        : البحث في العنوان والوصف
     *  - category_id   : تصفية بالقسم
     *  - author_id     : تصفية بالمؤلف
     *  - language      : تصفية باللغة
     *  - sort_by       : حقل الترتيب (created_at|views_count|downloads_count|average_rating)
     *  - sort_dir      : اتجاه الترتيب (asc|desc)
     *  - per_page      : عدد النتائج في الصفحة (افتراضي: 15)
     */
   public function index(Request $request): AnonymousResourceCollection
{
    $query = Book::query()
        ->approved()
        ->with(['author', 'category'])
        ->withCount('reviews');

    // ── 1. البحث النصي ─────────────────────────────────────────────────────
    if ($search = $request->string('search')->trim()->value()) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    // ── 2. الفلاتر المتعددة المتوافقة مع الـ Vue ───────────────────────────
    
    // فلترة اللغات (تستقبل مصفوفة مفصولة بفاصلة مثل ar,en)
    if ($request->filled('languages')) {
        $languages = explode(',', $request->string('languages')->value());
        $query->whereIn('language', $languages);
    }

    // فلترة التصنيفات (تستقبل مصفوفة مفصولة بفاصلة مثل 23,24)
    if ($request->filled('categories')) {
        $categories = explode(',', $request->string('categories')->value());
        $query->whereIn('category_id', $categories);
    }

    // فلترة حجم الملف (مطابقة مع lt2, 2to5, 5to10, gt10 المرسلة من الـ Vue)
    // ملاحظة: تأكد من تعديل الحسابات بناءً على كيفية تخزين الحجم لديك (مثال هنا بالكيلوبايت أو الميجابايت)
    if ($request->filled('file_size')) {
        $fileSize = $request->string('file_size')->value();
        $query->where(function ($q) use ($fileSize) {
            // نقوم بقص واستخراج الرقم العشري من حقل file_size المخزن نصياً (مثل "3.5 MB")
            $rawSize = "CAST(SUBSTRING_INDEX(file_size, ' ', 1) AS DECIMAL(10,2))";
            
            switch ($fileSize) {
                case 'lt2': // أقل من 2MB أو أي ملف بالكيلوبايت KB
                    $q->where('file_size', 'like', '%KB%')
                      ->orWhere(function($sub) use ($rawSize) {
                          $sub->where('file_size', 'like', '%MB%')
                              ->whereRaw("$rawSize < 2.0");
                      });
                    break;
                case '2to5': // بين 2 و 5 MB
                    $q->where('file_size', 'like', '%MB%')
                      ->whereRaw("$rawSize >= 2.0 AND $rawSize <= 5.0");
                    break;
                case '5to10': // بين 5 و 10 MB
                    $q->where('file_size', 'like', '%MB%')
                      ->whereRaw("$rawSize > 5.0 AND $rawSize <= 10.0");
                    break;
                case 'gt10': // أكبر من 10 MB
                    $q->where('file_size', 'like', '%MB%')
                      ->whereRaw("$rawSize > 10.0");
                    break;
            }
        });
    }

    // فلترة التقييم (الحد الأدنى للتقييم)
    if ($request->filled('min_rating')) {
        $minRating = $request->integer('min_rating');
        // تفترض أن لديك حقل rating أو متوسط التقييمات في جدول الكتب
        $query->where('rating', '>=', $minRating); 
    }

    // ── 3. الترتيب المتوافق مع الـ Vue ─────────────────────────────────────
    $sort = $request->string('sort')->value(); // استقبال 'latest', 'rating', 'downloads', 'alpha'
    
    $sortBy = 'created_at';
    $sortDir = 'desc';

    switch ($sort) {
       case 'rating':
            // الترتيب بناءً على عدد التقييمات/المراجعات المتاحة للكتاب
            $sortBy = 'reviews_count'; 
            $sortDir = 'desc';
            break;
        case 'downloads':
            $sortBy = 'downloads_count';
            $sortDir = 'desc';
            break;
        case 'alpha':
            $sortBy = 'title';
            $sortDir = 'asc';
            break;
        case 'latest':
        default:
            $sortBy = 'created_at';
            $sortDir = 'desc';
            break;
    }

    $query->orderBy($sortBy, $sortDir);

    // ── 4. الترقيم والتجهيز النهائي ────────────────────────────────────────
    $perPage = min($request->integer('per_page', 12), 100);

    return BookResource::collection($query->paginate($perPage));
}

    /**
     * GET /api/books/{slug}
     *
     * جلب تفاصيل كتاب واحد بكامل علاقاته (مؤلف، قسم، تقييمات).
     */
    public function show(string $slug): BookResource
    {
        $book = Book::query()
            // ->approved()
            ->where('slug', $slug)
            ->with([
                'author',
                'category.parent',
                'uploader',
                'reviews.user',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->firstOrFail();

        return new BookResource($book);
    }

    /**
     * POST /api/books/{slug}/view
     *
     * زيادة عداد المشاهدات للكتاب.
     * يُستخدم عبر Session لمنع الاحتساب المتكرر لنفس الزيارة.
     */
    public function incrementView($slug)
{
    // البحث عن الكتاب باستخدام الـ slug
    $book = \App\Models\Book::where('slug', $slug)->first();

    if (!$book) {
        return response()->json(['message' => 'الكتاب غير موجود'], 404);
    }

    // زيادة العداد وحفظ التغيير
    $book->increment('views_count');

    return response()->json([
        'success' => true,
        'views_count' => $book->views_count
    ]);
}

    // =========================================================================
    // Protected Endpoints (تحتاج مصادقة)
    // =========================================================================

    /**
     * POST /api/books
     *
     * رفع كتاب جديد. مقيّد بمستخدمين لديهم صلاحية canUploadBooks.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated): JsonResponse {

            // ── رفع ملف PDF ───────────────────────────────────────────────────
            $pdfPath = $request->file('pdf_file')
                ->store('books/pdfs', 'public');

            // ── رفع صورة الغلاف (إن وُجدت) ───────────────────────────────────
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $coverPath = $request->file('cover_image')
                    ->store('books/covers', 'public');
            }

            // ── توليد Slug فريد ───────────────────────────────────────────────
            $slug = $this->generateUniqueSlug($validated['title']);

            // ── حساب حجم الملف بصيغة مقروءة ─────────────────────────────────
            $fileSize = $this->formatFileSize(
                Storage::disk('public')->size($pdfPath)
            );

            $book = Book::create([
                'title'       => $validated['title'],
                'slug'        => $slug,
                'description' => $validated['description'] ?? null,
                'language'    => $validated['language'] ?? 'ar',
                'pages_count' => $validated['pages_count'] ?? null,
                'author_id'   => $validated['author_id'],
                'category_id' => $validated['category_id'],
                'uploader_id' => $request->user()->id,
                'pdf_path'    => $pdfPath,
                'cover_image' => $coverPath,
                'file_size'   => $fileSize,
                'status'      => BookStatus::Pending,
            ]);

            return response()->json([
                'message' => 'تم رفع الكتاب بنجاح وهو الآن قيد المراجعة.',
                'data'    => new BookResource($book->load(['author', 'category'])),
            ], 201);
        });
    }

    /**
     * POST /api/books/{slug}/download
     *
     * تسجيل عملية تحميل وزيادة العداد مع منع التكرار للمستخدم نفسه.
     * يعيد مسار ملف PDF للتحميل.
     */
    public function download(string $slug, Request $request): JsonResponse
    {
        $book = Book::approved()->where('slug', $slug)->firstOrFail();

        $user   = $request->user();
        $userId = $user?->id;
        $ip     = $request->ip();

        // ── التحقق مما إذا كان المستخدم المسجّل قد حمّل الكتاب من قبل ──────
        $alreadyDownloaded = $userId && BookDownload::query()
            ->where('book_id', $book->id)
            ->where('user_id', $userId)
            ->exists();

        if (! $alreadyDownloaded) {
            // استخدام updateOrCreate للأمان في الحالات المتزامنة
            BookDownload::updateOrCreate(
                ['book_id' => $book->id, 'user_id' => $userId],
                ['ip_address' => $ip, 'downloaded_at' => now()]
            );

            $book->increment('downloads_count');
        }

        return response()->json([
            'download_url'    => asset('storage/' . $book->pdf_path),
            'downloads_count' => $book->fresh()->downloads_count,
        ]);
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * توليد slug فريد بإضافة رقم تسلسلي عند التكرار.
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug     = $baseSlug;
        $counter  = 1;

        while (Book::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * تنسيق حجم الملف إلى صيغة مقروءة (KB, MB, GB).
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return round($bytes / 1_073_741_824, 2) . ' GB';
        }
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }
        return round($bytes / 1_024, 2) . ' KB';
    }
}
