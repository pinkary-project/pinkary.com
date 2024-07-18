<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
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
     * Property to temporarily store the uploaded images.
     *
     * @var array<int, UploadedFile>
     */
    #[Validate(['images.*' => ['image', 'max:2048', 'mimes:jpg,png,jpeg,gif']])]
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
            $this->uploadImages();
        }
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
    }

    /**
     * Handle the image uploads.
     */
    public function uploadImages(): void
    {
        $this->validateOnly('images');

        collect($this->images)->each(function (UploadedFile $image): void {
            /** @var string $path */
            $today = now()->format('Y-m-d');
            $path = $image->store("images/{$today}", 'public');
            session()->push('images', $path);
            $this->dispatch(
                'image.uploaded',
                path: Storage::url($path),
                originalName: $image->getClientOriginalName()
            );
        });

        $this->reset('images');
    }

    /**
     * Handle the image deletes.
     *
     * @param  array<string, string>  $image
     */
    #[On('image.delete')]
    public function deleteImage(array $image): void
    {
        Storage::disk('public')->delete(
            ltrim($image['path'], '/storage')
        );
    }

    /**
     * Delete any unused images.
     */
    private function deleteUnusedImages(): void
    {
        /** @var array<string> $imagePaths */
        $imagePaths = session()->get('images', []);
        collect($imagePaths)
            ->reject(fn (string $path): bool => str_contains($this->content, $path))
            ->each(fn (string $path): ?bool => $this->deleteImage(['path' => $path]));
        session()->forget('images');
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

}
