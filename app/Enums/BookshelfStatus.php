<?php

namespace App\Enums;

enum BookshelfStatus: string
{
    case WantToRead = 'want_to_read';
    case Reading    = 'reading';
    case Read       = 'read';

    /**
     * الحصول على التسمية العربية لكل حالة.
     */
    public function label(): string
    {
        return match($this) {
            self::WantToRead => 'أريد قراءته',
            self::Reading    => 'أقرأه حالياً',
            self::Read       => 'قرأته',
        };
    }
}
