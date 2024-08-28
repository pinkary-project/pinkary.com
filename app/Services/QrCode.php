<?php

declare(strict_types=1);

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\HtmlString;
use Imagick;

final class QrCode
{
    private readonly Rgb $backgroundColor;

    private readonly Rgb $foregroundColor;

    private readonly string $iconPath;

    private int $iconSize = 100;

    public function __construct()
    {
        $this->backgroundColor = new Rgb(3, 7, 18);
        $this->foregroundColor = new Rgb(236, 72, 153);
        $this->iconPath = public_path('img/ico.png');
    }

    /**
     * Generate a QR code for the given content.
     */
    public function generate(string $content): HtmlString
    {
        $qrCodeBinary = $this->createQrCodeData($content);
        $qrCodeImage = $this->createQrCodeImage($qrCodeBinary);

        $this->addIconToQrCode($qrCodeImage);

        return new HtmlString($qrCodeImage->getImageBlob());
    }

    private function createQrCodeData(string $content): string
    {
        return (new Writer(
            renderer: new ImageRenderer(
                rendererStyle: new RendererStyle(
                    size: 512,
                    margin: 0,
                    fill: Fill::withForegroundColor($this->backgroundColor, $this->foregroundColor, EyeFill::inherit(), EyeFill::inherit(), EyeFill::inherit())
                ),
                imageBackEnd: new ImagickImageBackEnd()
            )
        ))->writeString(
            content: $content,
            ecLevel: ErrorCorrectionLevel::M()
        );
    }

    private function createQrCodeImage(string $qrCodeBinary): Imagick
    {
        $qrCodeImage = new Imagick();
        $qrCodeImage->readImageBlob($qrCodeBinary);

        return $qrCodeImage;
    }

    private function addIconToQrCode(Imagick $qrCodeImage): void
    {
        $icon = new Imagick($this->iconPath);

        $icon->resizeImage($this->iconSize, $this->iconSize, Imagick::FILTER_UNDEFINED, 1);

        $x = ($qrCodeImage->getImageWidth() - $this->iconSize) / 2;
        $y = ($qrCodeImage->getImageHeight() - $this->iconSize) / 2;

        $qrCodeImage->compositeImage($icon, Imagick::COMPOSITE_OVER, (int) $x, (int) $y);
    }
}
