<?php

declare(strict_types=1);

namespace App\Livewire\Changelog;

use App\Services\GitHub;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

final class Releases extends Component
{

    /**
     * @var array<array{name: string, published_at: string, items: array<int, mixed>}>
     */
    public array $releases;

    public function mount(GitHub $github): void
    {
        $this->releases = Cache::remember('git-releases', 720, fn () => $github->getReleases());
    }

    public function render(): View
    {
        return view('livewire.releases');
    }
}
