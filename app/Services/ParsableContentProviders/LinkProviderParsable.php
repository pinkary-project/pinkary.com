<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use App\Services\MetaData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

final readonly class LinkProviderParsable implements ParsableContentProvider
{
    /**
     * The characters that can be attached to a URL.
     */
    private const string ALLOWABLE_ATTACHED_CHARACTERS = '{([<!,.?;:>)]}';

    /**
     * The maximum width of the text displayed for a link.
     */
    private const int MAX_DISPLAYED_TEXT_LENGTH = 50;

    /**
     * Regex to check for the presence of images in the content
     * (Checks both Markdown and parsed images)
     */
    private const string IMAGE_REGEX = '/!\[[^]]*]\([^)]*\)|<img[^>]+src\s*=\s*["\'][^"\'\s]+["\'][^>]*>/i';

    /**
     * {@inheritDoc}
     *
     * @throws Throwable
     */
    public function parse(string $content): string
    {
        $tokens = $this->tokenize($content);

        if ($tokens === false) {
            return $content;
        }

        if ($this->imagesExist($content)) {
            $previewMetadata = null;
            $previewIndex = null;
        } else {
            [$previewIndex, $previewMetadata] = $this->findFirstValidPreview($tokens);
        }

        $processedTokens = array_map(
            fn (string $token, int $index): string => $this
                ->parseToken(
                    token: $token,
                    showPreview: $index === $previewIndex,
                    metadata: $previewMetadata
                ),
            $tokens,
            array_keys($tokens)
        );

        return implode('', $processedTokens);
    }

    /**
     * Check to see if any images exist in the content
     */
    private function imagesExist(string $content): bool
    {
        return (bool) preg_match(self::IMAGE_REGEX, $content);
    }

    /**
     * Trim the token
     */
    private function trimToken(string $token): string
    {
        return $this->splitToken($token)[1];
    }

    /**
     * @return array{string, string, string}
     */
    private function splitToken(string $token): array
    {
        $withoutLeadingCharacters = mb_ltrim($token, self::ALLOWABLE_ATTACHED_CHARACTERS);
        $leadingCharacters = mb_substr($token, 0, mb_strlen($token) - mb_strlen($withoutLeadingCharacters));
        $url = $this->trimTrailingCharacters($withoutLeadingCharacters);
        $trailingCharacters = mb_substr($withoutLeadingCharacters, mb_strlen($url));

        return [$leadingCharacters, $url, $trailingCharacters];
    }

    private function trimTrailingCharacters(string $token): string
    {
        while ($token !== '') {
            $lastCharacter = mb_substr($token, -1);

            if ($lastCharacter === '.' && preg_match('/\.{3,}$/', $token) === 1) {
                break;
            }

            $openingCharacter = match ($lastCharacter) {
                ')' => '(',
                ']' => '[',
                '}' => '{',
                default => null,
            };

            if ($openingCharacter !== null && mb_substr_count($token, $openingCharacter) >= mb_substr_count($token, $lastCharacter)) {
                break;
            }

            if (! str_contains(self::ALLOWABLE_ATTACHED_CHARACTERS, $lastCharacter)) {
                break;
            }

            $token = mb_substr($token, 0, -1);
        }

        return $token;
    }

    /**
     * Find the first valid preview starting from the end of the content
     *
     * @param  list<string>  $tokens
     * @return array{(int|string|null), Collection<string, string>|null}
     */
    private function findFirstValidPreview(array $tokens): array
    {
        // Collect all valid URLs with their indices
        $linkIndices = [];
        foreach ($tokens as $index => $token) {
            if ($this->isValidUrl($this->trimToken($token))) {
                $linkIndices[] = $index;
            }
        }

        // If no links found, return early
        if ($linkIndices === []) {
            return [null, null];
        }

        // Start from the end and work backwards until we find a valid preview to show
        foreach (array_reverse($linkIndices) as $index) {
            $service = new MetaData(
                url: $this->trimToken($tokens[$index])
            );
            $metadata = $service->fetch();

            if ($metadata->isNotEmpty() && ($metadata->has('image') || $metadata->has('html'))) {
                return [$index, $metadata];
            }
        }

        return [null, null];
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
     *
     * @param  ?Collection<string, string>  $metadata
     *
     * @throws Throwable
     */
    private function parseToken(
        string $token,
        bool $showPreview = false,
        ?Collection $metadata = null
    ): string {
        [$leading, $trimmedToken, $trailing] = $this->splitToken($token);

        if ($trimmedToken === '' || $trimmedToken === '0') {
            return $token;
        }

        if (filter_var($trimmedToken, FILTER_VALIDATE_EMAIL)) {
            $trimmedToken = "mailto:{$trimmedToken}";
        } elseif (! $this->isValidUrl($trimmedToken)) {
            return $token;
        }

        $humanUrl = $this->displayedText(
            Str::of($trimmedToken)
                ->replaceMatches('/^(https?:\/\/|mailto:)/', '')
                ->rtrim('/')
                ->toString()
        );

        $linkHtml = "<a data-navigate-ignore=\"true\" class=\"text-blue-500 hover:underline hover:text-blue-700 cursor-pointer\" target=\"_blank\" href=\"{$trimmedToken}\">{$humanUrl}</a>";

        if ($showPreview && $metadata?->isNotEmpty()) {
            $trimmedPreviewCard = mb_trim(
                view('components.link-preview-card', [
                    'data' => $metadata,
                    'url' => $trimmedToken,
                ])->render()
            );

            $linkHtml .= $trimmedPreviewCard;
        }

        return $leading.$linkHtml.$trailing;
    }

    /**
     * Shorten the text of a link so long urls do not make a post look spammy.
     *
     * The cut may land inside an escaped entity such as "&amp;", which would
     * render as literal text, so any partial entity is dropped before the
     * ellipsis is appended.
     */
    private function displayedText(string $url): string
    {
        if (mb_strlen($url) <= self::MAX_DISPLAYED_TEXT_LENGTH) {
            return $url;
        }

        $trimmed = Str::limit($url, self::MAX_DISPLAYED_TEXT_LENGTH - 3, '');
        $trimmed = Str::replaceMatches('/(?:&[#a-zA-Z0-9]*|%(?:[a-fA-F0-9])?)$/', '', $trimmed);

        return $trimmed.'...';
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
            '/((https?:\/\/)?((localhost)|((?:\d{1,3}\.){3}\d{1,3})|[\w\-._@:%+~#=]{1,256}(\.[a-zA-Z]{2,})+)(:\d+)?(\/[\w\-._@:%+~#=\/()]*)?(\?[\w\-._@:%+~#=\/&()]*)?)/i',
            $token
        );
    }
}
