<?php

declare(strict_types=1);

namespace Tests\Console;

use App\Models\Question;
use App\Models\Topic;
use App\Models\User;

it('backfills posts without topics into appropriate topics or general', function (): void {
    $author = User::factory()->create();
    $laravelTopic = Topic::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $generalTopic = Topic::factory()->create(['name' => 'General', 'slug' => 'general']);

    $laravelPost = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'topic_id' => null,
        'answer' => 'I really love building apps with Laravel framework!',
    ]);

    $randomPost = Question::factory()->create([
        'to_id' => $author->id,
        'from_id' => $author->id,
        'topic_id' => null,
        'answer' => 'Just another beautiful day.',
    ]);

    $this->artisan('pinkary:backfill-post-topics')
        ->assertSuccessful();

    expect($laravelPost->fresh()->topic->slug)->toBe('laravel')
        ->and($randomPost->fresh()->topic->slug)->toBe('uncategorized');
});
