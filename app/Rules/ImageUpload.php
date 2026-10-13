<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

final readonly class ImageUpload implements ValidationRule
{
    public const int MAX_KILOBYTES = 8192;

    public const int MAX_WIDTH = 4000;

    public const int MAX_HEIGHT = 4000;

    public const float MIN_ASPECT_RATIO = 0.4;

    /**
     * @var array<int, string>
     */
    public const array TYPES = ['jpeg', 'png', 'gif', 'webp', 'jpg'];

    public const int MAX_PER_POST = 3;

    /**
     * Bail prevents File's expanded rules from reporting multiple errors for one upload.
     *
     * @return array<int, mixed>
     */
    public static function rules(): array
    {
        return [
            'bail',

            File::image()
                ->types(self::TYPES)
                ->max(self::MAX_KILOBYTES)
                ->dimensions(
                    Rule::dimensions()->maxWidth(self::MAX_WIDTH)->maxHeight(self::MAX_HEIGHT)
                ),

            new self,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'image' => 'The file must be an image.',
            'mimes' => 'The image must be a file of type: :values.',
            'max' => 'The image may not be greater than :max kilobytes.',
            'dimensions' => 'The image must be less than :max_width x :max_height pixels.',
        ];
    }

    /**
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return; // The File rule has already reported it is not an image.
        }

        $dimensions = $value->dimensions();

        if (! is_array($dimensions)) {
            $fail('The image aspect ratio could not be determined.');

            return;
        }

        /** @var array<int, int> $dimensions */
        [$width, $height] = $dimensions;

        if ($height === 0 || ($width / $height) < self::MIN_ASPECT_RATIO) {
            $fail('The image aspect ratio must be less than 2/5.');
        }
    }
}
