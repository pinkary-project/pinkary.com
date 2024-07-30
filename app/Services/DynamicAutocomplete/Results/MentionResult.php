<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete\Results;

use App\Contracts\Services\AutocompleteResult;

final readonly class MentionResult implements AutocompleteResult
{
    use ImplementsDynamicAutocompleteResult;

    /**
     * Construct the result.
     */
    public function __construct(
        public string|int $id,
        public string $avatarSrc,
        public string $name,
        public string $username,
        public string $replacement,
        public bool $isFollowedByUser,
        public bool $isVerified,
        public bool $isCompanyVerified,
        public string $view = 'components.autocomplete.mention-item'
    ) {
        //
    }
}
