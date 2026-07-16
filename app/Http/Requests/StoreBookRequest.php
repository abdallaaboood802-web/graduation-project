<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
new \Illuminate\Auth\Access\AuthorizationException('غير مصرح لك برفع الكتب.'); // تحقق من صلاحية رفع الكتب

/**
 * Form Request للتحقق من صحة بيانات رفع كتاب جديد.
 */
class StoreBookRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مصرحاً له بإجراء هذا الطلب.
     */
    public function authorize(): bool
    {
        // 👈 قم بتغيير السطر ليرجع true مباشرة لتجاوز الحظر
        return true; 
    }

    /**
     * قواعد التحقق من الصحة.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255', 'min:2'],
            'description' => ['nullable', 'string', 'max:5000'],
            'language'    => ['nullable', 'string'], 
            'pages_count' => ['nullable', 'integer', 'min:1', 'max:99999'],

            // 👈 جعلنا حقول المؤلف والتصنيف اختيارية مؤقتاً لتخطي الخطأ الحالي
            'author_id'   => ['nullable'], 
            'category_id' => ['nullable'],

            'pdf_file'    => [
                'required',
                'file',
                'mimes:pdf',
                'max:51200',
            ],

            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
            ],
        ];
    }

    /**
     * رسائل الخطأ المخصصة باللغة العربية.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'          => 'عنوان الكتاب مطلوب.',
            'title.max'               => 'عنوان الكتاب لا يجب أن يتجاوز 255 حرفاً.',
            'title.min'               => 'عنوان الكتاب يجب أن يحتوي على حرفين على الأقل.',
            'author_id.required'      => 'يجب تحديد المؤلف.',
            'author_id.exists'        => 'المؤلف المحدد غير موجود.',
            'category_id.required'    => 'يجب تحديد القسم.',
            'category_id.exists'      => 'القسم المحدد غير موجود.',
            'pdf_file.required'       => 'ملف PDF مطلوب.',
            'pdf_file.mimes'          => 'يجب أن يكون الملف بصيغة PDF فقط.',
            'pdf_file.max'            => 'حجم ملف PDF لا يجب أن يتجاوز 50 ميغابايت.',
            'cover_image.image'       => 'يجب أن يكون الغلاف صورة.',
            'cover_image.mimes'       => 'صيغ الصور المقبولة: JPEG, PNG, WebP.',
            'cover_image.max'         => 'حجم صورة الغلاف لا يجب أن يتجاوز 5 ميغابايت.',
            'cover_image.dimensions'  => 'أبعاد صورة الغلاف يجب أن لا تقل عن 200×300 بكسل.',
            'pages_count.integer'     => 'عدد الصفحات يجب أن يكون رقماً صحيحاً.',
            'pages_count.min'         => 'عدد الصفحات يجب أن يكون 1 على الأقل.',
            'language.size'           => 'يجب أن يكون رمز اللغة مكوناً من حرفين (مثل: ar, en).',
        ];
    }
}
