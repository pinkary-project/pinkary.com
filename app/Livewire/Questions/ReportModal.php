<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ReportModal extends Component
{
    /**
     * The question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * The report reason category.
     */
    public string $category = 'spam';

    /**
     * The suggested topic ID (if reporting wrong topic).
     */
    public ?int $suggestedTopicId = null;

    /**
     * Additional details/note.
     */
    public string $details = '';

    /**
     * Mount the component.
     */
    public function mount(string $questionId): void
    {
        $this->questionId = $questionId;
    }

    /**
     * Submit the report.
     */
    public function submit(#[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $question = Question::with('topic')->findOrFail($this->questionId);

        if ($question->to_id === $user->id) {
            $this->dispatch('notification.created', message: 'You cannot report your own post.');
            $this->dispatch('close-modal', "question.report.{$this->questionId}");

            return;
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'category' => ['required', 'string', Rule::in(['spam', 'harassment', 'inappropriate', 'wrong_topic', 'other'])],
            'suggestedTopicId' => [
                'nullable',
                Rule::exists('topics', 'id')->where('is_active', true)->where('is_system', false),
            ],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->category === 'wrong_topic' && $question->topic_id !== null) {
            $alreadyReportedTopic = TopicReport::query()
                ->where('question_id', $question->id)
                ->where('reporter_id', $user->id)
                ->where('current_topic_id', $question->topic_id)
                ->exists();

            if (! $alreadyReportedTopic) {
                TopicReport::query()->create([
                    'question_id' => $question->id,
                    'reporter_id' => $user->id,
                    'current_topic_id' => $question->topic_id,
                    'suggested_topic_id' => $this->suggestedTopicId,
                    'reason' => $this->details !== '' ? $this->details : 'Wrong topic classification.',
                    'status' => 'pending',
                ]);

                // Lightweight consensus: if >= 3 reports agree on a suggested topic
                $topSuggested = TopicReport::query()
                    ->where('question_id', $question->id)
                    ->where('current_topic_id', $question->topic_id)
                    ->whereNotNull('suggested_topic_id')
                    ->selectRaw('suggested_topic_id, count(*) as votes')
                    ->groupBy('suggested_topic_id')
                    ->orderByDesc('votes')
                    ->first();

                if ($topSuggested && $topSuggested->votes >= 3) {
                    $question->updateQuietly(['topic_id' => $topSuggested->suggested_topic_id]);
                }
            }
        } else {
            // General violation report (spam, harassment, inappropriate, etc.)
            $question->update(['is_reported' => true]);
        }

        $this->dispatch('notification.created', message: 'Thank you for your report. We will review this post.');
        $this->dispatch('close-modal', "question.report.{$this->questionId}");
        $this->dispatch('question.reported');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $question = Question::with('topic')->findOrFail($this->questionId);

        $topics = Topic::query()
            ->where('is_active', true)
            ->where('is_system', false)
            ->where('id', '!=', $question->topic_id ?? 0)
            ->orderBy('name')
            ->get();

        return view('livewire.questions.report-modal', [
            'question' => $question,
            'topics' => $topics,
        ]);
    }
}
