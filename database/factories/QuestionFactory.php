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
    /**
     * @use RefreshOnCreate<Question>
     */
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
}
