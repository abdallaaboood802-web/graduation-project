<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource لتنسيق بيانات الكتاب في الاستجابات.
 */
class BookResource extends JsonResource
{
    /**
     * تحويل المورد إلى مصفوفة.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'cover_url' => $this->cover ? url('storage/' . $this->cover) : null,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'cover_image_url' => $this->cover_image
                                    ? asset('storage/' . $this->cover_image)
                                    : null,
            'pdf_url'         => $this->pdf_path 
                                    ? url('view-pdf/' . basename($this->pdf_path)) 
                                    : null,
            'pages_count'     => $this->pages_count,
            'file_size'       => $this->file_size,
            'language'        => $this->language,
            
            // 🛡️ فحص آمن لحالة الكتاب (سواء كانت Enum، نص عادي، أو قيمة فارغة)
            'status'          => $this->status instanceof \BackedEnum 
                                    ? $this->status->value 
                                    : ($this->status ?? 'pending'),
                                    
            'status_label'    => method_exists($this->status, 'label') 
                                    ? $this->status->label() 
                                    : 'قيد المراجعة',
                                    
            'views_count'     => $this->views_count,
            'downloads_count' => $this->downloads_count,
            'average_rating'  => $this->average_rating,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),

            // العلاقات (محمّلة شرطياً)
            'author'          => new AuthorResource($this->whenLoaded('author')),
            'category'        => new CategoryResource($this->whenLoaded('category')),
            'uploader'        => new UserResource($this->whenLoaded('uploader')),
            'reviews'         => ReviewResource::collection($this->whenLoaded('reviews')),
            'reviews_count'   => $this->whenCounted('reviews'),
            'pivot'           => $this->when($this->pivot, function () {
                return [
                    'status' => $this->pivot->status,
                ];
            }),
        ];
    }
}