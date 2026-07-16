<?php

namespace Database\Factories;

use App\Enums\BookStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * عناوين كتب عربية واقعية.
     */
    private static array $titles = [
        'أولاد حارتنا',
        'اللص والكلاب',
        'زقاق المدق',
        'ثلاثية القاهرة',
        'موسم الهجرة إلى الشمال',
        'عمارة يعقوبيان',
        'في قلبي أنثى عبرية',
        'أنا وأنت من نحن',
        'مزرعة الحيوانات',
        'ألف شمس مشرقة',
        'قواعد العشق الأربعون',
        'مئة عام من العزلة',
        'الخيميائي',
        'جريمة وعقاب',
        'شيفرة دافنشي',
        'ملك وذباب',
        'الحرب والسلام',
        'البؤساء',
        'رواية العطر',
        'أيام الشوق',
    ];

    public function definition(): array
    {
        $title = fake()->unique()->randomElement(self::$titles);

        return [
            'title'           => $title,
            'slug'            => Str::slug($title . '-' . fake()->randomNumber(4)),
            'description'     => fake()->paragraphs(3, true),
            'cover_image'     => null,
            'pdf_path'        => 'books/pdfs/sample.pdf',
            'pages_count'     => fake()->numberBetween(100, 800),
            'file_size'       => fake()->randomElement(['1.2 MB', '2.5 MB', '4.1 MB', '8.3 MB']),
            'language'        => fake()->randomElement(['ar', 'en']),
            'category_id'     => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'author_id'       => Author::inRandomOrder()->value('id')   ?? Author::factory(),
            'uploader_id'     => User::inRandomOrder()->value('id')     ?? User::factory(),
            'status'          => BookStatus::Approved,
            'views_count'     => fake()->numberBetween(0, 5000),
            'downloads_count' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * كتاب قيد المراجعة.
     */
    public function pending(): static
    {
        return $this->state(fn () => ['status' => BookStatus::Pending]);
    }

    /**
     * كتاب معتمد.
     */
    public function approved(): static
    {
        return $this->state(fn () => ['status' => BookStatus::Approved]);
    }

    /**
     * كتاب مرفوض.
     */
    public function rejected(): static
    {
        return $this->state(fn () => ['status' => BookStatus::Rejected]);
    }
}
