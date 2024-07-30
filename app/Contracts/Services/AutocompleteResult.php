<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface AutocompleteResult
{
    /**
     * Get the id of the result.
     */
    public function id(): int|string;

    /**
     * Get the autocompletion replacement for the result.
     */
    public function replacement(): string;

    /**
     * Get the view named used to display the result.
     */
    public function view(): string;
}
