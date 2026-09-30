<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class SortLinksRequest extends FormRequest
{
    /** Reordering is authenticated by the route. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sort' => ['required', 'array'],
            'sort.*' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function order(): array
    {
        $sort = $this->validated('sort');

        if (! is_array($sort)) {
            return [];
        }

        // The `integer` rule accepts numeric strings, and a client sending
        // JSON like {"sort":["3","1"]} produced a validated array that
        // filtered straight down to empty -- which the caller then wrote to
        // the column as null, silently discarding the order the user had
        // just set. Cast instead of dropping.
        return array_values(array_map(
            strval(...),
            array_filter(
                $sort,
                static fn (mixed $value): bool => is_int($value) || (is_string($value) && ctype_digit($value)),
            ),
        ));
    }
}
