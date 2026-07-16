<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_reviews';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'review_text',
    ];

    /**
     * تحويل أنواع الحقول.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * المستخدم صاحب التقييم.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الكتاب الذي يخص التقييم.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
