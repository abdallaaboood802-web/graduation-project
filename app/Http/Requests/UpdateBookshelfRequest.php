<?php

namespace App\Http\Requests;

use App\Enums\BookshelfStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request للتحقق من صحة بيانات تحديث حالة كتاب في المكتبة الشخصية.
 */
class UpdateBookshelfRequest extends FormRequest
{
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
            'status' => ['required', new Enum(BookshelfStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'حالة القراءة مطلوبة.',
            'status'          => 'حالة القراءة غير صحيحة. القيم المتاحة: want_to_read, reading, read.',
        ];
    }
}
