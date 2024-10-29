<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final readonly class MetaData
{
    /**
     * The Open Graph data.
     *
     * @var Collection<string, string>
     */
    private Collection $data;

    /**
     * Fetch the Open Graph data for a given URL.
     */
    public function __construct(private string $url)
    {
        /** @var Collection<string, string> $cachedData */
        $cachedData = Cache::remember(
            Str::of($url)->slug()->prepend('preview_')->value(),
            now()->addYear(),
            fn (): Collection => $this->getData()
        );

        $this->data = $cachedData;
    }

    /**
     * Fetch the parsed meta-data for a given URL.
     *
     * @return Collection<string, string>
     */
    public static function fetch(string $url): Collection
    {
        return (new self($url))->data;
    }

    /**
     * Get the meta-data for a given URL.
     *
     * @return Collection<string, string>
     */
    public function getData(): Collection
    {
        $data = collect();

        try {
            $response = Http::get($this->url);

            if ($response->ok()) {
                // TODO: add unit test for this service
                $data = $this->parse($response->body())->filter(fn ($value) => $value !== '');
            }
        } catch (Exception) {
            // Do nothing,
        }

        return $data;
    }

    /**
     * Fetch Twitter oEmbed data for a given tweet URL.
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
                $data = collect((array) $response->json())->filter(fn ($value) => $value !== '');
            }
        } catch (Exception) {
            // Do nothing,
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
                // basic meta tags
                if (mb_strtolower($meta->getAttribute('name')) === 'title') {
                    $data->put('title', $meta->getAttribute('content'));
                }

                if (mb_strtolower($meta->getAttribute('name')) === 'description') {
                    $data->put('description', $meta->getAttribute('content'));
                }
                if (mb_strtolower($meta->getAttribute('name')) === 'keywords') {
                    $data->put('keywords', $meta->getAttribute('content'));
                }

                // og, twitter cards & other meta tags
                collect(['name', 'property'])
                    ->map(fn ($name) => $meta->getAttribute($name))
                    ->filter(fn ($attribute) => in_array(explode(':', $attribute)[0], $interested_in))
                    ->each(function ($attribute) use ($data, $meta) {
                        $key = explode(':', $attribute)[1];
                        if (! $data->has($key)) {
                            $data->put($key, $meta->getAttribute('content'));
                        }
                    });
            }
        }

        // fetch oEmbed data for X / Twitter
        if ($data->has('site_name') && $data->get('site_name') === 'X (formerly Twitter)') {
            $x = $this->fetchOEmbed(service: 'https://publish.twitter.com/oembed');
            if ($x->isNotEmpty()) {
                foreach ($x as $key => $value) {
                    $data->put($key, $value);
                }
            }
        }

        // fetch oEmbed data for YouTube
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
