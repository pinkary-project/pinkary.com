<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word
        ];
    }

    /**
     * Indicate that the tag should be trending.
     */
    public function isTrending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_trending' => true,
        ]);
    }
}
