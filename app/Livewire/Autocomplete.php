<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\Autocomplete as AutocompleteService;
use App\Services\Autocomplete\Result;
use App\Services\Autocomplete\Types\Type;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read array<string, array<string, string>> $autocompleteTypes
 * @property-read Collection<int, Result> $autocompleteResults
 */
final class Autocomplete extends Component
{
    /**
     * An array of matched type aliases (like ["mentions", ...]).
     *
     * @var array<int, string>
     */
    public array $matchedTypes = [];

    /**
     * The matched term to search.
     */
    public string $query = '';

    /**
     * The autocomplete service.
     */
    private AutocompleteService $autocompleteService;

    /**
     * Boot the component.
     */
    public function boot(AutocompleteService $service): void
    {
        $this->autocompleteService = $service;
    }

    /**
     * Get the available autocomplete types.
     *
     * @return array<string, array<string, string>>
     */
    #[Computed]
    public function autocompleteTypes(): array
    {
        return collect($this->autocompleteService::types())
            /** @param class-string<Type> $type */
            ->map(fn (string $type): array => (new $type)->toArray())
            ->all();
    }

    /**
     * Set the required search properties on the component.
     *
     * @param  array<int, string>  $matchedTypes
     */
    public function setAutocompleteSearchParams(array $matchedTypes, string $query): void
    {
        $this->matchedTypes = array_intersect($matchedTypes, array_keys($this->autocompleteTypes));
        $this->query = $this->matchedTypes === [] ? '' : $query;
    }

    /**
     * Get the autocomplete results (aka options) for the matched types
     * and search query previously set on the component.
     *
     * @return Collection<int, Result>
     */
    #[Computed]
    public function autocompleteResults(): Collection
    {
        // @phpstan-ignore-next-line
        return collect($this->matchedTypes)
            ->map(fn (string $typeAlias): Collection => $this->autocompleteService
                ->search($typeAlias, $this->query)
            )
            ->flatten(1);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.autocomplete');
    }
}
