<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicReport>
 */
final class TopicReportFactory extends Factory
{
    /**
     * @use RefreshOnCreate<TopicReport>
     */
    use RefreshOnCreate;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TopicReport>
     */
    protected $model = TopicReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'reporter_id' => User::factory(),
            'category' => 'wrong_topic',
            'current_topic_id' => Topic::factory(),
            'suggested_topic_id' => null,
            'reason' => 'This post does not belong in this topic.',
            'status' => 'pending',
        ];
    }
}
