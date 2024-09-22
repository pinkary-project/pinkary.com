<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\User;
use App\Rules\MaxUploads;
use App\Rules\NoBlankCharacters;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Imagick;
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
    use WithFileUploads;

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
     * The updated lifecycle hook.
     */
    public function updated(mixed $property): void
    {
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
    public function mount(Request $request): void
    {
        if (auth()->check()) {
            $user = type($request->user())->as(User::class);

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
    public function store(Request $request): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type($request->user())->as(User::class);

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

        if ($this->isSharingUpdate) {
            $validated['answer_created_at'] = now();
            $validated['answer'] = $validated['content'];
            $validated['content'] = '__UPDATE__';
        }

        if (filled($this->parentId)) {
            $validated['parent_id'] = $this->parentId;
            $validated['root_id'] = Question::whereKey($this->parentId)->value('root_id') ?? $this->parentId;
        }

        $user->questionsSent()->create([
            ...$validated,
            'to_id' => $this->toId,
        ]);

        $this->deleteUnusedImages();

        $this->reset(['content']);

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
     * Handle the image uploads.
     */
    public function uploadImages(): void
    {
        collect($this->images)->each(function (UploadedFile $image): void {
            $today = now()->format('Y-m-d');

            /** @var string $path */
            $path = $image->store("images/{$today}", 'public');
            $this->optimizeImage($path);

            if ($path) {
                session()->push('images', $path);

                $this->dispatch(
                    'image.uploaded',
                    path: Storage::url($path),
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
     * Optimize the images.
     */
    public function optimizeImage(string $path): void
    {
        $imagePath = Storage::disk('public')->path($path);
        $imagick = new Imagick($imagePath);

        if ($imagick->getNumberImages() > 1) {
            $imagick = $imagick->coalesceImages();

            foreach ($imagick as $frame) {
                $frame->resizeImage(1000, 1000, Imagick::FILTER_LANCZOS, 1, true);
                $frame->stripImage();
                $frame->setImageCompressionQuality(80);
            }

            $imagick = $imagick->deconstructImages();
            $imagick->writeImages($imagePath, true);
        } else {
            $imagick->resizeImage(1000, 1000, Imagick::FILTER_LANCZOS, 1, true);
            $imagick->stripImage();
            $imagick->setImageCompressionQuality(80);
            $imagick->writeImage($imagePath);
        }

        $imagick->clear();
        $imagick->destroy();
    }

    /**
     * Handle the image deletes.
     */
    public function deleteImage(string $path): void
    {
        Storage::disk('public')->delete($path);
        $this->cleanSession($path);
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
     * Clean the session of the given image path.
     */
    private function cleanSession(string $path): void
    {
        /** @var array<int, string> $images */
        $images = session()->get('images', []);

        $remainingImages = collect($images)
            ->reject(fn (string $imagePath): bool => $imagePath === $path);

        session()->put('images', $remainingImages->toArray());
    }

    /**
     * Delete any unused images.
     */
    private function deleteUnusedImages(): void
    {
        /** @var array<int, string> $images */
        $images = session()->get('images', []);

        collect($images)
            ->reject(fn (string $path): bool => str_contains($this->content, $path))
            ->each(fn (string $path): ?bool => $this->deleteImage($path));

        session()->forget('images');
    }
}
