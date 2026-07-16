<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::inRandomOrder()->value('id') ?? User::factory(),
            'book_id'     => Book::inRandomOrder()->value('id') ?? Book::factory(),
            'rating'      => fake()->numberBetween(1, 5),
            'review_text' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
