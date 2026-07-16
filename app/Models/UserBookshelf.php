<?php

namespace App\Models;

use App\Enums\BookshelfStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookshelf extends Model
{
    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_user_bookshelves';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
    ];

    /**
     * تحويل أنواع الحقول.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookshelfStatus::class,
        ];
    }

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * المستخدم صاحب المكتبة.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الكتاب الموجود في المكتبة.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
