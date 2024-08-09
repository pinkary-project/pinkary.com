<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * @phpstan-type MetaData = array{
 *     title: string,
 *     type: string,
 *     image: string,
 *     url: string,
 *     description: string,
 *     site_name: string,
 *     locale: string,
 * }
 * @phpstan-type MetaDataCollection = Collection<int, MetaData>
 */
final readonly class MetaData
{
    /**
     * The Open Graph data.
     *
     * @var MetaDataCollection
     */
    private Collection $data;

    /**
     * Fetch the Open Graph data for a given URL.
     */
    public function __construct(
        private string $url
    ) {
        $this->data = Cache::get(
            $this->url,
            fn () => $this->getData()
        );
    }

    /**
     * Get the open graph data for a given external URL.
     *
     * @return MetaDataCollection
     */
    public static function fetch(string $url): Collection
    {
        return (new self($url))->data;
    }

    /**
     * Get the Open Graph data.
     *
     * @return MetaDataCollection
     */
    public function getData(): Collection
    {
        $response = Http::get($this->url);

        if ($response->ok()) {
            $opg = $this->parse($response->body());
            //Cache::put($this->url, $opg, now()->addDay());
            dump($opg);

            return $opg;
        }

        return collect();
    }

    // ensure fb, twitter card, open graph, and oembed are all parsed,
    // so we can use them in the view to generate a preview

    /**
     * Parse the response body for MetaData.
     *
     * @return Collection<int, DOMElement>
     */
    private function parse(string $content): Collection
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($content);

        $interested_in = ['og', 'fb', 'twitter'];

        $data = collect();

        // Open graph
        $metas = $doc->getElementsByTagName('meta');
        if ($metas->length > 0) {
            dump($metas);
            for ($n = 0; $n < $metas->length; $n++) {
                $meta = $metas->item($n);

                collect(['name', 'property'])->each(function ($name) use ($meta, $interested_in, $data) {
                    $meta_bits = explode(':', $meta->getAttribute($name));
                    //dump($meta_bits);
                    if (in_array($meta_bits[0], $interested_in)) {
                        if ($data->has($meta->getAttribute($name)) && ! is_array($data->get($meta->getAttribute($name)))) {
                            $data->put($meta_bits[0], [$data->get($meta->getAttribute($name)), $meta->getAttribute('content')]);
                        } elseif ($data->has($meta->getAttribute($name)) && is_array($data->get($meta->getAttribute($name)))) {
                            $data->push($meta->getAttribute('content'));
                        } else {
                            $data->put($meta_bits[0], $meta->getAttribute('content'));
                        }
                    }
                });
            }
        }

        // OEmbed
        $metas = $doc->getElementsByTagName('link');
        if ($metas->length > 0) {
            for ($n = 0; $n < $metas->length; $n++) {
                $meta = $metas->item($n);

                if (mb_strtolower($meta->getAttribute('rel')) === 'alternate') {
                    if (mb_strtolower($meta->getAttribute('type')) === 'application/json+oembed') {
                        $data->put('oembed.json', $meta->getAttribute('href'));
                    }
                    if (mb_strtolower($meta->getAttribute('type')) === 'text/json+oembed') {
                        $data->put('oembed.json', $meta->getAttribute('href'));
                    }
                    if (mb_strtolower($meta->getAttribute('type')) === 'text/xml+oembed') {
                        $data->put('oembed.xml', $meta->getAttribute('href'));
                    }
                }
            }

            $data = $this->parseTwitterOEmbed(collect($metas), $data);
        }

        // Basics
        $basic = 'title';
        if (preg_match("#<$basic>(.*?)</$basic>#siu", $content, $matches)) {
            $data->put($basic, trim($matches[1], " \n"));
        }
        $metas = $doc->getElementsByTagName('meta');
        if ($metas->length > 0) {
            for ($n = 0; $n < $metas->length; $n++) {
                $meta = $metas->item($n);

                if (mb_strtolower($meta->getAttribute('name')) === 'description') {
                    $data->put('description', $meta->getAttribute('content'));
                }
                if (mb_strtolower($meta->getAttribute('name')) === 'keywords') {
                    $data->put('keywords', $meta->getAttribute('content'));
                }
            }
        }

        return $data;
    }

    /**
     * Parse Twitter OEmbed data.
     *
     * @param  Collection<int, DOMElement>  $metas
     * @param  Collection<string, array<int, string>>  $data
     * @return Collection<int, string>
     */
    private function parseTwitterOEmbed(Collection $metas, Collection $data): Collection
    {
        if ($data->has('oembed.jsonp')) {
            return $data;
        }

        $canonicalLinks = $metas->filter(function ($meta) {
            $canonicalLinks = collect(iterator_to_array($meta->attributes))
                ->filter(fn ($attr) => $attr->name === 'rel' && $attr->value === 'canonical');

            return $canonicalLinks->isNotEmpty();
        });

        if ($canonicalLinks->isNotEmpty()) {
            $firstCanonicalLink = $canonicalLinks->first()->getAttribute('href');

            if (! empty(trim($firstCanonicalLink)) && preg_match('#^https://(www\.|mobile\.)?twitter\.com#i', $firstCanonicalLink) === 1) {
                $data->put('oembed.jsonp', [
                    'https://publish.twitter.com/oembed?url='.$firstCanonicalLink.'&align=center',
                ]);
            }
        }

        return $data;
    }
}
