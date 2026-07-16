<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * أسماء أقسام عربية واقعية لمنصة كتب.
     */
    private static array $categories = [
        'روايات'        => 'كتب روائية متنوعة بين الخيال والواقع',
        'تاريخ'         => 'كتب تاريخية تروي أحداث الأمم والحضارات',
        'علوم'          => 'كتب علمية في الفيزياء والكيمياء والأحياء',
        'فلسفة'         => 'كتب في الفكر الفلسفي والمنطق',
        'تطوير الذات'   => 'كتب تساعدك على النمو الشخصي والمهني',
        'اقتصاد وأعمال' => 'كتب في علوم الاقتصاد وإدارة الأعمال',
        'قصص أطفال'    => 'قصص تعليمية وترفيهية للأطفال',
        'شعر وأدب'      => 'دواوين شعرية ومجموعات أدبية متنوعة',
        'سياسة'         => 'كتب في السياسة والعلاقات الدولية',
        'طب وصحة'       => 'مراجع طبية وكتب صحية للقارئ العام',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(array_keys(self::$categories));

        return [
            'name'        => $name,
            'slug'        => Str::slug($name . '-' . fake()->randomNumber(3)),
            'description' => self::$categories[$name],
            'parent_id'   => null,
        ];
    }
}
