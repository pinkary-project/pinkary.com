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
     */
    private Collection $data;

    /**
     * Fetch the Open Graph data for a given URL.
     */
    public function __construct(private string $url)
    {
        $this->data = Cache::remember(
            Str::of($url)->slug()->prepend('preview_')->value(),
            now()->addYear(),
            // NOTE: check why this data is not being cached, we are just caching a Collection in the DB & I can't see the data in the DB
            fn () => $this->getData()
        );
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
        try {
            $response = Http::get($this->url);
            if ($response->ok()) {
                // TODO: add unit test for this service
                $data = $this->parse($response->body())->filter(fn ($value) => $value !== '');
            }
        } catch (Exception) {
            $data = collect();
        }

        return $data ?? collect();
    }

    /**
     * Fetch Twitter oEmbed data for a given tweet URL.
     *
     * @return Collection<string, mixed>
     */
    private function fetchTwitterOEmbed(string $tweetUrl): Collection
    {
        $oEmbedUrl = 'https://publish.twitter.com/oembed?url='.urlencode($tweetUrl);

        $response = Http::get($oEmbedUrl);

        if ($response->ok()) {
            $data = $response->json();
        }

        return collect($data ?? []);
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

                // og & twitter meta tags
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

        // if the title is x.com, fetch twitter oEmbed & add to data
        if ($data->has('site_name') && $data->get('site_name') === 'X (formerly Twitter)') {
            $x = $this->fetchTwitterOEmbed($this->url);
            if ($x->isNotEmpty()) {
                foreach ($x as $key => $value) {
                    $data->put($key, $value);
                }
            }
        }

        return $data->unique();
    }
}
