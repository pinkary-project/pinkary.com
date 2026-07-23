<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlockedAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedAccount>
 */
final class BlockedAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
