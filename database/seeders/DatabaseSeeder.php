<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * ترتيب التشغيل مهم جداً — لا تغيّره.
     *
     * 1. Users أولاً (لأن Authors يحتاج user_id)
     * 2. Categories (لأن Books يحتاج category_id)
     * 3. Authors (لأن Books يحتاج author_id)
     * 4. Books آخراً (يحتاج الثلاثة السابقين)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            AuthorSeeder::class,
            BookSeeder::class,
        ]);
    }
}
