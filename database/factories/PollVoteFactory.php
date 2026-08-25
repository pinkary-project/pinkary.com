<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PollVote>
 */
final class PollVoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'poll_option_id' => PollOption::factory(),
            'question_id' => function (array $attributes): string {
                /** @var string|null $questionId */
                $questionId = PollOption::query()
                    ->whereKey($attributes['poll_option_id'])
                    ->value('question_id');

                if ($questionId === null) {
                    throw new RuntimeException('Cannot derive question_id: the poll option does not exist.');
                }

                return $questionId;
            },
        ];
    }
}
