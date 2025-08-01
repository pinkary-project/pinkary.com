<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use Exception;
use Highlight\Highlighter;

final readonly class CodeProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/```(?<language>[a-z]+)?\s*\n(?<code>.*?)\n```/s',
            function (array $matches): string {
                $code = $matches['code'];
                $language = empty($matches['language'])
                    ? 'plaintext'
                    : mb_trim($matches['language']);

                $highlighter = new Highlighter();

                $code = htmlspecialchars_decode($code, ENT_QUOTES);

                try {
                    $highlighted = $highlighter->highlight($language, $code);
                } catch (Exception) { // @codeCoverageIgnoreStart
                    $highlighted = $highlighter->highlight('plaintext', $code);
                } // @codeCoverageIgnoreEnd

                $highlightedCode = is_scalar($highlighted->value) ? $highlighted->value : '';
                $highlightedLanguage = is_scalar($highlighted->language) ? $highlighted->language : 'plaintext';

                return '<pre><code class="p-4 rounded-lg hljs '.$highlightedLanguage.' text-xs" style="background-color: #23262E">'.$highlightedCode.'</code></pre>';
            },
            $content
        );
    }
}
