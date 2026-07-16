<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending  = 'pending';
    case Reviewed = 'reviewed';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    /**
     * الحصول على التسمية العربية لكل حالة.
     */
    public function label(): string
    {
        return match($this) {
            self::Pending   => 'قيد المراجعة',
            self::Reviewed  => 'قيد الدراسة',
            self::Resolved  => 'تم الحل',
            self::Dismissed => 'تم الرفض',
        };
    }
}
