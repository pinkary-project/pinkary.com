<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Imagick;

final class ImageOptimizer
{
    /**
     * The image path.
     */
    private string $image;

    /**
     * Create a new ImageOptimizer instance.
     */
    public function __construct(
        private readonly string $path,
        private readonly int $width,
        private readonly int $height,
        private readonly int $quality,
        private readonly bool $isThumbnail,
        private ?Imagick $imagick = null,
    ) {
        $this->image = Storage::disk('public')->path($this->path);
        $this->imagick = $this->imagick ?? new Imagick($this->image);
        $this->optimizeImage();
    }

    /**
     * Static factory method to optimize an image.
     */
    public static function optimize(
        string $path,
        int $width,
        int $height,
        ?int $quality = null,
        bool $isThumbnail = false,
    ): void {
        $quality = $quality ?? ($isThumbnail ? 100 : 80);
        new self($path, $width, $height, $quality, $isThumbnail);
    }

    /**
     * Run the optimization process.
     */
    private function optimizeImage(): void
    {
        if ($this->isThumbnail) {
            $this->coverDown($this->width, $this->height);
        }

        if ($this->imagick === null) {
            return;
        }

        $this->imagick->autoOrient();

        $this->imagick->resizeImage(
            $this->width,
            $this->height,
            Imagick::FILTER_LANCZOS,
            1,
            true
        );

        $this->imagick->stripImage();

        $this->imagick->setImageCompressionQuality($this->quality);
        $this->imagick->writeImage($this->image);

        $this->imagick->clear();
        $this->imagick->destroy();
    }

    /**
     * Crop the image from the centre, while maintaining the desired aspect ratio.
     */
    private function coverDown(int $width, int $height): void
    {
        if ($this->imagick === null) {
            return;
        }
        $originalWidth = $this->imagick->getImageWidth();
        $originalHeight = $this->imagick->getImageHeight();

        $targetAspect = $width / $height;
        $originalAspect = $originalWidth / $originalHeight;

        if ($originalAspect > $targetAspect) {
            $newHeight = $originalHeight;
            $newWidth = (int) round($originalHeight * $targetAspect);
        } else {
            $newWidth = $originalWidth;
            $newHeight = (int) round($originalWidth / $targetAspect);
        }

        $x = (int) round(($originalWidth - $newWidth) / 2);
        $y = (int) round(($originalHeight - $newHeight) / 2);

        $this->imagick->cropImage($newWidth, $newHeight, $x, $y);
        $this->imagick->setImagePage($newWidth, $newHeight, 0, 0);
    }
}
