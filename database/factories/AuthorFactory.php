<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Author>
 */
class AuthorFactory extends Factory
{
    /**
     * أسماء مؤلفين عرب مشهورين لبيانات واقعية.
     */
    private static array $arabicAuthors = [
        'نجيب محفوظ',    'غادة السمان',   'أحمد خالد توفيق',
        'إحسان عبد القدوس', 'يوسف إدريس', 'محمود درويش',
        'طه حسين',       'العقاد',        'جبران خليل جبران',
        'نزار قباني',    'علي أحمد باكثير', 'توفيق الحكيم',
    ];

    public function definition(): array
    {
        return [
            'name'    => fake()->unique()->randomElement(self::$arabicAuthors),
            'bio'     => fake()->paragraphs(2, true),
            'photo'   => null,
            'user_id' => null, // يُربط بمستخدم في الـ Seeder
        ];
    }

    /**
     * مؤلف مرتبط بمستخدم.
     */
    public function withUser(int $userId): static
    {
        return $this->state(fn () => ['user_id' => $userId]);
    }
}
