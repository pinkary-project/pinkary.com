<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Question;
use App\Models\User;
use App\Rules\MaxUploads;
use App\Rules\NoBlankCharacters;
use Closure;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

/**
 * @property-read bool $isSharingUpdate
 * @property-read int $maxContentLength
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
     * The component's content.
     */
    public string $content = '';

    /**
     * Uploaded images.
     *
     * @var array<int, UploadedFile>
     */
    public array $images = [];

    /**
     * The component's anonymously state.
     */
    public bool $anonymously = true;

    /**
     * Whether this is a poll.
     */
    public bool $isPoll = false;

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
        return filled($this->parentId)
            ? "reply_{$this->parentId}"
            : 'post_new';
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

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subMinute())->count() >= 3) {
            $this->addError('content', 'You can only send 3 questions per minute.');

            return;
        }

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subDay())->count() > 30) {
            $this->addError('content', 'You can only send 30 questions per day.');

            return;
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'anonymously' => ['boolean', Rule::excludeIf($this->isSharingUpdate)],
            'content' => ['required', 'string', 'min: 3', 'max:'.$this->maxContentLength, new NoBlankCharacters],
        ]);

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

        if ($this->isSharingUpdate) {
            $validated['answer_created_at'] = now();
            $validated['answer'] = $validated['content'];
            $validated['content'] = '__UPDATE__';
        }

        if (filled($this->parentId)) {
            $validated['parent_id'] = $this->parentId;
            $validated['root_id'] = Question::whereKey($this->parentId)->value('root_id') ?? $this->parentId;
        }

        $question = $user->questionsSent()->create([
            ...$validated,
            'to_id' => $this->toId,
            'poll_expires_at' => $this->isPoll ? now()->addDays($this->pollDuration) : null,
        ]);

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

        $this->deleteUnusedImages();

        $this->reset(['content', 'isPoll', 'pollDuration']);
        $this->pollOptions = ['', ''];

        $this->anonymously = $user->prefers_anonymous_questions;

        $this->dispatch('question.created');

        $message = match (true) {
            filled($this->parentId) => 'Comment sent.',
            $this->isSharingUpdate => 'Update sent.',
            default => 'Question sent.'
        };

        $this->dispatch('notification.created', message: $message);

        if (filled($this->parentId)) {
            $this->js(<<<'JS'
                Livewire.navigate(window.location.href);
            JS);
        }
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
                ]
            );
        }

        $resizer = $this->resizer()->read($image)
            ->resizeDown(1000, 1000);

        $imagePath .= '/'.$image->hashName();

        return Storage::disk(self::IMAGE_DISK)->put(
            $imagePath,
            $resizer->encodeByExtension(
                $image->getClientOriginalExtension(),
                quality: 80
            )->toFilePointer()
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
        collect($this->images)->each(function (UploadedFile $image): void {

            $path = $this->optimizeImage($image);

            if ($path) {
                session()->push('images', $path);

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
        $remainingImages = collect($this->getSessionImages())
            ->reject(fn (string $imagePath): bool => $imagePath === $path);

        session()->put('images', $remainingImages->toArray());
    }

    /**
     * Delete any unused images.
     */
    private function deleteUnusedImages(): void
    {
        collect($this->getSessionImages())
            ->reject(fn (string $path): bool => str_contains($this->content, $path))
            ->each(fn (string $path): ?bool => $this->deleteImage($path));

        session()->forget('images');
    }

    /**
     * Get the session images.
     *
     * @return array<int, string>
     */
    private function getSessionImages(): array
    {
        /** @var array<int, string> $images */
        $images = session()->get('images', []);

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
