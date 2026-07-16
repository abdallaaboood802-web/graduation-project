<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopyrightReport extends Model
{
    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_copyright_reports';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً.
     *
     * @var list<string>
     */
    protected $fillable = [
        'book_id',
        'reporter_name',
        'reporter_email',
        'reason',
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
            'status' => ReportStatus::class,
        ];
    }

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * الكتاب المُبلَّغ عنه.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
