<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DynamicAutocomplete\DynamicAutocomplete as AutocompleteService;
use App\Services\DynamicAutocomplete\Results\Collection;
use App\Services\DynamicAutocomplete\Types\Type;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read array<string, array<string, string>> $autocompleteTypes
 * @property-read Collection $autocompleteResults
 */
final class DynamicAutocomplete extends Component
{
    /**
     * An array of matched type aliases (like ["mentions", ...]).
     *
     * @var string[]
     */
    public array $matchedTypes = [];

    /**
     * The matched term to search.
     */
    public string $query = '';

    #[Computed]
    public function autocompleteTypes(): array
    {
        return collect(AutocompleteService::types())
            /** @param class-string<Type> $type */
            ->map(function (string $type) {
                return $type::make()->toArray();
            })
            ->all();
    }

    public function setAutocompleteSearchParams(array $matchedTypes, string $query): void
    {
        if (blank($matchedTypes)) {
            return;
        }

        $this->matchedTypes = $matchedTypes;
        $this->query = $query;
    }

    #[Computed]
    public function autocompleteResults(): Collection
    {
        $autocompleteService = new AutocompleteService();

        return Collection::make($this->matchedTypes)
            ->map(fn (string $typeAlias) => $autocompleteService
                ->search($typeAlias, $this->query)
            )
            ->flatten(1);
    }

    public function render(): View
    {
        return view('livewire.dynamic-autocomplete');
    }
}
