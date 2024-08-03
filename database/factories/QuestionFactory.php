<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
final class QuestionFactory extends Factory
{
    use RefreshOnCreate;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Question>
     */
    protected $model = Question::class;

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
            'answer' => $this->faker->sentence,
            'answer_created_at' => $this->faker->dateTime,
            'pinned' => false,
            'views' => $this->faker->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the question is shared Update.
     */
    public function sharedUpdate(): self
    {
        return $this->state(fn (array $attributes): array => [
            'content' => '__UPDATE__',
            'answer' => $this->faker->sentence,
        ]);
    }

    /**
     * Indicate that the question is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pinned' => true,
        ]);
    }

    /**
     * Indicate that the question was asked anonymously.
     */
    public function anonymously(): static
    {
        return $this->state(fn (array $attributes): array => [
            'anonymously' => true,
        ]);
    }

    /**
     * Indicate that the question was reported.
     */
    public function reported(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_reported' => true,
        ]);
    }

    /**
     * Indicate that the question is ignored.
     */
    public function ignored(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_ignored' => true,
        ]);
    }

    /**
     * Indicate that the question has no answer.
     */
    public function unanswered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer' => null,
            'answer_created_at' => null,
            'answer_updated_at' => null,
        ]);
    }

    /**
     * Indicate that the question has been viewed a few times.
     */
    public function fewView(): static
    {
        return $this->state(fn (array $attributes): array => [
            'views' => $this->faker->numberBetween(0, 10),
        ]);
    }

    /**
     * Indicate that the question has been viewed many times.
     */
    public function manyViews(): static
    {
        return $this->state(fn (array $attributes): array => [
            'views' => $this->faker->numberBetween(500, 1000),
        ]);
    }
}
