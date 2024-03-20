<?php

declare(strict_types=1);

namespace App\Services\ParsableContentProviders;

use App\Contracts\ParsableContentProvider;

final readonly class LinkProviderParsable implements ParsableContentProvider
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $content): string
    {
        return (string) preg_replace_callback(
            '/((https?:\/\/)?[\w\-._@:%\+~#=]{1,256}\.[a-z]{2,4}\b(\/[\w\-._@:%\+~#=\/]*)?)/i',
            function (array $matches): string {
                $url = preg_match('/^https?:\/\//', $matches[0]) ? $matches[0] : 'https://'.$matches[0];
                $humanUrl = (string) preg_replace('/^https?:\/\//', '', $matches[0]);

                if (mb_substr($humanUrl, -1) === '/') {
                    $humanUrl = mb_substr($humanUrl, 0, -1);
                }

                return '<a class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="'.$url.'">'.$humanUrl.'</a>';
            },
            $content
        );
    }
}
