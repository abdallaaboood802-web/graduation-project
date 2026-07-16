<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource لتنسيق بيانات المؤلف.
 */
class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'bio'       => $this->bio,
            'photo_url' => $this->photo ? asset('storage/' . $this->photo) : null,
        ];
    }
}
