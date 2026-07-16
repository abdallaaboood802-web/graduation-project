<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource لتنسيق البيانات العامة للمستخدم (لا تشمل البيانات الحساسة).
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'bio'        => $this->bio,
            'avatar_url' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'role'       => $this->role?->value ?? 'reader',
        ];
    }
}
