<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
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

            $previous = $this->adjacentContentNode($node, true);
            $next = $this->adjacentContentNode($node, false);

            if ((! $previous instanceof DOMNode || $this->isLineBreak($previous)) && $next instanceof DOMElement && $this->isLineBreak($next)) {
                $next->parentNode?->removeChild($next);
            }

            $node->parentNode?->removeChild($node);
        }

        if ($media->length > 0) {
            $first = $root->firstChild;

            while ($first instanceof DOMNode && ($this->isLineBreak($first) || $first instanceof DOMText && mb_trim($first->data) === '')) {
                $root->removeChild($first);
                $first = $root->firstChild;
            }

            $last = $root->lastChild;

            while ($last instanceof DOMNode && ($this->isLineBreak($last) || $last instanceof DOMText && mb_trim($last->data) === '')) {
                $root->removeChild($last);
                $last = $root->lastChild;
            }
        }

        $text = '';

        foreach ($root->childNodes as $node) {
            $text .= $document->saveHTML($node);
        }

        return ['html' => $text, 'images' => $images, 'preview' => $images === [] ? $preview : ''];
    }

    /**
     * Skip formatting whitespace surrounding an extracted image.
     */
    private function adjacentContentNode(DOMNode $node, bool $previous): ?DOMNode
    {
        $sibling = $previous ? $node->previousSibling : $node->nextSibling;

        while ($sibling instanceof DOMText && mb_trim($sibling->data) === '') {
            $sibling = $previous ? $sibling->previousSibling : $sibling->nextSibling;
        }

        return $sibling;
    }

    /**
     * Identify HTML line separators without touching paragraph content.
     */
    private function isLineBreak(?DOMNode $node): bool
    {
        return $node instanceof DOMElement && $node->tagName === 'br';
    }
}
