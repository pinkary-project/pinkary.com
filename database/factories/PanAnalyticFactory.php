<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PanAnalytic>
 */
final class PanAnalyticFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'impressions' => fn (array $attributes): int => (is_numeric($attributes['hovers']) ? (int) $attributes['hovers'] : 0) +
                (is_numeric($attributes['clicks']) ? (int) $attributes['clicks'] : 0),
            'hovers' => $this->faker->randomNumber(),
            'clicks' => $this->faker->randomNumber(),
        ];
    }
}
