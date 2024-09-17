<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hashtag;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hashtag>
 */
final class HashtagFactory extends Factory
{
    /**
     * @use RefreshOnCreate<Hashtag>
     */
    use RefreshOnCreate;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
