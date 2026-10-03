<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreQuestionViewBatchRequest;
use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Services\Firewall;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionViewBatchController
{
    /** Record authorized post impressions in a batch. */
    public function store(StoreQuestionViewBatchRequest $request, Firewall $firewall): Response
    {
        if ($firewall->isBot($request)) {
            return response()->noContent();
        }

        /** @var list<string> $ids */
        $ids = $request->validated('question_ids');
        $questions = Question::query()->whereIn('id', $ids)->get()
            ->filter(fn (Question $question): bool => $question->getRawOriginal('answer') !== null && Gate::allows('view', $question));

        if ($questions->isNotEmpty()) {
            $viewer = $request->user()->id ?? 'mobile:'.$request->string('viewer_id')->toString();
            IncrementViews::dispatch($questions->toBase(), $viewer);
        }

        return response()->noContent();
    }
}
