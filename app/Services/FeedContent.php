<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

final readonly class FeedContent
{
    /**
     * Separate media from already-parsed post HTML without fetching metadata.
     *
     * @return array{html: string, images: list<array{src: string, alt: string}>, preview: string}
     */
    public function parse(string $html): array
    {
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="utf-8"?><html><body><div id="feed-content">'.$html.'</div></body></html>', LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $root = $document->getElementById('feed-content');
        assert($root instanceof DOMElement);

        $media = new DOMXPath($document)->query('.//div[@id="link-preview-card"] | .//img[not(ancestor::pre) and not(ancestor::code) and not(ancestor::div[@id="link-preview-card"])]', $root);
        assert($media !== false);
        $images = [];
        $preview = '';

        foreach ($media as $node) {
            assert($node instanceof DOMElement);

            if ($node->tagName === 'img') {
                $src = $node->getAttribute('src');

                if ($src !== '' && ! in_array($src, array_column($images, 'src'), true)) {
                    $images[] = ['src' => $src, 'alt' => $node->getAttribute('alt') ?: 'Post image'];
                }
            } elseif ($preview === '') {
                $preview = (string) $document->saveHTML($node);
            }

            $node->parentNode?->removeChild($node);
        }

        $text = '';

        foreach ($root->childNodes as $node) {
            $text .= $document->saveHTML($node);
        }

        return ['html' => $text, 'images' => $images, 'preview' => $images === [] ? $preview : ''];
    }
}
