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

final readonly class QrCode
{
    /**
     * The background color of the QR code.
     */
    private Rgb $backgroundColor;

    /**
     * The foreground color of the QR code.
     */
    private Rgb $foregroundColor;

    /**
     * The path to the icon that will be added to the QR code.
     */
    private string $iconPath;

    /**
     * The size of the icon to be added to the QR code.
     */
    private int $iconSize;

    /**
     * Create a new QR code service instance.
     */
    public function __construct(bool $lightMode = true)
    {
        if ($lightMode) {
            $this->backgroundColor = new Rgb(248, 250, 252);
            $this->foregroundColor = new Rgb(236, 72, 153);
        } else {
            $this->backgroundColor = new Rgb(3, 7, 18);
            $this->foregroundColor = new Rgb(236, 72, 153);
        }

        $this->iconPath = public_path('img/ico.png');
        $this->iconSize = 100;
    }

    /**
     * Generate a QR code for the given content.
     */
    public function generate(string $content): HtmlString
    {
        $qrCodeData = $this->createQrCodeData($content);
        $qrCodeImage = $this->createQrCodeImage($qrCodeData);

        $this->addIconToQrCode($qrCodeImage);

        $qrCodeImage->stripImage();

        return new HtmlString($qrCodeImage->getImageBlob());
    }

    /**
     * Create the QR code data.
     */
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

    /**
     * Create the QR code image.
     */
    private function createQrCodeImage(string $qrCodeBinary): Imagick
    {
        $qrCodeImage = new Imagick();
        $qrCodeImage->readImageBlob($qrCodeBinary);

        return $qrCodeImage;
    }

    /**
     * Add an icon to the center of the QR code.
     */
    private function addIconToQrCode(Imagick $qrCodeImage): void
    {
        $icon = new Imagick($this->iconPath);

        $icon->resizeImage($this->iconSize, $this->iconSize, Imagick::FILTER_UNDEFINED, 1);

        $x = ($qrCodeImage->getImageWidth() - $this->iconSize) / 2;
        $y = ($qrCodeImage->getImageHeight() - $this->iconSize) / 2;

        $qrCodeImage->compositeImage($icon, Imagick::COMPOSITE_OVER, (int) $x, (int) $y);
    }
}
