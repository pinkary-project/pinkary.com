<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreImageRequest;
use App\Services\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

/**
 * Accepts a post's images so a client is not limited to the web composer.
 *
 * The web already had this capability, reached through Livewire's
 * WithFileUploads and a session draft -- not something an API client can
 * speak. The rest of the pipeline already existed: ImageProcessor writes and
 * scales the file, ImageUpload states the limits, and ImageProviderParsable
 * turns the markdown the client writes back into an <img>. This is that same
 * path with a different front door.
 */
final readonly class ImageController
{
    /**
     * Store the uploaded images and hand back their public URLs.
     */
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
                // `url` is what a client puts in markdown; `path` is the only
                // way back to the file for a delete, as in the web's draft.
                'url' => $imageProcessor->url($path),
                'path' => $path,
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
