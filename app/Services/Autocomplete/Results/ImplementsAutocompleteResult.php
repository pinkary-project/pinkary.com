<?php

declare(strict_types=1);

namespace App\Services\Autocomplete\Results;

trait ImplementsAutocompleteResult
{
    /**
     * {@inheritDoc}
     */
    public function id(): int|string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function replacement(): string
    {
        return $this->replacement;
    }

    /**
     * {@inheritDoc}
     */
    public function view(): string
    {
        return $this->view;
    }
}
