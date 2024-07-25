<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete\Results;

trait ImplementsDynamicAutocompleteResult
{
    public function id(): int|string
    {
        return $this->id;
    }

    public function replacement(): string
    {
        return $this->replacement;
    }

    public function view(): string
    {
        return $this->view;
    }
}
