<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Services\ParsableContent;
use JsonException;

final readonly class RefreshParsedContent
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        private ParsableContent $parsableContent,
    ) {}

    /**
     * Get the parsed output for the field, refreshing the stored payload when stale.
     */
    public function handle(Question $question, string $field, string $hashKey, string $value): string
    {
        $stored = $this->stored($question, $field, $hashKey, $value);

        if (is_string($stored)) {
            return $stored;
        }

        $payload = $this->refresh($question);

        return $payload[$field] ?? $this->parsableContent->parse($value);
    }

    /**
     * Get the stored parsed output for the field, if it is fresh.
     */
    private function stored(Question $question, string $field, string $hashKey, string $value): ?string
    {
        $stored = $this->payload($question);

        if (($stored['f'] ?? null) !== $this->parsableContent->fingerprint()) {
            return null;
        }

        if (($stored[$hashKey] ?? null) !== sha1($value)) {
            return null;
        }

        $html = $stored[$field] ?? null;

        return is_string($html) ? $html : null;
    }

    /**
     * Parse the raw fields and persist the fresh payload.
     *
     * @return array{c: string|null, a: string|null, content: string|null, answer: string|null, f: string}
     */
    private function refresh(Question $question): array
    {
        $attributes = $question->getAttributes();
        $content = $attributes['content'] ?? null;
        $answer = $attributes['answer'] ?? null;

        $payload = [
            'f' => $this->parsableContent->fingerprint(),
            'c' => is_string($content) ? sha1($content) : null,
            'a' => is_string($answer) ? sha1($answer) : null,
            'content' => $this->parseRaw($content),
            'answer' => $this->parseRaw($answer),
        ];

        if ($question->exists && ! $question->isDirty(['content', 'answer'])) {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);

            $question->newQuery()->whereKey($question->getKey())->update(['parsed' => $json]);
            $question->setRawAttributes(array_merge($attributes, ['parsed' => $json]), true);
        }

        return $payload;
    }

    /**
     * Parse a raw value using the empty value semantics of the accessors.
     */
    private function parseRaw(mixed $value): ?string
    {
        if (! is_string($value) || in_array($value, ['', '0'], true)) {
            return null;
        }

        return $this->parsableContent->parse($value);
    }

    /**
     * Decode the stored parsed payload, tolerating legacy or corrupt values.
     *
     * @return array<string, mixed>
     */
    private function payload(Question $question): array
    {
        $raw = $question->getAttributes()['parsed'] ?? null;

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
