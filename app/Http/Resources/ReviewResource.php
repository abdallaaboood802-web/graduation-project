<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource لتنسيق بيانات التقييم.
 */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'rating'      => $this->rating,
            'review_text' => $this->review_text,
            'created_at'  => $this->created_at?->toIso8601String(),
            'user'        => new UserResource($this->whenLoaded('user')),
        ];
    }
}
