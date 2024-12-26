<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use App\Services\MetaData;
use Illuminate\Support\Str;

final readonly class LinkProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        $tokens = $this->tokenize($content);

        if ($tokens === false) {
            return $content;
        }

        $processedTokens = array_map(
            fn (string $token): string => $this->processToken($token),
            $tokens);

        return implode('', $processedTokens);
    }

    /**
     * Split the content into tokens based on spaces and newlines.
     *
     * @return list<string>|false
     */
    private function tokenize(string $content): array|false
    {
        return preg_split('/(\s|<br>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Process a single token and convert valid URLs into HTML links.
     */
    private function processToken(string $token): string
    {
        $allowableAttachedCharacters = '{([<!,.?;:>)]}';

        $trimmedToken = trim($token, $allowableAttachedCharacters);

        if ($trimmedToken === '' || $trimmedToken === '0') {
            return $token;
        }

        if (filter_var($trimmedToken, FILTER_VALIDATE_EMAIL)) {
            $trimmedToken = "mailto:{$trimmedToken}";
        } elseif (! $this->isValidUrl($trimmedToken)) {
            return $token;
        }

        $humanUrl = Str::of($trimmedToken)
            ->replaceMatches('/^(https?:\/\/|mailto:)/', '')
            ->rtrim('/')
            ->toString();

        $linkHtml = "<a data-navigate-ignore=\"true\" class=\"text-blue-500 hover:underline hover:text-blue-700 cursor-pointer\" target=\"_blank\" href=\"{$trimmedToken}\">{$humanUrl}</a>";

        $service = new MetaData($trimmedToken);
        $metadata = $service->fetch();
        if ($metadata->isNotEmpty() && ($metadata->has('image') || $metadata->has('html'))) {
            $trimmedPreviewCard = trim(
                view('components.link-preview-card', [
                    'data' => $metadata,
                    'url' => $trimmedToken,
                ])->render()
            );

            $linkHtml .= $trimmedPreviewCard;
        }

        $leading = $this->getCharacters($token, $allowableAttachedCharacters, 'leading');
        $trailing = $this->getCharacters($token, $allowableAttachedCharacters, 'trailing');

        return $leading.$linkHtml.$trailing;
    }

    /**
     * Extract leading or trailing punctuation/characters from a token.
     */
    private function getCharacters(string $token, string $allowableCharacters, string $direction): string
    {
        $pattern = match ($direction) {
            'leading' => '/^(['.preg_quote($allowableCharacters, '/').']+)/',
            'trailing' => '/(['.preg_quote($allowableCharacters, '/').']+)$/',
            default => '',
        };

        if (preg_match($pattern, $token, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Validate if a token is a valid URL.
     */
    private function isValidUrl(string $token): bool
    {
        $urlComponents = parse_url($token);
        if ($urlComponents === false || ! filter_var($token, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = $urlComponents['scheme'] ?? null;
        $host = $urlComponents['host'] ?? null;
        if (! in_array($scheme, ['http', 'https'], true) || ! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        foreach (['path', 'query', 'fragment'] as $part) {
            if (isset($urlComponents[$part]) && preg_match('/[\s<>{}[\]]/', $urlComponents[$part])) {
                return false;
            }
        }

        if (isset($urlComponents['port']) && (preg_match('/^\d{1,5}$/', (string) $urlComponents['port']) === 0 || preg_match('/^\d{1,5}$/', (string) $urlComponents['port']) === false)) {
            return false;
        }

        return (bool) preg_match(
            '/((https?:\/\/)?((localhost)|((?:\d{1,3}\.){3}\d{1,3})|[\w\-._@:%+~#=]{1,256}(\.[a-zA-Z]{2,})+)(:\d+)?(\/[\w\-._@:%+~#=\/]*)?(\?[\w\-._@:%+~#=\/&]*)?)/i',
            $token
        );
    }
}
