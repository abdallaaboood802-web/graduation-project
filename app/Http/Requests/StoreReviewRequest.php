<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request للتحقق من صحة بيانات إضافة تقييم على كتاب.
 */
class StoreReviewRequest extends FormRequest
{
    /**
     * يجب أن يكون المستخدم مسجلاً للدخول.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating'      => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:3000', 'min:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'التقييم مطلوب.',
            'rating.integer'  => 'يجب أن يكون التقييم رقماً صحيحاً.',
            'rating.min'      => 'الحد الأدنى للتقييم هو نجمة واحدة.',
            'rating.max'      => 'الحد الأقصى للتقييم هو 5 نجوم.',
            'review_text.min' => 'نص المراجعة يجب أن يحتوي على 10 أحرف على الأقل.',
            'review_text.max' => 'نص المراجعة لا يتجاوز 3000 حرف.',
        ];
    }
}
