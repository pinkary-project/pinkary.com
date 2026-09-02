<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Channel;
use App\Models\Question;
use App\Models\User;
use App\Rules\MaxUploads;
use App\Rules\NoBlankCharacters;
use Closure;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Intervention\Image\Drivers;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

/**
 * @property-read bool $isSharingUpdate
 * @property-read bool $canThread
 * @property-read int $maxContentLength
 * @property-read int $maxThreadPosts
 * @property-read int $needsCaptcha
 * @property-read string $turnstileId
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Channel> $availableChannels
 * @property-read Channel|null $selectedChannel
 */
final class Create extends Component
{
    use NeedsVerifiedEmail;
    use WithFileUploads;

    /**
     * The disk to store the images.
     */
    public const ?string IMAGE_DISK = null;

    /**
     * Max number of posts allowed in a thread.
     */
    public const int MAX_THREAD_POSTS = 10;

    /**
     * Max number of images allowed.
     */
    #[Locked]
    public int $uploadLimit = 3;

    /**
     * Max file size allowed.
     */
    #[Locked]
    public int $maxFileSize = 1024 * 8;

    /**
     * The component's user ID.
     */
    #[Locked]
    public ?int $toId = null;

    /**
     * Which question this question is commenting on.
     */
    #[Locked]
    public ?string $parentId = null;

    /**
     * Optional custom draft key.
     */
    #[Locked]
    public ?string $customDraftKey = null;

    /**
     * Optional channel ID.
     */
    public ?int $channelId = null;

    /**
     * Optional newly created channel name (deferred until post creation).
     */
    public ?string $channelName = null;

    /**
     * The component's content.
     */
    public string $content = '';

    /**
     * Additional posts forming a thread, published together with the main post.
     *
     * @var array<int, string>
     */
    public array $threadPosts = [];

    /**
     * Poll state for each additional thread post.
     *
     * @var array<int, array{isPoll: bool, options: array<int, string>, duration: int}>
     */
    public array $threadPolls = [];

    /**
     * Uploaded images.
     *
     * @var array<int, UploadedFile>
     */
    public array $images = [];

    /**
     * Draft key containing images handed off to this composer.
     */
    public ?string $imageSourceDraftKey = null;

    /**
     * The component's anonymously state.
     */
    public bool $anonymously = true;

    /**
     * Whether this is a poll.
     */
    public bool $isPoll = false;

    /**
     * The turnstile response from the client (bound via wire:model).
     */
    public ?string $cfTurnstileResponse = null;

    /**
     * Poll options.
     *
     * @var array<int, string>
     */
    public array $pollOptions = ['', ''];

    /**
     * Poll duration in days.
     */
    public int $pollDuration = 1;

    /**
     * The updated lifecycle hook.
     */
    public function updated(mixed $property): void
    {
        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        if ($property === 'images') {
            $this->runImageValidation();
            $this->uploadImages();
        }
    }

    /**
     * Run image validation rules.
     */
    public function runImageValidation(): void
    {
        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $this->validate(
            rules: [
                'images' => [
                    'bail',
                    new MaxUploads($this->uploadLimit),
                ],
                'images.*' => [
                    File::image()
                        ->types(['jpeg', 'png', 'gif', 'webp', 'jpg'])
                        ->max($this->maxFileSize)
                        ->dimensions(
                            Rule::dimensions()->maxWidth(4000)->maxHeight(4000)
                        ),

                    static function (string $attribute, mixed $value, Closure $fail): void {
                        /** @var UploadedFile $value */
                        $dimensions = $value->dimensions();
                        if (is_array($dimensions)) {
                            /** @var array<int, int> $dimensions */
                            [$width, $height] = $dimensions;
                            $aspectRatio = $width / $height;
                            $maxAspectRatio = 2 / 5;
                            if ($aspectRatio < $maxAspectRatio) {
                                $fail('The image aspect ratio must be less than 2/5.');
                            }
                        } else {
                            $fail('The image aspect ratio could not be determined.');
                        }
                    },

                ],
            ],
            messages: [
                'images.*.image' => 'The file must be an image.',
                'images.*.mimes' => 'The image must be a file of type: :values.',
                'images.*.max' => 'The image may not be greater than :max kilobytes.',
                'images.*.dimensions' => 'The image must be less than :max_width x :max_height pixels.',
            ]
        );
    }

    /**
     * Mount the component.
     */
    public function mount(#[CurrentUser] ?User $user): void
    {
        if ($user instanceof User) {
            $this->anonymously = $user->prefers_anonymous_questions;
        }
    }

    /**
     * Determine if the user is sharing an update.
     */
    #[Computed]
    public function isSharingUpdate(): bool
    {
        return $this->toId === auth()->id();
    }

    /**
     * Determine if the composer can publish a thread of multiple posts.
     */
    #[Computed]
    public function canThread(): bool
    {
        return filled($this->parentId) === false && $this->isSharingUpdate;
    }

    /**
     * Get the maximum number of posts a thread can contain.
     */
    #[Computed]
    public function maxThreadPosts(): int
    {
        return self::MAX_THREAD_POSTS;
    }

    /**
     * Choose appropriate placeholder copy.
     */
    #[Computed]
    public function placeholder(): string
    {
        return match (true) {
            filled($this->parentId) => 'Write a comment...',
            $this->isSharingUpdate() => 'Share an update...',
            default => 'Ask a question...'
        };
    }

    /**
     * Get the maximum content length.
     */
    #[Computed]
    public function maxContentLength(): int
    {
        return $this->isSharingUpdate ? 1000 : 255;
    }

    /**
     * Get the draft key.
     */
    #[Computed]
    public function draftKey(): string
    {
        if ($this->customDraftKey !== null) {
            return $this->customDraftKey;
        }

        return filled($this->parentId)
            ? "reply_{$this->parentId}"
            : 'post_new';
    }

    /**
     * Get the captcha widget ID, restricted to the characters allowed by Turnstile.
     */
    #[Computed]
    public function turnstileId(): string
    {
        $id = $this->draftKey().'_turnstile_'.($this->toId ?? 'global');

        return (string) preg_replace('/[^A-Za-z0-9_]/', '_', $id);
    }

    /**
     * Whether the current acting user should be shown a captcha.
     */
    #[Computed]
    public function needsCaptcha(): bool
    {
        return app()->isProduction() && (int) auth()->user()?->followers()->count() === 0;
    }

    /**
     * Refresh the component.
     */
    #[On([
        'link-settings.updated',
        'question.created',
    ])]
    public function refresh(): void
    {
        //
    }

    /**
     * Stores a new question.
     */
    public function store(#[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        // Treat whitespace-only rows as empty and keep each row's poll state aligned.
        /** @var array<int, array{isPoll: bool, options: array<int, string>, duration: int}> $threadPolls */
        $threadPolls = $this->threadPolls;
        $threadPosts = [];
        $normalizedThreadPolls = [];

        foreach ($this->threadPosts as $index => $post) {
            if (mb_trim($post) === '') {
                continue;
            }

            $threadPosts[] = $post;
            $normalizedThreadPolls[] = $threadPolls[$index] ?? $this->emptyThreadPoll();
        }

        $this->threadPosts = $threadPosts;
        $this->threadPolls = $normalizedThreadPolls;
        $threadPolls = $normalizedThreadPolls;

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($this->validationRules(), [
            'threadPosts.max' => __('A thread can have a maximum of :max extra posts.'),
            'threadPosts.*.min' => __('Each post must be at least 1 character.'),
            'threadPosts.*.max' => __('A post may not be greater than :max characters.'),
        ]);

        // Require captcha for users with zero followers (bot protection).
        if ($this->needsCaptcha) {
            $this->validate([
                'cfTurnstileResponse' => ['required', app(Turnstile::class)],
            ], [
                'cfTurnstileResponse.required' => __('The reCAPTCHA is required.'),
            ]);
        }

        $threadPosts = $this->canThread ? collect($this->threadPosts) : collect();

        // The thread posts are not a database column on questions.
        unset($validated['threadPosts']);

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subMinute())->count() >= 3) {
            $this->addError('content', 'You can only send 3 questions per minute.');

            return;
        }

        // Each post of a thread counts towards the daily limit.
        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subDay())->count() + 1 + $threadPosts->count() > 30) {
            $this->addError('content', 'You can only send 30 questions per day.');

            return;
        }

        if ($this->isPoll) {
            $this->validate([
                'pollDuration' => ['required', 'integer', 'min:1', 'max:7'],
            ]);

            /** @var array<int, string> $validOptions */
            $validOptions = array_filter($this->pollOptions, fn (string $option): bool => mb_trim($option) !== '');

            $hasEmptyOptions = false;
            foreach ($this->pollOptions as $option) {
                if (mb_trim($option) === '') {
                    $hasEmptyOptions = true;
                    break;
                }
            }

            if ($hasEmptyOptions) {
                $this->addError('pollOptions', 'All poll options are required.');

                return;
            }

            foreach ($this->pollOptions as $option) {
                if (mb_strlen($option) > 40) {
                    $this->addError('pollOptions', 'Poll options cannot exceed 40 characters.');

                    return;
                }
            }

            if (count($validOptions) < 2) {
                $this->addError('pollOptions', 'A poll must have at least 2 options.');

                return;
            }

            if (count($validOptions) > 4) {
                $this->addError('pollOptions', 'A poll can have maximum 4 options.');

                return;
            }
        }

        $threadPollOptions = [];
        foreach ($threadPolls as $index => $threadPoll) {
            $threadPollOptions[$index] = $this->validatedPollOptions($threadPoll, "threadPolls.{$index}");

            if ($threadPoll['isPoll'] && $threadPollOptions[$index] === null) {
                return;
            }
        }

        if ($this->isSharingUpdate) {
            $validated['answer_created_at'] = now();
            $validated['answer'] = $validated['content'];
            $validated['content'] = '__UPDATE__';
        }

        if (filled($this->parentId)) {
            $validated['parent_id'] = $this->parentId;
            $validated['root_id'] = Question::whereKey($this->parentId)->value('root_id') ?? $this->parentId;
        }

        $finalChannelId = null;

        if ($this->isSharingUpdate && blank($this->parentId)) {
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

        if ($finalChannelId !== null) {
            $validated['channel_id'] = $finalChannelId;
        }

        /** @var array<int, array<string, mixed>> $payloads */
        $payloads = [
            [
                ...$validated,
                'to_id' => $this->toId,
                'poll_expires_at' => $this->isPoll ? now()->addDays($this->pollDuration) : null,
            ],
        ];

        foreach ($threadPosts as $index => $postContent) {
            $pollExpiresAt = null;

            if ($this->threadPolls[$index]['isPoll'] ?? false) {
                $pollExpiresAt = now()->addDays((int) $this->threadPolls[$index]['duration']);
            }

            $payloads[] = [
                'to_id' => $this->toId,
                'content' => '__UPDATE__',
                'answer' => $postContent,
                'answer_created_at' => now(),
                'poll_expires_at' => $pollExpiresAt,
            ];
        }

        /** @var array<int, Question> $questions */
        $questions = DB::transaction(function () use ($user, $payloads): array {
            /** @var array<int, Question> $created */
            $created = [];

            foreach ($payloads as $index => $payload) {
                if ($index > 0) {
                    $payload['parent_id'] = $created[$index - 1]->id;
                    $payload['root_id'] = $created[0]->id;
                }

                $created[$index] = $user->questionsSent()->create($payload);
            }

            return $created;
        });

        $question = $questions[0];

        if ($finalChannelId !== null) {
            Channel::whereKey($finalChannelId)->increment('questions_count');
            Cache::forget('channels:popular');
        }

        if ($this->isPoll) {
            $options = [];

            foreach ($validOptions as $optionText) {
                $options[] = [
                    'text' => mb_trim($optionText),
                    'votes_count' => 0,
                ];
            }

            $question->pollOptions()->createMany($options);
        }

        foreach ($questions as $index => $createdQuestion) {
            if ($index === 0) {
                continue;
            }

            if (empty($threadPollOptions[$index - 1])) {
                continue;
            }

            $createdQuestion->pollOptions()->createMany(array_map(
                fn (string $option): array => ['text' => mb_trim($option), 'votes_count' => 0],
                $threadPollOptions[$index - 1],
            ));
        }

        $this->transferImagesFromSourceDraft();
        $this->deleteUnusedImages();

        $this->reset(['content', 'isPoll', 'pollDuration', 'threadPosts', 'threadPolls', 'imageSourceDraftKey', 'channelId', 'channelName']);
        $this->pollOptions = ['', ''];

        $this->anonymously = $user->prefers_anonymous_questions;

        $this->dispatch('question.created');
        $this->dispatch('close-modal', 'post-create');

        $message = match (true) {
            $threadPosts->isNotEmpty() => 'Thread sent.',
            filled($this->parentId) => 'Comment sent.',
            $this->isSharingUpdate => 'Update sent.',
            default => 'Question sent.'
        };

        if ($this->isSharingUpdate) {
            $this->dispatch('notification.created', message: $message, url: route('questions.show', [
                'username' => $question->to->username,
                'question' => $question,
            ]), actionText: 'View update');
        } else {
            $this->dispatch('notification.created', message: $message);
        }

        if (filled($this->parentId)) {
            $this->js(<<<'JS'
                Livewire.navigate(window.location.href);
            JS);
        }
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
    public function render(): View
    {
        $user = new User;

        if (filled($this->toId)) {
            $user = $user->findOrFail($this->toId);
        }

        return view('livewire.questions.create', [
            'user' => $user,
        ]);
    }

    /**
     * Validate and delete the image if it meets criteria.
     */
    public function deleteImageAfterValidation(string $path): void
    {
        if (! $this->validateImagePath($path)) {
            return;
        }

        $this->deleteImage($path);
    }

    /**
     * Delete images handed off from another composer when its draft is discarded.
     */
    public function discardSourceImages(): void
    {
        if ($this->imageSourceDraftKey === null || $this->imageSourceDraftKey === $this->draftKey()) {
            return;
        }

        $sourceSessionKey = 'images.'.$this->imageSourceDraftKey;
        $sourceImages = session()->get($sourceSessionKey, []);

        if (is_array($sourceImages)) {
            collect($sourceImages)
                ->filter(fn (mixed $path): bool => is_string($path) && str_starts_with($path, 'images/'))
                ->each(fn (string $path): bool => Storage::disk(self::IMAGE_DISK)->delete($path));
        }

        session()->forget($sourceSessionKey);
        $this->reset('imageSourceDraftKey');
    }

    /**
     * Return the default poll state for an additional thread post.
     *
     * @return array{isPoll: bool, options: array<int, string>, duration: int}
     */
    private function emptyThreadPoll(): array
    {
        return ['isPoll' => false, 'options' => ['', ''], 'duration' => 1];
    }

    /**
     * Validate a poll belonging to a specific composer row.
     *
     * @param  array<string, mixed>  $poll
     * @return array<int, string>|null
     */
    private function validatedPollOptions(array $poll, string $attribute): ?array
    {
        if (! ($poll['isPoll'] ?? false)) {
            return null;
        }

        $durationValue = $poll['duration'] ?? null;
        $duration = is_int($durationValue)
            ? $durationValue
            : (is_numeric($durationValue) ? (int) $durationValue : 0);
        if ($duration < 1 || $duration > 7) {
            $this->addError("{$attribute}.duration", 'Poll duration must be between 1 and 7 days.');

            return null;
        }

        $options = array_values(array_map(
            static fn (mixed $option): string => is_string($option) ? $option : '',
            is_array($poll['options'] ?? null) ? $poll['options'] : [],
        ));

        if (count($options) < 2 || count($options) > 4 || in_array('', array_map(mb_trim(...), $options), true)) {
            $this->addError("{$attribute}.options", 'Polls must have 2 to 4 non-empty options.');

            return null;
        }

        foreach ($options as $option) {
            if (mb_strlen($option) > 40) {
                $this->addError("{$attribute}.options", 'Poll options cannot exceed 40 characters.');

                return null;
            }
        }

        return $options;
    }

    /**
     * Validate if the image path is eligible for deletion.
     */
    private function validateImagePath(string $path): bool
    {
        $images = $this->getSessionImages();

        return in_array($path, $images, true) && $this->isValidImageFile($path);
    }

    /**
     * Check if the path exists and is a valid image file.
     */
    private function isValidImageFile(string $path): bool
    {
        if (! Storage::disk(self::IMAGE_DISK)->exists($path)) {
            return false;
        }

        $imageContent = Storage::disk(self::IMAGE_DISK)->get($path) ?: '';

        return @getimagesizefromstring($imageContent) !== false;
    }

    /**
     * Optimize the images.
     */
    private function optimizeImage(UploadedFile $image): string|false
    {
        $today = today()->format('Y-m-d');

        $imagePath = 'images/'.$today;

        if ($image->getMimeType() === 'image/gif') {
            return $image->store(
                $imagePath, [
                    'disk' => self::IMAGE_DISK,
                    'visibility' => 'public',
                ]
            );
        }

        $resizer = $this->resizer()->read($image)
            ->scaleDown(750, 750);

        $imagePath .= '/'.$image->hashName();

        return Storage::disk(self::IMAGE_DISK)->put(
            $imagePath,
            $resizer->encodeByExtension(
                $image->getClientOriginalExtension(),
                quality: 80
            )->toFilePointer(),
            ['visibility' => 'public'],
        ) ? $imagePath : false;
    }

    /**
     * Handle the image deletes.
     */
    private function deleteImage(string $path): void
    {
        if (! str_starts_with($path, 'images/')) {
            return;
        }

        Storage::disk(self::IMAGE_DISK)->delete($path);
        $this->cleanSession($path);
    }

    /**
     * Handle the image uploads.
     */
    private function uploadImages(): void
    {
        $sessionKey = 'images.'.$this->draftKey();

        collect($this->images)->each(function (UploadedFile $image) use ($sessionKey): void {

            $path = $this->optimizeImage($image);

            if ($path) {
                session()->push($sessionKey, $path);

                $this->dispatch(
                    'image.uploaded',
                    path: Storage::disk(self::IMAGE_DISK)->url($path),
                    originalName: $image->getClientOriginalName()
                );
            } else { // @codeCoverageIgnoreStart
                $this->addError('images', 'The image could not be uploaded.');
                $this->dispatch('notification.created', message: 'The image could not be uploaded.');
            } // @codeCoverageIgnoreEnd
        });

        $this->reset('images');
    }

    /**
     * Clean the session of the given image path.
     */
    private function cleanSession(string $path): void
    {
        $sessionKey = 'images.'.$this->draftKey();

        $remainingImages = collect($this->getSessionImages())
            ->reject(fn (string $imagePath): bool => $imagePath === $path);

        session()->put($sessionKey, $remainingImages->toArray());
    }

    /**
     * Get the validation rules for storing.
     *
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        $rules = [
            'anonymously' => ['boolean', Rule::excludeIf($this->isSharingUpdate)],
            'content' => ['required', 'string', 'min:1', 'max:'.$this->maxContentLength, new NoBlankCharacters],
        ];

        if ($this->canThread) {
            $rules['threadPosts'] = ['array', 'max:'.(self::MAX_THREAD_POSTS - 1)];
            $rules['threadPosts.*'] = [
                'nullable',
                'string',
                'min:1',
                'max:'.$this->maxContentLength,
                new NoBlankCharacters,
            ];
        }

        return $rules;
    }

    /**
     * Delete any unused images.
     */
    private function deleteUnusedImages(): void
    {
        $publishedContent = implode("\n", [$this->content, ...$this->threadPosts]);

        collect($this->getSessionImages())
            ->reject(fn (string $path): bool => str_contains($publishedContent, $path))
            ->each(fn (string $path): ?bool => $this->deleteImage($path));

        session()->forget('images.'.$this->draftKey());
    }

    /**
     * Move images handed off by another composer into this draft's session.
     */
    private function transferImagesFromSourceDraft(): void
    {
        if ($this->imageSourceDraftKey === null || $this->imageSourceDraftKey === $this->draftKey()) {
            return;
        }

        $sourceSessionKey = 'images.'.$this->imageSourceDraftKey;
        $sourceImages = session()->get($sourceSessionKey, []);

        if (! is_array($sourceImages) || $sourceImages === []) {
            return;
        }

        /** @var array<int, mixed> $sourceImages */
        session()->put('images.'.$this->draftKey(), collect($this->getSessionImages())
            ->merge($sourceImages)
            ->filter(fn (mixed $path): bool => is_string($path))
            ->unique()
            ->values()
            ->all());
        session()->forget($sourceSessionKey);
    }

    /**
     * Get the session images.
     *
     * @return array<int, string>
     */
    private function getSessionImages(): array
    {
        /** @var array<int, string> $images */
        $images = session()->get('images.'.$this->draftKey(), []);

        return $images;
    }

    /**
     * Creates a new image resizer.
     */
    private function resizer(): ImageManager
    {
        return new ImageManager(
            new Drivers\Imagick\Driver(),
            strip: true,
        );
    }
}
