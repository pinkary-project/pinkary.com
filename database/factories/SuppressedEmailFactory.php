<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SuppressedEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuppressedEmail>
 */
final class SuppressedEmailFactory extends Factory
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
            'reason' => 'mailcoach-406',
        ];
    }
}
