<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_authors';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'bio',
        'photo',
        'user_id',
    ];

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * حساب المستخدم المرتبط بهذا المؤلف (إن وُجد).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الكتب التي ألّفها هذا المؤلف.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
