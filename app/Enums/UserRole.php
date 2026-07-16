<?php

namespace App\Enums;

enum UserRole: string
{
    case Reader    = 'reader';
    case Author    = 'author';
    case Moderator = 'moderator';
    case Admin     = 'admin';

    /**
     * التحقق إذا كان الدور يملك صلاحية رفع الكتب.
     */
    public function canUploadBooks(): bool
    {
        return in_array($this, [self::Author, self::Moderator, self::Admin]);
    }

    /**
     * التحقق إذا كان الدور يملك صلاحيات الإدارة.
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::Moderator, self::Admin]);
    }
}
