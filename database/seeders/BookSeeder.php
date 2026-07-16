<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;
use App\Models\Author;
use App\Models\Category;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. جلب حساب المدير الرئيسي لتسجيله كـ uploader
        $user = User::where('role', 'admin')->first() ?? User::first();

        // 2. جلب الأقسام التي تم إنشاؤها بواسطة CategorySeeder
        $novels = Category::where('name', 'روايات')->first() ?? Category::first();
        $selfDev = Category::where('name', 'تطوير الذات')->first() ?? Category::first();
        $history = Category::where('name', 'تاريخ')->first() ?? Category::first();
        $islamic = Category::where('name', 'فلسفة وفكر')->first() ?? Category::first();

        // 3. جلب أو إنشاء المؤلفين (بدون حقل slug لأن جدول المؤلفين لا يحتوي عليه)
        $aedh = Author::firstOrCreate(['name' => 'عائض القرني']);
        $khaldoun = Author::firstOrCreate(['name' => 'ابن خلدون']);
        $taha = Author::firstOrCreate(['name' => 'د. محمد طه']);
        $mahfouz = Author::firstOrCreate(['name' => 'نجيب محفوظ']);
        $coelho = Author::firstOrCreate(['name' => 'باولو كويلو']);

        // 4. قائمة الكتب الحقيقية مع ربطها بالأقسام والمؤلفين
        $booksData = [
            [
                'title' => 'لا تحزن',
                'slug' => 'la-tahzan',
                'author_id' => $aedh->id,
                'category_id' => $islamic->id,
                'description' => 'كتاب أخلاقي ودعوي شهير يدعو إلى التفاؤل والرضا وقضاء الحوائج، والابتعاد عن الهموم والغموم والتركيز على الجوانب الإيجابية في الحياة والعيش في حدود يومك.',
                'status' => 'approved',
                'pdf_path' => 'books/pdfs/la-tahzan.pdf',
            ],
            [
                'title' => 'مقدمة ابن خلدون',
                'slug' => 'moqaddemat-ibn-khaldoun',
                'author_id' => $khaldoun->id,
                'category_id' => $history->id,
                'description' => 'أحد أهم كتب التاريخ وعلم الاجتماع البشري في العالم. ألّفه ابن خلدون سنة 1377م كمدخل لمؤلفه الضخم الموسوعي كتاب العبر، ويبحث في العمران البشري وقوانين تطور الدول والقبائل.',
                'status' => 'approved',
                'pdf_path' => 'books/pdfs/moqaddemat-ibn-khaldoun.pdf',
            ],
            [
                'title' => 'علاقات خطرة',
                'slug' => 'alaqat-khatera',
                'author_id' => $taha->id,
                'category_id' => $selfDev->id,
                'description' => 'كتاب متميز في علم النفس وتطوير الذات يتناول العلاقات الإنسانية وأنواعها المختلفة، والشفاء النفسي وكيفية تجنب العلاقات السامة والمؤذية وبناء علاقات صحية ومتوازنة مع الآخرين ومع النفس.',
                'status' => 'approved',
                'pdf_path' => 'books/pdfs/alaqat-khatera.pdf',
            ],
            [
                'title' => 'أولاد حارتنا',
                'slug' => 'awlad-haretna',
                'author_id' => $mahfouz->id,
                'category_id' => $novels->id,
                'description' => 'واحدة من أشهر وأروع روايات الأديب المصري نجيب محفوظ الحائز على جائزة نوبل للآداب. الرواية فلسفية رمزية تناقش قضايا العدالة الإنسانية، والصراع الأزلي بين الخير والشر والبحث عن الحقيقة.',
                'status' => 'approved',
                'pdf_path' => 'books/pdfs/awlad-haretna.pdf',
            ],
            [
                'title' => 'الخيميائي',
                'slug' => 'al-khemyai',
                'author_id' => $coelho->id,
                'category_id' => $novels->id,
                'description' => 'رواية عالمية ملهمة تحكي قصة الراعي الإسباني الشاب سانتياغو في رحلته للبحث عن كنز مدفون قرب أهرامات مصر، وهي رحلة رمزية تسلط الضوء على السعي وراء الأحلام واكتشاف الشغف وقراءة لغة الكون.',
                'status' => 'approved',
                'pdf_path' => 'books/pdfs/al-khemyai.pdf',
            ]
        ];

        // 5. إدخال أو تحديث الكتب في قاعدة البيانات
        foreach ($booksData as $book) {
            Book::updateOrCreate(
                ['slug' => $book['slug']], 
                array_merge($book, [
                    'uploader_id' => $user?->id,
                    'views_count' => rand(50, 500),
                    'downloads_count' => rand(10, 150),
                ])
            );
        }
    }
}