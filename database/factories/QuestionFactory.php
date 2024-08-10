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
            'from_id' => User::factory(),
            'to_id' => User::factory(),
            'content' => $this->faker->sentence,
            'anonymously' => $this->faker->boolean,
            'pinned' => false,
            'is_update' => false,
            'views' => $this->faker->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the question is shared Update.
     */
    public function sharedUpdate(): self
    {
        return $this->state(fn (array $attributes): array => [
            'content' => $this->faker->sentence,
        ]);
    }
}
