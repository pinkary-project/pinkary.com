<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Livewire\Questions\ReportModal;
use App\Models\Question;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Livewire\Livewire;

it('allows a user to report a post for spam or policy violation', function (): void {
    $author = User::factory()->create();
    $reporter = User::factory()->create();

    $question = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'answer' => 'Spam content here',
        'is_reported' => false,
    ]);

    Livewire::actingAs($reporter)
        ->test(ReportModal::class, ['questionId' => $question->id])
        ->set('category', 'spam')
        ->set('details', 'Blatant bot spam')
        ->call('submit')
        ->assertDispatched('notification.created');

    expect($question->fresh()->is_reported)->toBeTrue();
});

it('allows a user to report a post with a wrong topic and suggest a new topic', function (): void {
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $currentTopic = Topic::factory()->create(['name' => 'PHP']);
    $suggestedTopic = Topic::factory()->create(['name' => 'Laravel']);

    $question = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'topic_id' => $currentTopic->id,
        'answer' => 'Post with wrong topic',
    ]);

    Livewire::actingAs($reporter)
        ->test(ReportModal::class, ['questionId' => $question->id])
        ->set('category', 'wrong_topic')
        ->set('details', 'This post belongs in Laravel, not PHP.')
        ->set('suggestedTopicId', $suggestedTopic->id)
        ->call('submit')
        ->assertDispatched('notification.created');

    $report = TopicReport::where('question_id', $question->id)->first();
    expect($report)->not->toBeNull()
        ->and($report->reporter_id)->toBe($reporter->id)
        ->and($report->current_topic_id)->toBe($currentTopic->id)
        ->and($report->suggested_topic_id)->toBe($suggestedTopic->id)
        ->and($question->fresh()->topic_id)->toBe($currentTopic->id);
});

it('prevents a user from reporting their own post', function (): void {
    $author = User::factory()->create();
    $question = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
    ]);

    Livewire::actingAs($author)
        ->test(ReportModal::class, ['questionId' => $question->id])
        ->call('submit')
        ->assertDispatched('notification.created');

    expect($question->fresh()->is_reported)->toBeFalse();
});
