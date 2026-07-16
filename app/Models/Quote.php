<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_quotes';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'quote_text',
    ];

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * المستخدم صاحب الاقتباس.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الكتاب المأخوذ منه الاقتباس.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
