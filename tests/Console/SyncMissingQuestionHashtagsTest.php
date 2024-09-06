<?php

declare(strict_types=1);

it('syncs hashtag relations for questions that are missing hashtags', function () {
    /** @var Tests\TestCase $this */
    $withHashtags = App\Models\Question::factory(10)->create(['answer' => 'has a #hashtag']);
    $withoutHashtags = App\Models\Question::factory(10)->create(['answer' => 'no hashtags here']);

    Illuminate\Support\Facades\DB::table('hashtag_question')->truncate();

    $this->artisan('app:sync-missing-hashtags')->assertSuccessful();

    $withHashtags->load('hashtags');
    $withoutHashtags->load('hashtags');

    expect($withHashtags->every(
        fn (App\Models\Question $question): bool => $question->hashtags->pluck('name')->all() === ['hashtag'])
    )
        ->toBeTrue();
    expect($withoutHashtags->every(
        fn (App\Models\Question $question): bool => $question->hashtags->isEmpty())
    )
        ->toBeTrue();
});
