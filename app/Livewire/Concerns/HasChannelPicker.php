<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Actions\Channels\CreateChannel;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * @property-read Collection<int, Channel> $availableChannels
 * @property-read Channel|null $selectedChannel
 *
 * @mixin \Livewire\Component
 */
trait HasChannelPicker
{
    use NeedsVerifiedEmail;

    /**
     * Optional initial channel ID (used as default when resetting after post).
     */
    #[Locked]
    public ?int $initialChannelId = null;

    /**
     * Optional channel ID.
     */
    public ?int $channelId = null;

    /**
     * Optional channel name staged for creation upon posting.
     */
    public ?string $channelName = null;

    /**
     * Get available channels.
     *
     * @return Collection<int, Channel>
     */
    #[Computed]
    public function availableChannels(): Collection
    {
        return Cache::remember(
            'channels:popular',
            3600,
            fn (): Collection => Channel::query()
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

        if (RateLimiter::tooManyAttempts('create-channel:'.auth()->id(), 10)) {
            $this->addError('newChannel', 'Too many attempts. Please try again later.');

            return null;
        }

        RateLimiter::hit('create-channel:'.auth()->id(), 60);

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
     * Resolve the channel ID from either an existing ID or a staged new channel name.
     *
     * Returns the resolved channel ID, null if none selected, or false if validation failed.
     */
    protected function resolveChannelId(User $user, CreateChannel $createChannel): int|null|false
    {
        if ($this->channelName !== null) {
            $channelName = mb_trim($this->channelName);
            $channelValidator = Validator::make(
                ['channelName' => $channelName],
                ['channelName' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\pN\s\-_]+$/u']],
            );

            if ($channelValidator->fails()) {
                $this->addError('channelName', (string) $channelValidator->errors()->first('channelName'));

                return false;
            }

            $slug = Str::slug($channelName);
            if (blank($slug)) {
                $this->addError('channelName', 'Please enter a valid channel name.');

                return false;
            }

            return $createChannel->handle($user, $channelName, $slug)->id;
        }

        if ($this->channelId !== null) {
            if (Channel::whereKey($this->channelId)->exists()) {
                return $this->channelId;
            }

            $this->addError('channelId', 'Selected channel does not exist.');

            return false;
        }

        return null;
    }
}
