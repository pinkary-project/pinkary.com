<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class HomeExplorer extends Component
{
    /**
     * The current section.
     */
    #[Locked]
    public string $currentSection;

    /**
     * The sections for the explorer component.
     *
     * @var array<string, array<string, string>>
     */
    #[Locked]
    public array $sections = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->setSections();

        $this->currentSection = type(request()->route('section'))->asString();

        if ($this->sectionTitle() === '') {
            $this->redirectRoute('welcome');
        }
    }

    /**
     * Get the title for the current section.
     */
    #[Computed('section')]
    public function sectionTitle(): string
    {
        return $this->sections[$this->currentSection]['title'] ?? '';
    }

    /**
     * Set the sections for the explorer component.
     */
    public function setSections(): void
    {
        $this->sections = [
            'feed' => [
                'title' => __('Recent Questions'),
                'label' => __('Feed'),
                'icon' => 'icons.home',
            ],
            'for-you' => [
                'title' => __('Questions you might like'),
                'label' => __('For you'),
                'icon' => 'icons.smile',
            ],
            'trending' => [
                'title' => __('Trending Questions'),
                'label' => __('Trending'),
                'icon' => 'icons.trending-solid',
            ],
            'users' => [
                'title' => __('Users'),
                'label' => __('Users'),
                'icon' => 'icons.users',
            ],
        ];
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.home-explorer');
    }
}
