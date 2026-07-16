<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_categories';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * القسم الرئيسي (Parent Category).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * الأقسام الفرعية (Children Categories).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * الكتب التابعة لهذا القسم.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * جلب الأقسام الرئيسية فقط (التي ليس لها parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
