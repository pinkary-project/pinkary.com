<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final readonly class MetaData
{
    /**
     * Fetch the Open Graph data for a given URL.
     */
    public function __construct(private string $url)
    {
        //
    }

    /**
     * Fetch the parsed meta-data for the URL
     *
     * @return Collection<string, string>
     */
    public function fetch(): Collection
    {
        /** @var Collection<string, string> $cachedData */
        $cachedData = Cache::remember(
            Str::of($this->url)->slug()->prepend('preview_')->value(),
            now()->addYear(),
            fn (): Collection => $this->getData()
        );

        return $cachedData;
    }

    /**
     * Get the meta-data for a given URL.
     *
     * @return Collection<string, string>
     */
    private function getData(): Collection
    {
        $data = collect();

        try {
            $response = Http::get($this->url);

            if ($response->ok()) {
                $data = $this->parse($response->body())
                    ->filter(fn (string $value): bool => $value !== '');
            }
        } catch (ConnectionException) {
            // Catch but not capture the exception
        }

        return $data;
    }

    /**
     * Fetch the oEmbed data for a given URL.
     *
     * @param  array<string, string>  $options
     * @return Collection<string, string>
     */
    private function fetchOEmbed(string $service, array $options): Collection
    {
        $data = collect();

        try {
            $response = Http::get(
                url: $service.'?url='.urlencode($this->url).'&'.http_build_query($options)
            );

            if ($response->ok()) {
                /** @var Collection<string, string|null> $data */
                $data = $response->collect();
                $data = $data
                    ->filter(fn (?string $value): bool => (string) $value !== '');
            }
        } catch (ConnectionException) {
            // Catch but not capture the exception
        }

        return $data;
    }

    /**
     * Parse the response body for MetaData.
     *
     * @return Collection<string, string>
     */
    private function parse(string $content): Collection
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $interested_in = ['og', 'twitter'];
        $allowed = ['title', 'description', 'keywords', 'image', 'site_name', 'url', 'type'];
        $data = collect();
        $metas = $doc->getElementsByTagName('meta');

        if ($metas->count() > 0) {
            foreach ($metas as $meta) {
                if (mb_strtolower($meta->getAttribute('name')) === 'title') {
                    $data->put('title', $meta->getAttribute('content'));
                }

                if (mb_strtolower($meta->getAttribute('name')) === 'description') {
                    $data->put('description', $meta->getAttribute('content'));
                }

                if (mb_strtolower($meta->getAttribute('name')) === 'keywords') {
                    $data->put('keywords', $meta->getAttribute('content'));
                }

                collect(['name', 'property'])
                    ->map(fn (string $name): string => $meta->getAttribute($name))
                    ->filter(fn (string $attribute): bool => in_array(explode(':', $attribute)[0], $interested_in))
                    ->each(function (string $attribute) use ($data, $allowed, $meta): void {
                        $key = explode(':', $attribute)[1];
                        if (! $data->has($key) && in_array($key, $allowed, true)) {
                            $data->put($key, $meta->getAttribute('content'));
                        }
                    });
            }
        }

        if ($data->has('site_name') && $data->get('site_name') === 'X (formerly Twitter)') {
            $x = $this->fetchOEmbed(
                service: 'https://publish.twitter.com/oembed',
                options: [
                    'dnt' => 'true',
                    'omit_script' => 'true',
                    'hide_thread' => 'true',
                    'maxwidth' => '446',
                    'maxheight' => '251',
                ]);
            if ($x->isNotEmpty()) {
                foreach ($x as $key => $value) {
                    $data->put($key, $value);
                }
            }
        }

        if ($data->has('site_name') && $data->get('site_name') === 'Vimeo') {
            $vimeo = $this->fetchOEmbed(
                service: 'https://vimeo.com/api/oembed.json',
                options: [
                    'maxwidth' => '446',
                    'maxheight' => '251',
                ]
            );
            if ($vimeo->isNotEmpty()) {
                foreach ($vimeo as $key => $value) {
                    $data->put($key, $value);
                }
            }
        }

        if ($data->has('site_name') && $data->get('site_name') === 'YouTube') {
            $youtube = $this->fetchOEmbed(
                service: 'https://www.youtube.com/oembed',
                options: [
                    'maxwidth' => '446',
                    'maxheight' => '251',
                ]);

            if ($youtube->isNotEmpty()) {
                foreach ($youtube as $key => $value) {
                    $data->put($key, $value);
                }
            }
        }

        return $data->unique();
    }
}
