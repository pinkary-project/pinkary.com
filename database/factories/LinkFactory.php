<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
final class LinkFactory extends Factory
{
    use RefreshOnCreate;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Link>
     */
    protected $model = Link::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'click_count' => 0,
            'description' => $this->faker->sentence,
            'url' => $this->faker->url,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the link was clicked.
     */
    public function clicked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'click_count' => $this->faker->numberBetween(0, 1000),
        ]);
    }
}
