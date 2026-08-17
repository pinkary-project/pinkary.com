<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Topic;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Topic>
 */
final class TopicFactory extends Factory
{
    /**
     * @use RefreshOnCreate<Topic>
     */
    use RefreshOnCreate;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Topic>
     */
    protected $model = Topic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'is_discoverable' => true,
            'is_system' => false,
        ];
    }

    /**
     * Indicate that the topic is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the topic is a system topic (e.g. Uncategorized).
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_system' => true,
            'is_discoverable' => false,
        ]);
    }
}
