<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreImageRequest;
use App\Services\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

final readonly class ImageController
{
    /** Store the uploaded images and hand back their public URLs. */
    public function store(StoreImageRequest $request, ImageProcessor $imageProcessor): JsonResponse
    {
        /** @var array<int, UploadedFile> $images */
        $images = $request->file('images');

        $stored = [];

        foreach ($images as $image) {
            $path = $imageProcessor->process($image);

            if ($path === null) {
                abort(422, 'The image could not be uploaded.');
            }

            $stored[] = [
                // Store disk-relative paths in markdown; use absolute URLs only for previews.
                'path' => $path,
                'url' => $imageProcessor->url($path),
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
            ];
        }

        return response()->json([
            'data' => $stored,
            'meta' => ['count' => count($stored)],
        ], 201);
    }
}
