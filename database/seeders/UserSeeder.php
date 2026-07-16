<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── حساب المدير الرئيسي (ثابت للاختبار) ────────────────────────────
        User::factory()->admin()->create([
            'username'      => 'admin',
            'email'         => 'admin@eshraq.com',
            'password_hash' => bcrypt('12345678'),
            'bio'           => 'مدير منصة إشراق للكتب الرقمية.',
        ]);

        // ── حساب مشرف ────────────────────────────────────────────────────────
        User::factory()->moderator()->create([
            'username'      => 'moderator',
            'email'         => 'moderator@eshraq.com',
            'password_hash' => bcrypt('12345678'),
        ]);

        // ── حسابات مؤلفين ────────────────────────────────────────────────────
        User::factory()->author()->count(5)->create();

        // ── حسابات قراء عاديين ───────────────────────────────────────────────
        User::factory()->count(20)->create();
    }
}
