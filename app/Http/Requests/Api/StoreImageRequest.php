<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Rules\ImageUpload;
use App\Rules\MaxUploads;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreImageRequest extends FormRequest
{
    /** Only a signed-in user may attach images. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', new MaxUploads(ImageUpload::MAX_PER_POST)],
            'images.*' => ImageUpload::rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ImageUpload::messages();
    }
}
