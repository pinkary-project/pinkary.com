<?php

declare(strict_types=1);

namespace App\Services\Autocomplete\Results;

final readonly class AutocompleteResult
{
    /**
     * Creates a new AutocompleteResult instance.
     *
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string|int $id,
        public string $replacement,
        public string $view,
        public array $payload = [],
    ) {
        //
    }
}
