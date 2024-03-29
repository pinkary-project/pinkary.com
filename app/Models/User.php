<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Avatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $avatar
 * @property string $avatar_url
 * @property string|null $bio
 * @property Carbon $created_at
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $gradient
 * @property int $id
 * @property bool $is_verified
 * @property string|null $github_username
 * @property string $left_color
 * @property array<int, string> $links_sort
 * @property string $link_shape
 * @property string $mail_preference_time
 * @property string $name
 * @property string $questions_preference
 * @property string $right_color
 * @property array<string, string>|null $settings
 * @property string $timezone
 * @property Carbon $updated_at
 * @property string $username
 * @property-read Collection<int, Link> $links
 * @property-read Collection<int, Question> $questionsReceived
 * @property-read Collection<int, Question> $questionsSent
 * @property-read Question $pinnedQuestion
 * @property-read Collection<int, DatabaseNotification> $unreadNotifications
 * @property-read Collection<int, DatabaseNotification> $readNotifications
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the user's links.
     *
     * @return HasMany<Link>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user's questions sent.
     *
     * @return HasMany<Question>
     */
    public function questionsSent(): HasMany
    {
        return $this->hasMany(Question::class, 'from_id');
    }

    /**
     * Get the user's questions received.
     *
     * @return HasMany<Question>
     */
    public function questionsReceived(): HasMany
    {
        return $this->hasMany(Question::class, 'to_id');
    }

    /**
     * @return HasOne<Question>
     */
    public function pinnedQuestion(): HasOne
    {
        return $this->hasOne(Question::class, 'to_id')
            ->where('pinned', true);
    }

    /**
     * Get the user's links sort attribute.
     *
     * @return array<int, int>
     */
    public function getLinksSortAttribute(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        /** @var array<int, string> $sorting */
        $sorting = json_decode($value, true);

        return collect($sorting)
            ->map(fn (string $linkId): int => (int) $linkId)
            ->values()
            ->all();
    }

    /**
     * Set the user's right color attribute.
     */
    public function getRightColorAttribute(): string
    {
        return str($this->gradient)
            ->match('/to-.*?\d{3}/')
            ->after('to-')
            ->value();
    }

    /**
     * Set the user's left color attribute.
     */
    public function getLeftColorAttribute(): string
    {
        return str($this->gradient)
            ->match('/from-.*?\d{3}/')
            ->after('from-')
            ->value();
    }

    /**
     * Get the user's avatar URL attribute.
     */
    public function getAvatarUrlAttribute(): string
    {
        /** @var array<int, string> $urls */
        $urls = $this->links->pluck('url')->values()->all();

        return (new Avatar(
            email: $this->email,
            links: $urls,
        ))->url();
    }

    /**
     * Get the user's shape attribute.
     */
    public function getLinkShapeAttribute(): string
    {
        $settings = $this->settings ?: [];

        $linkShape = data_get($settings, 'link_shape', 'rounded-lg');

        assert(is_string($linkShape));

        return $linkShape;
    }

    /**
     * Get the user's gradient attribute.
     */
    public function getGradientAttribute(): string
    {
        $settings = $this->settings ?: [];

        $gradient = data_get($settings, 'gradient', 'from-blue-500 to-purple-600');

        assert(is_string($gradient));

        return $gradient;
    }

    /**
     * Purge the user's account.
     */
    public function purge(): void
    {
        if ($this->avatar) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $this->avatar)
            );
        }

        $this->delete();
    }

    /**
     * Get the user's "is_verified" attribute.
     */
    public function getIsVerifiedAttribute(bool $isVerified): bool
    {
        if (collect(config()->array('sponsors.github_usernames'))->contains($this->username)) {
            return true;
        }

        return $isVerified;
    }

    /**
     * Get the user's "questions_preference" attribute.
     */
    public function getQuestionsPreferenceAttribute(): string
    {
        $settings = $this->settings ?: [];

        $questionsPreference = data_get($settings, 'questions_preference', 'anonymously');

        assert(is_string($questionsPreference));

        return $questionsPreference;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'is_verified' => 'boolean',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }
}
