<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;
use Illuminate\Support\Str;

final readonly class LinkProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/(<(a|code|pre)\s+[^>]*>.*?<\/\2>)|(?<!src=")((https?:\/\/)?((localhost)|((?:\d{1,3}\.){3}\d{1,3})|[\w\-._@:%\+~#=]{1,256}(\.[a-zA-Z]{2,})+)(:\d+)?(\/[\w\-._@:%\+~#=\/]*)?(\?[\w\-._@:%\+~#=\/&]*)?)(?<!\.)((?![^<]*>|[^<>]*<\/))/is',
            function (array $matches): string {
                if ($matches[1] !== '') {
                    return $matches[1];
                }

                $humanUrl = Str::of($matches[0])
                    ->replaceMatches('/^https?:\/\//', '')
                    ->rtrim('/')
                    ->toString();

                $isMail = (bool) preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $humanUrl);
                $isHttp = Str::startsWith($matches[0], ['http://', 'https://']);

                if ((! $isMail) && (! $isHttp)) {
                    return $matches[0];
                }

                $url = $isHttp ? $matches[0] : 'https://'.$matches[0];

                $url = $isMail ? 'mailto:'.$humanUrl : $url;

                return '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="'.$url.'">'.$humanUrl.'</a>';
            },
            str_replace('&amp;', '&', $content)
        );
    }
}
