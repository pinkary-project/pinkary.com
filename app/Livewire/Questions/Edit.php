<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Edit extends Component
{
    use NeedsVerifiedEmail;

    /**
     * The component's question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * The component's answer.
     */
    public string $answer = '';

    /**
     * The selected topic ID.
     */
    public ?int $topicId = null;

    /**
     * Mount the component.
     */
    public function mount(string $questionId): void
    {
        $this->questionId = $questionId;
        $question = Question::findOrFail($questionId);
        $rawAnswer = $question->getRawOriginal('answer');
        $this->answer = is_string($rawAnswer) ? $rawAnswer : '';
        $this->topicId = $question->topic_id;
    }

    /**
     * Updates the question with the given answer.
     */
    public function update(#[CurrentUser] User $user): void
    {
        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'answer' => ['required', 'string', 'max:1000', new NoBlankCharacters],
            'topicId' => [
                'nullable',
                Rule::exists('topics', 'id')->where('is_active', true)->where('is_system', false),
            ],
        ]);

        $question = Question::query()
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->find($this->questionId);

        $originalAnswer = $question->answer ?? null;

        if (is_null($question)) {
            $this->dispatch('notification.created', message: 'Sorry, something unexpected happened. Please try again.');
            $this->redirectRoute('profile.show', ['username' => $user->username], navigate: true);

            return;
        }

        if ($question->answer_created_at !== null && $question->answer_created_at->diffInHours(now()) > 24) {
            $this->dispatch('notification.created', message: 'Answer cannot be edited after 24 hours.');

            return;
        }

        $this->authorize('update', $question);

        if ($originalAnswer === null) {
            $validated['answer_created_at'] = now();
        } else {
            $validated['answer_updated_at'] = now();
        }

        if ($this->topicId !== null) {
            $validated['topic_id'] = $this->topicId;
        }
        unset($validated['topicId']);

        $question->update($validated);

        if ($originalAnswer !== null) {
            $question->likes()->delete();

            $this->dispatch('close-modal', "question.edit.answer.{$question->id}");
        }

        $this->dispatch('notification.created', message: $originalAnswer === null ? 'Question answered.' : ($question->isSharedUpdate() ? 'Post updated.' : 'Answer updated.'));
        $this->dispatch('question.updated');
    }

    /**
     * Reports the question.
     */
    public function report(): void
    {
        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        $question->update([
            'is_reported' => true,
        ]);

        $this->dispatch('notification.created', message: 'Question reported.');
        $this->dispatch('question.reported');
    }

    /**
     * Ignores the question.
     */
    public function ignore(): void
    {
        $this->dispatch('notification.created', message: 'Question ignored.');

        $this->dispatch('question.ignore', questionId: $this->questionId);
    }

    /**
     * Select or create a topic by name on the fly.
     */
    public function selectOrCreateTopic(string $name): void
    {
        $clean = mb_ltrim(mb_trim($name), '#');

        if (mb_strlen($clean) < 2 || mb_strlen($clean) > 50) {
            return;
        }

        $topic = Topic::firstOrCreate(
            ['slug' => Str::slug($clean)],
            [
                'name' => $clean,
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ]
        );

        $this->topicId = $topic->id;
    }

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] User $user): View
    {
        $topics = Topic::query()
            ->where('is_active', true)
            ->where('is_system', false)
            ->orderBy('name')
            ->get();

        $recentTopicIds = Question::query()
            ->where('from_id', $user->id)
            ->whereNotNull('topic_id')
            ->latest('id')
            ->pluck('topic_id')
            ->unique()
            ->take(3)
            ->values()
            ->all();

        $recentTopics = ! empty($recentTopicIds)
            ? Topic::query()
                ->whereIn('id', $recentTopicIds)
                ->where('is_active', true)
                ->where('is_system', false)
                ->get()
            : collect();

        return view('livewire.questions.edit', [
            'question' => Question::findOrFail($this->questionId),
            'user' => $user,
            'topics' => $topics,
            'recentTopics' => $recentTopics,
        ]);
    }
}
