<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Actions\Channels\CreateChannel;
use App\Actions\Questions\UpdateQuestion;
use App\Actions\Questions\UpdateQuestionStatus;
use App\Livewire\Concerns\HasChannelPicker;
use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Channel;
use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * @property-read Collection<int, Channel> $availableChannels
 * @property-read Channel|null $selectedChannel
 */
final class Edit extends Component
{
    use HasChannelPicker;
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
     * Mount the component.
     */
    public function mount(string $questionId): void
    {
        $this->questionId = $questionId;
        $question = Question::findOrFail($questionId);
        $rawAnswer = $question->getRawOriginal('answer');
        $this->answer = is_string($rawAnswer) ? $rawAnswer : '';
        $this->channelId = blank($question->parent_id) ? $question->channel_id : null;
    }

    /**
     * Updates the question with the given answer.
     */
    public function update(
        #[CurrentUser] User $user,
        CreateChannel $createChannel,
        UpdateQuestion $updateQuestion,
    ): void {
        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        /** @var array<string, string> $validated */
        $validated = $this->validate([
            'answer' => ['required', 'string', 'max:1000', new NoBlankCharacters],
        ]);

        $question = Question::query()
            ->tap(new WhereNotModerated)
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

        $syncChannel = $question->isSharedUpdate() && blank($question->parent_id);
        $previousChannelId = $question->channel_id;

        if ($syncChannel) {
            $finalChannelId = $this->resolveChannelId($user, $createChannel);

            if ($finalChannelId === false) {
                return;
            }

            $validated['channel_id'] = $finalChannelId;
        }

        $channels = $updateQuestion->handle(
            $question,
            $validated,
            $syncChannel,
            $previousChannelId,
            $originalAnswer !== null,
        );

        if ($channels['previousChannel'] instanceof Channel) {
            $this->dispatch('channel-count-updated', channelId: $channels['previousChannel']->id, count: $channels['previousChannel']->questions_count);
        }

        if ($channels['channel'] instanceof Channel) {
            $this->dispatch('channel-count-updated', channelId: $channels['channel']->id, count: $channels['channel']->questions_count);
        }

        if ($originalAnswer !== null) {
            $this->dispatch('close-modal', "question.edit.answer.{$question->id}");
        }

        $this->dispatch('notification.created', message: $originalAnswer === null ? 'Question answered.' : ($question->isSharedUpdate() ? 'Post updated.' : 'Answer updated.'));
        $this->dispatch('question.updated');
    }

    /**
     * Reports the question.
     */
    public function report(UpdateQuestionStatus $updateQuestionStatus): void
    {
        $question = Question::findOrFail($this->questionId);

        $this->authorize('update', $question);

        $updateQuestionStatus->handle($question, reported: true);

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
     * Render the component.
     */
    public function render(#[CurrentUser] User $user): View
    {
        return view('livewire.questions.edit', [
            'question' => Question::findOrFail($this->questionId),
            'user' => $user,
        ]);
    }
}
