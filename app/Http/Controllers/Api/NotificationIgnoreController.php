<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\UpdateQuestionStatus;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final readonly class NotificationIgnoreController
{
    /** Ignore received questions and delete notifications up to the given time. */
    public function __invoke(Request $request, UpdateQuestionStatus $updateQuestionStatus): Response
    {
        /** @var array{until_datetime: string} $validated */
        $validated = $request->validate([
            'until_datetime' => ['required', 'date'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $untilDatetime = CarbonImmutable::parse($validated['until_datetime']);

        DB::transaction(function () use ($user, $untilDatetime, $updateQuestionStatus): void {
            $questionsToIgnore = $user
                ->notifications()
                ->where('created_at', '<=', $untilDatetime)
                ->where('type', QuestionCreated::class)
                ->select('data->question_id');

            $user
                ->questionsReceived()
                ->whereIn('id', $questionsToIgnore)
                ->each(function (Question $question) use ($updateQuestionStatus): void {
                    $updateQuestionStatus->handle($question, ignored: true);
                });

            $user->notifications()
                ->where('created_at', '<=', $untilDatetime)
                ->delete();
        });

        return response()->noContent();
    }
}
