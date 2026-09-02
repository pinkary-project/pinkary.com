<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Channel;
use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Channel> $availableChannels
 * @property-read Channel|null $selectedChannel
 */
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
     * Optional channel ID.
     */
    public ?int $channelId = null;

    /**
     * Optional newly created channel name (deferred until post update).
     */
    public ?string $channelName = null;

    /**
     * Mount the component.
     */
    public function mount(string $questionId): void
    {
        $this->questionId = $questionId;
        $question = Question::findOrFail($questionId);
        $rawAnswer = $question->getRawOriginal('answer');
        $this->answer = is_string($rawAnswer) ? $rawAnswer : '';
        $this->channelId = $question->channel_id;
    }

    /**
     * Updates the question with the given answer.
     */
    public function update(#[CurrentUser] User $user): void
    {
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

        $finalChannelId = null;

        if ($question->isSharedUpdate() && blank($question->parent_id)) {
            if ($this->channelName !== null) {
                $slug = Str::slug($this->channelName);
                if (filled($slug)) {
                    $channel = Channel::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'user_id' => $user->id,
                            'name' => $this->channelName,
                            'questions_count' => 0,
                        ],
                    );
                    $finalChannelId = $channel->id;
                }
            } elseif ($this->channelId !== null) {
                $finalChannelId = $this->channelId;
            }
        }

        $previousChannelId = $question->channel_id;
        $validated['channel_id'] = $finalChannelId;

        $question->update($validated);

        if ($previousChannelId !== $finalChannelId) {
            if ($previousChannelId !== null) {
                Channel::whereKey($previousChannelId)->decrement('questions_count');
            }
            if ($finalChannelId !== null) {
                Channel::whereKey($finalChannelId)->increment('questions_count');
            }
            Cache::forget('channels:popular');
        }

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
     * Get available channels.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Channel>
     */
    #[Computed]
    public function availableChannels(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            'channels:popular',
            3600,
            fn (): \Illuminate\Database\Eloquent\Collection => Channel::query()
                ->orderByDesc('questions_count')
                ->orderBy('name')
                ->limit(8)
                ->get(),
        );
    }

    /**
     * Search channels by query.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function searchChannels(string $query): array
    {
        $q = mb_trim($query);

        if ($q === '') {
            return $this->availableChannels->map(fn (Channel $channel): array => [
                'id' => $channel->id,
                'name' => $channel->name,
            ])->values()->all();
        }

        return Channel::query()
            ->where('name', 'like', "%{$q}%")
            ->orderByDesc('questions_count')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (Channel $channel): array => [
                'id' => $channel->id,
                'name' => $channel->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Get the selected channel model.
     */
    #[Computed]
    public function selectedChannel(): ?Channel
    {
        return $this->channelId !== null ? Channel::find($this->channelId) : null;
    }

    /**
     * Select or clear the active channel.
     */
    public function selectChannel(?int $id): void
    {
        $this->channelId = $id;
        $this->channelName = null;
        unset($this->selectedChannel);
    }

    /**
     * Validate and stage a new channel for creation upon posting.
     *
     * @return array{id: int|string, name: string}|null
     */
    public function createChannel(string $name): ?array
    {
        if ($this->doesNotHaveVerifiedEmail()) {
            return null;
        }

        $name = mb_trim($name);

        $validator = Validator::make(
            ['name' => $name],
            ['name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\pN\s\-_]+$/u']],
        );

        if ($validator->fails()) {
            $this->addError('newChannel', (string) $validator->errors()->first('name'));

            return null;
        }

        $slug = Str::slug($name);

        if (blank($slug)) {
            $this->addError('newChannel', 'Please enter a valid channel name.');

            return null;
        }

        $channel = Channel::where('slug', $slug)->first();

        if ($channel) {
            $this->channelId = $channel->id;
            $this->channelName = null;
            $this->resetValidation('newChannel');
            unset($this->selectedChannel);

            return [
                'id' => $channel->id,
                'name' => $channel->name,
            ];
        }

        $this->channelId = null;
        $this->channelName = $name;
        $this->resetValidation('newChannel');
        unset($this->selectedChannel);

        return [
            'id' => 'new:'.$slug,
            'name' => $name,
        ];
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
