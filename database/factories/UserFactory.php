<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'username'          => fake()->unique()->userName(),
            'email'             => fake()->unique()->safeEmail(),
            'password_hash'     => bcrypt('password'), // كلمة المرور الافتراضية: password
            'role'              => UserRole::Reader,
            'bio'               => fake()->optional(0.6)->paragraph(),
            'avatar'            => null,
            'email_verified_at' => now(),
        ];
    }

    /** مستخدم بدور مؤلف */
    public function author(): static
    {
        return $this->state(fn () => ['role' => UserRole::Author]);
    }

    /** مستخدم بدور مشرف */
    public function moderator(): static
    {
        return $this->state(fn () => ['role' => UserRole::Moderator]);
    }

    /** مستخدم بدور مدير */
    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Admin]);
    }

    /** مستخدم غير مفعَّل البريد */
    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
