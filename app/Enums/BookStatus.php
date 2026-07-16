<?php

namespace App\Enums;

enum BookStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * الحصول على التسمية العربية لكل حالة.
     */
    public function label(): string
    {
        return match($this) {
            self::Pending  => 'قيد المراجعة',
            self::Approved => 'معتمد',
            self::Rejected => 'مرفوض',
        };
    }
}
