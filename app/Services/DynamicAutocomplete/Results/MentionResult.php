<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete\Results;

use App\Contracts\Services\DynamicAutocompleteResult;

final readonly class MentionResult implements DynamicAutocompleteResult
{
    public function __construct(
        public string|int $id,
        public string $avatar_src,
        public string $name,
        public string $username,
        public string $replacement,
        public bool $is_followed_by_user,
        public bool $is_verified,
        public bool $is_company_verified,
        public string $view = 'components.autocomplete.mention-item'
    ) {
        //
    }

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
