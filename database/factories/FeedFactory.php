<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedVisibility;
use App\Models\Feed;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Feed>
 */
final class FeedFactory extends Factory
{
    /**
     * @use RefreshOnCreate<Feed>
     */
    use RefreshOnCreate;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Feed>
     */
    protected $model = Feed::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'user_id' => User::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'visibility' => FeedVisibility::Public,
        ];
    }

    /**
     * Indicate that the feed is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes): array => [
            'visibility' => FeedVisibility::Private,
        ]);
    }

    /**
     * Indicate that the feed is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes): array => [
            'visibility' => FeedVisibility::Public,
        ]);
    }
}
