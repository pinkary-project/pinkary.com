<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\Services\ParsableContentProvider;

final readonly class LinkProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/((https?:\/\/)?((localhost)|((?:\d{1,3}\.){3}\d{1,3})|[\w\-._@:%\+~#=]{1,256}(\.[a-zA-Z]{2,})+)(:\d+)?(\/[\w\-._@:%\+~#=\/]*)?(\?[\w\-._@:%\+~#=\/&]*)?)(?<!\.)/',
            function (array $matches): string {
                $url = preg_match('/^https?:\/\//', $matches[0]) ? $matches[0] : 'https://'.$matches[0];
                $humanUrl = (string) preg_replace('/^https?:\/\//', '', $matches[0]);
                $isMail = preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $humanUrl);

                if (mb_substr($humanUrl, -1) === '/') {
                    $humanUrl = mb_substr($humanUrl, 0, -1);
                }

                $url = $isMail ? 'mailto:'.$humanUrl : $url;

                return '<a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="'.$url.'">'.$humanUrl.'</a>';
            },
            str_replace('&amp;', '&', $content)
        );
    }
}
