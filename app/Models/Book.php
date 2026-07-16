<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_books';
    protected $casts = [
        'status' => \App\Enums\BookStatus::class, // تأكد من كتابة مسار الـ Enum الصحيح لديك هنا
    ];

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'pdf_path',
        'pages_count',
        'file_size',
        'language',
        'category_id',
        'author_id',
        'uploader_id',
        'status',
        'views_count',
        'downloads_count',
    ];

    /**
     * تحويل أنواع الحقول (Casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'          => BookStatus::class,
            'pages_count'     => 'integer',
            'views_count'     => 'integer',
            'downloads_count' => 'integer',
        ];
    }

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * القسم التابع له الكتاب.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * مؤلف الكتاب.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * المستخدم الذي رفع الكتاب.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * التقييمات المرتبطة بالكتاب.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'book_id');
    }

    /**
     * الاقتباسات المرتبطة بالكتاب.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'book_id');
    }

    /**
     * بلاغات حقوق النشر المرتبطة بالكتاب.
     */
    public function copyrightReports(): HasMany
    {
        return $this->hasMany(CopyrightReport::class, 'book_id');
    }

    /**
     * سجل من قام بتحميل الكتاب (لمنع تكرار العداد).
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(BookDownload::class, 'book_id');
    }

    /**
     * المستخدمون الذين أضافوا الكتاب لمكتباتهم.
     */
    public function shelfUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'table_user_bookshelves', 'book_id', 'user_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * جلب الكتب المعتمدة فقط.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', BookStatus::Approved);
    }

    // =========================================================================
    // Computed Attributes
    // =========================================================================

    /**
     * حساب متوسط تقييم الكتاب.
     */
    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->avg('rating') ?? 0.0;
    }
}
