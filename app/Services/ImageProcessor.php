<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers;
use Intervention\Image\ImageManager;

final readonly class ImageProcessor
{
    /**
     * The disk used for public images.
     */
    public const ?string DISK = null;

    /**
     * Create a new image processor.
     */
    public function __construct(
        private FilesystemManager $storage,
    ) {}

    /**
     * Process and store an uploaded image.
     */
    public function process(UploadedFile $image): ?string
    {
        $imagePath = 'images/'.today()->format('Y-m-d');

        if ($image->getMimeType() === 'image/gif') {
            $path = $image->store(
                $imagePath,
                [
                    'disk' => self::DISK,
                    'visibility' => 'public',
                ],
            );

            return is_string($path) ? $path : null;
        }

        $resizedImage = $this->manager()
            ->read($image)
            ->scaleDown(750, 750);

        $imagePath .= '/'.$image->hashName();

        $stored = $this->disk()->put(
            $imagePath,
            $resizedImage->encodeByExtension(
                $image->extension(),
                quality: 80,
            )->toFilePointer(),
            ['visibility' => 'public'],
        );

        return $stored ? $imagePath : null;
    }

    /**
     * Get the public URL for an image path.
     */
    public function url(string $path): string
    {
        return $this->disk()->url($path);
    }

    /**
     * Determine whether the stored path contains a valid image.
     */
    public function isValid(string $path): bool
    {
        if (! $this->disk()->exists($path)) {
            return false;
        }

        $imageContent = $this->disk()->get($path) ?: '';

        return @getimagesizefromstring($imageContent) !== false;
    }

    /**
     * Delete a public image path.
     */
    public function delete(string $path): bool
    {
        if (! Str::startsWith($path, 'images/')) {
            return false;
        }

        return $this->disk()->delete($path);
    }

    /**
     * Get the public storage disk.
     */
    private function disk(): Filesystem
    {
        return $this->storage->disk(self::DISK);
    }

    /**
     * Create the image manager used for processing uploads.
     */
    private function manager(): ImageManager
    {
        return new ImageManager(
            new Drivers\Imagick\Driver(),
            strip: true,
        );
    }
}
