<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface ParsableContentProvider
{
    /**
     * Parse the given "parsable" content.
     */
    public function parse(string $content): string;
}
