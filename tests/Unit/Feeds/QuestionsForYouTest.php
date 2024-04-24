<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\Database\Eloquent\Builder;

describe('verify query', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();

        $this->inspirationalUser = User::factory()
            ->has(Question::factory()
                ->hasLikes(1, ['user_id' => $this->user->id])
                ->state(['answer' => 'yes']),
                'questionsReceived')
            ->create();
    });

    it('only gets questions liked by inspirational user', function () {

        Question::factory(rand(1, 5))
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->create();

        Question::factory()->create([
            'content' => 'This question should not be included in the feed',
        ]);

        $builder = (new QuestionsForYouFeed($this->user))->builder();

        $result = $builder->get();

        $result->each(function ($question) {
            expect($question->likes()->where('user_id', $this->inspirationalUser->id)->exists())->toBeTrue();
        });

        expect($result->where('content', 'This question should not be included in the feed')->count())->toBe(0);
    });

    it('does not get questions with no answer', function () {

        Question::factory(2)
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->create();

        Question::factory(2)
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->state(['answer' => null])
            ->create();

        $builder = (new QuestionsForYouFeed($this->user))->builder();

        $result = $builder->get();
        expect($result->count())->toBe(2);
    });

    it('does not get questions that are reported', function () {

        Question::factory(2)
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->create();

        Question::factory(2)
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->state(['is_reported' => true])
            ->create();

        $builder = (new QuestionsForYouFeed($this->user))->builder();

        $result = $builder->get();
        expect($result->count())->toBe(2);
    });

    it('does not get questions that are ignored', function () {

        Question::factory(2)
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->create();

        Question::factory(2)
            ->hasLikes(1, ['user_id' => $this->inspirationalUser->id])
            ->state(['is_ignored' => true])
            ->create();

        $builder = (new QuestionsForYouFeed($this->user))->builder();

        $result = $builder->get();
        expect($result->count())->toBe(2);
    });

});

it('builder returns Eloquent\Builder instance', function () {
    $builder = (new QuestionsForYouFeed(User::factory()->create()))->builder();

    expect($builder)->toBeInstanceOf(Builder::class);
});
