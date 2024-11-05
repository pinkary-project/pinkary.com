<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MetaData;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Support\Str;

final readonly class OpenGraphController
{
    /**
     * Display the OpenGraph Validator and Preview page.
     */
    public function index(): View
    {
        return view('opengraph.validator');
    }

    /**
     * Validate the input and return OpenGraph metadata.
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:url,html',
            'input' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = $request->input('type');
        $input = $request->input('input');

        if ($type === 'url') {
            $metadata = (new MetaData($input))->fetch();
        } else {
            // Handle raw HTML input
            $metadata = $this->extractMetaFromHtml($input);
        }

        if ($metadata->isEmpty()) {
            return response()->json(['errors' => ['input' => ['No valid OpenGraph data found.']]], 422);
        }

        return response()->json(['metadata' => $metadata]);
    }

    /**
     * Extract OpenGraph metadata from raw HTML.
     */
    private function extractMetaFromHtml(string $html): \Illuminate\Support\Collection
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $metaTags = $dom->getElementsByTagName('meta');
        $metadata = collect();

        foreach ($metaTags as $meta) {
            $property = $meta->getAttribute('property');
            $content = $meta->getAttribute('content');

            if (Str::startsWith($property, 'og:') && $content) {
                $key = Str::after($property, 'og:');
                $metadata->put($key, $content);
            }
        }

        return $metadata;
    }
}
