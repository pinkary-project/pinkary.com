<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreQuestionViewRequest;
use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Services\Firewall;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionViewController
{
    /** Record an authorized human impression using the existing view job. */
    public function store(StoreQuestionViewRequest $request, Question $question, Firewall $firewall): Response
    {
        Gate::authorize('view', $question);

        if (! $firewall->isBot($request) && $question->getRawOriginal('answer') !== null) {
            $viewer = $request->user()->id ?? 'mobile:'.$request->string('viewer_id')->toString();
            IncrementViews::dispatch(collect([$question]), $viewer);
        }

        return response()->noContent();
    }
}
