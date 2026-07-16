<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Form Request للتحقق من صحة بيانات تسجيل مستخدم جديد.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // متاح للجميع
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'unique:table_users,username',
                'regex:/^[a-zA-Z0-9_]+$/', // حروف وأرقام و underscore فقط
            ],
            'email'    => [
                'required',
                'email',
                'max:255',
                'unique:table_users,email',
            ],
            'password' => [
                'required',
                'confirmed', // يتحقق من وجود password_confirmation
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
            'bio'      => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required'  => 'اسم المستخدم مطلوب.',
            'username.min'       => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل.',
            'username.max'       => 'اسم المستخدم لا يتجاوز 30 حرفاً.',
            'username.unique'    => 'اسم المستخدم هذا مأخوذ، اختر اسماً آخر.',
            'username.regex'     => 'اسم المستخدم يقبل الحروف والأرقام و (_) فقط.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.email'        => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'       => 'هذا البريد الإلكتروني مسجّل مسبقاً.',
            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'password.min'       => 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.',
            'bio.max'            => 'النبذة الشخصية لا تتجاوز 500 حرف.',
        ];
    }
}
