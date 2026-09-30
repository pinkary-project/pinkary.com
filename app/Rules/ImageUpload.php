<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

/**
 * The composer image rules, in one place.
 *
 * The web composer and the API both accept post images, and before this they
 * were described separately: the web's limits lived inline in
 * Livewire\Questions\Create::runImageValidation(), and the API had no
 * description at all because it had no upload surface. Two copies is how the
 * next one drifts, so both call rules() now and there is nothing left to keep
 * in step by hand.
 */
final readonly class ImageUpload implements ValidationRule
{
    /**
     * Largest accepted file, in kilobytes.
     */
    public const int MAX_KILOBYTES = 8192;

    /**
     * Largest accepted width, in pixels.
     */
    public const int MAX_WIDTH = 4000;

    /**
     * Largest accepted height, in pixels.
     */
    public const int MAX_HEIGHT = 4000;

    /**
     * Narrowest accepted width-to-height ratio (2/5), matching the web.
     */
    public const float MIN_ASPECT_RATIO = 0.4;

    /**
     * Accepted image types.
     *
     * @var array<int, string>
     */
    public const array TYPES = ['jpeg', 'png', 'gif', 'webp', 'jpg'];

    /**
     * Images allowed in a single post, matching the web composer's cap.
     */
    public const int MAX_PER_POST = 3;

    /**
     * The rule chain every composer image must pass.
     *
     * `bail` matters more than it looks: the File rule expands into image,
     * mimes, max and dimensions, so a single PDF used to produce four
     * complaints at once -- three of them about a file that is not an image
     * at all. One message per file is the difference between "that is not an
     * image" and a wall of text nobody can act on.
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
     * The messages for rules(), keyed by rule name so they apply whatever the
     * field is called.
     *
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
     * Validate the value of the given attribute.
     *
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
