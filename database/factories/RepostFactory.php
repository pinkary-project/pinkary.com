<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repost>
 */
final class RepostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question_id' => Question::factory(),
        ];
    }
}
