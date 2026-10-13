<?php

declare(strict_types=1);

use App\Models\PollVote;

it('derives question_id from the poll option', function (): void {
    $pollVote = PollVote::factory()->create();

    expect($pollVote->question_id)->toBe($pollVote->pollOption->question_id);
});

it('fails loudly when the poll option does not exist', function (): void {
    PollVote::factory()->create(['poll_option_id' => '01a00000-0000-7000-8000-000000000000']);
})->throws(RuntimeException::class);
