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
                    ->filter(fn ($value): bool => $value !== '');
            }
        } catch (ConnectionException) {
            // Catch but not capture the exception
        }

        return $data;
    }

    /**
     * Fetch the oEmbed data for a given URL.
     *
     * @return Collection<string, string>
     */
    private function fetchOEmbed(string $service): Collection
    {
        $data = collect();

        try {
            $response = Http::get(
                $service.'?url='.urlencode($this->url).'&maxwidth=446&maxheight=251&theme=dark&hide_thread=true&omit_script=true'
            );

            if ($response->ok()) {
                $data = collect((array) $response->json())
                    ->filter(fn ($value): bool => $value !== '');
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
                    ->map(fn ($name): string => $meta->getAttribute($name))
                    ->filter(fn ($attribute): bool => in_array(explode(':', (string) $attribute)[0], $interested_in))
                    ->each(function ($attribute) use ($data, $meta): void {
                        $key = explode(':', $attribute)[1];
                        if (! $data->has($key)) {
                            $data->put($key, $meta->getAttribute('content'));
                        }
                    });
            }
        }

        if ($data->has('site_name') && $data->get('site_name') === 'X (formerly Twitter)') {
            $x = $this->fetchOEmbed(service: 'https://publish.twitter.com/oembed');
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
            $youtube = $this->fetchOEmbed(service: 'https://www.youtube.com/oembed');
            if ($youtube->isNotEmpty()) {
                foreach ($youtube as $key => $value) {
                    $data->put($key, $value);
                }
            }
        }

        return $data->unique();
    }
}
