<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    private static array $quotes = [
        'الكتاب خير جليس في الأنام.',
        'اقرأ باسم ربك الذي خلق.',
        'الجهل موت الأحياء، والعلم حياة الأموات.',
        'في الكتب وراثة الحكمة.',
        'الأفكار الكبيرة لا تعرف الحدود.',
        'من لم يقرأ لم يعش.',
        'الحياة قصيرة والفن طويل.',
        'الكلمة التي تنبثق من القلب تصل إلى القلب.',
        'الحرية نار لا تُطفأ.',
        'الإنسان حيث يضع عقله لا حيث يضع قدمه.',
    ];

    public function definition(): array
    {
        return [
            'user_id'    => User::inRandomOrder()->value('id') ?? User::factory(),
            'book_id'    => Book::inRandomOrder()->value('id') ?? Book::factory(),
            'quote_text' => fake()->randomElement(self::$quotes),
        ];
    }
}
