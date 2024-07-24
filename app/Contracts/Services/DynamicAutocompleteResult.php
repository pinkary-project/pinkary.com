<?php

namespace App\Contracts\Services;

interface DynamicAutocompleteResult
{
    public function id(): int|string;

    public function replacement(): string;

    public function view(): string;
}
