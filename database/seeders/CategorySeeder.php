<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // 👈 تأكد من وجود هذا السطر لاستخدام الـ slug

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'روايات',
                'description' => 'كتب روائية متنوعة بين الخيال والواقع',
                'children'    => ['رواية عربية', 'رواية مترجمة', 'رواية تاريخية'],
            ],
            [
                'name'        => 'تاريخ',
                'description' => 'كتب تاريخية تروي أحداث الأمم والحضارات',
                'children'    => ['تاريخ إسلامي', 'تاريخ عالمي', 'سير وتراجم'],
            ],
            [
                'name'        => 'علوم وتكنولوجيا',
                'description' => 'كتب علمية في مجالات العلوم والتقنية',
                'children'    => ['علوم طبيعية', 'برمجة وتقنية', 'رياضيات'],
            ],
            [
                'name'        => 'تطوير الذات',
                'description' => 'كتب تساعدك على النمو الشخصي والمهني',
                'children'    => ['مهارات القيادة', 'الإنتاجية', 'الصحة النفسية'],
            ],
            [
                'name'        => 'اقتصاد وأعمال',
                'description' => 'كتب في علوم الاقتصاد وإدارة الأعمال',
                'children'    => ['ريادة الأعمال', 'الاستثمار والمال', 'إدارة'],
            ],
            [
                'name'        => 'فلسفة وفكر',
                'description' => 'كتب في الفكر الفلسفي والمنطق',
                'children'    => [],
            ],
            [
                'name'        => 'شعر وأدب',
                'description' => 'دواوين شعرية ومجموعات أدبية',
                'children'    => [],
            ],
            [
                'name'        => 'أطفال وناشئة',
                'description' => 'قصص تعليمية وترفيهية للأطفال',
                'children'    => [],
            ],
        ];

        foreach ($categories as $data) {
            $parent = Category::create([
                'name'        => $data['name'],
                'slug'        => Str::slug($data['name']), // 👈 إرجاع الـ slug للقسم الرئيسي
                'description' => $data['description'],
                'parent_id'   => null,
            ]);

            foreach ($data['children'] as $childName) {
                Category::create([
                    'name'        => $childName,
                    'slug'        => Str::slug($childName), // 👈 إرجاع الـ slug للأقسام الفرعية
                    'description' => null,
                    'parent_id'   => $parent->id,
                ]);
            }
        }
    }
}