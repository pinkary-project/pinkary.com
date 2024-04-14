<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Like;
use App\Models\Question;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
final class LikeFactory extends Factory
{
    use RefreshOnCreate;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->first()?->id ?? User::factory(),
            'question_id' => Question::query()->inRandomOrder()->first()?->id ?? Question::factory(),
        ];
    }
}
    
