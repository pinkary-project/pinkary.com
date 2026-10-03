<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Rules\MobileTurnstile;
use App\Rules\NoBlankCharacters;
use App\Services\MobileCaptcha;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreQuestionRequest extends FormRequest
{
    /** Authorize this request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(MobileCaptcha $captcha): array
    {
        $isAsking = $this->filled('to_username') && $this->input('to_username') !== $this->user()?->username;
        $maxContent = $isAsking ? 255 : 1000;

        $rules = [
            'to_username' => ['sometimes', 'nullable', 'string', 'exists:users,username'],
            'anonymously' => ['sometimes', 'boolean'],
            'content' => ['required', 'string', 'min:1', "max:{$maxContent}", new NoBlankCharacters],
            'thread_posts' => ['sometimes', 'array', 'max:9'],
            'thread_posts.*' => ['nullable', 'string', 'min:1', 'max:1000', new NoBlankCharacters],
            'channel_id' => ['sometimes', 'nullable', 'integer', 'exists:channels,id'],
            'channel_name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:50', 'regex:/^[\pL\pN\s\-_]+$/u'],
            'poll_options' => ['sometimes', 'array', 'min:2', 'max:4'],
            'poll_options.*' => ['required', 'string', 'min:1', 'max:40'],
            'poll_duration' => ['required_with:poll_options', 'integer', 'min:1', 'max:7'],
            'thread_polls' => ['sometimes', 'array', 'max:9'],
        ];

        /** @var User|null $user */
        $user = $this->user();

        if ($captcha->required('post', $user)) {
            $state = $this->input('captcha_state');
            $rules['captcha_state'] = ['bail', 'required', 'uuid'];
            $rules['cf-turnstile-response'] = ['bail', 'required', 'string', 'max:2048', new MobileTurnstile('post', is_string($state) ? $state : '')];
        }

        return $rules;
    }

    /** The user being asked, or null for a post to the timeline. */
    public function recipientUsername(): ?string
    {
        $username = $this->string('to_username')->toString();

        return $username === '' ? null : $username;
    }

    /** The post's text. */
    public function content(): string
    {
        return $this->string('content')->toString();
    }

    /** Whether the recipient should not be shown the author. */
    public function isAnonymous(): bool
    {
        if ($this->has('anonymously')) {
            return $this->boolean('anonymously');
        }

        // Honor the user's anonymity preference when the client omits this flag.
        /** @var User|null $user */
        $user = $this->user();

        return $user === null || $user->prefers_anonymous_questions;
    }
}
