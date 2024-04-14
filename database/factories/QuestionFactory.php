<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
final class QuestionFactory extends Factory
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
            'from_id' => User::query()->InRandomOrder()->first()?->id ?? User::factory(),
            'to_id' => User::query()->InRandomOrder()->first()?->id ?? User::factory(),
            'content' => $this->faker->sentence,
            'anonymously' => $this->faker->boolean,
            'answer' => $this->faker->sentence,
            'answered_at' => $this->faker->dateTime,
            'pinned' => false,
        ];
    }
}
