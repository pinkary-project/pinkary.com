<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Models\Viewable;
use App\Enums\UserMailPreference;
use App\Services\ParsableBio;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property bool $prefers_anonymous_questions
 * @property string|null $avatar
 * @property string $avatar_url
 * @property string|null $bio
 * @property HtmlString $parsed_bio
 * @property Carbon $created_at
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property ?string $two_factor_secret
 * @property array<int, string> $two_factor_recovery_codes
 * @property ?Carbon $two_factor_confirmed_at
 * @property string $gradient
 * @property int $id
 * @property bool $is_verified
 * @property bool $is_company_verified
 * @property string|null $github_username
 * @property string $left_color
 * @property array<int, string> $links_sort
 * @property string $link_shape
 * @property UserMailPreference $mail_preference_time
 * @property string $name
 * @property string $right_color
 * @property array<string, string>|null $settings
 * @property Carbon $updated_at
 * @property ?Carbon $avatar_updated_at
 * @property string $username
 * @property int $views
 * @property bool $is_uploaded_avatar
 * @property-read Collection<int, Link> $links
 * @property-read Collection<int, Question> $questionsReceived
 * @property-read Collection<int, Question> $questionsSent
 * @property-read Question $pinnedQuestion
 * @property-read Collection<int, DatabaseNotification> $unreadNotifications
 * @property-read Collection<int, DatabaseNotification> $readNotifications
 * @property-read Collection<int, User> $following
 * @property-read Collection<int, User> $followers
 */
final class User extends Authenticatable implements FilamentUser, MustVerifyEmail, Viewable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Increment the views for the given IDs.
     */
    public static function incrementViews(array $ids): void
    {
        self::withoutTimestamps(function () use ($ids): void {
            self::query()
                ->whereIn('id', $ids)
                ->increment('views');
        });
    }

    /**
     * Determine if the user can access the admin given panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail() && ($this->email === 'enunomaduro@gmail.com' || $this->email === 'mrpunyapal@gmail.com');
    }

    /**
     * Get the user's bookmarks.
     *
     * @return HasMany<Bookmark>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

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
     * Get the user's followers.
     *
     * @return BelongsToMany<User>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * Get the user's following.
     *
     * @return BelongsToMany<User>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * Get the user's avatar URL attribute.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : asset('img/default-avatar.png');
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
     * Get the user's shape attribute.
     */
    public function getLinkShapeAttribute(): string
    {
        $settings = $this->settings ?: [];

        $linkShape = data_get($settings, 'link_shape', 'rounded-lg');

        return type($linkShape)->asString();
    }

    /**
     * Get the user's gradient attribute.
     */
    public function getGradientAttribute(): string
    {
        $settings = $this->settings ?: [];

        $gradient = data_get($settings, 'gradient', 'from-blue-500 to-purple-600');

        return type($gradient)->asString();
    }

    /**
     * Purge the user's account.
     */
    public function purge(): void
    {
        if ($this->avatar) {
            Storage::disk('public')->delete($this->avatar);
        }

        $this->followers()->detach();
        $this->following()->detach();

        $this->notifications()->delete();

        $this->questionsReceived->each->delete();
        $this->questionsSent->each->delete();

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

        if (collect(config()->array('sponsors.github_company_usernames'))->contains($this->username)) {
            return true;
        }

        return $isVerified;
    }

    /**
     * Get the user's "is_company_verified" attribute.
     */
    public function getIsCompanyVerifiedAttribute(bool $isCompanyVerified): bool
    {
        if (collect(config()->array('sponsors.github_company_usernames'))->contains($this->username)) {
            return true;
        }

        return $isCompanyVerified;
    }

    /**
     * Get the user's bio attribute.
     */
    public function getParsedBioAttribute(): HtmlString
    {
        return new HtmlString((new ParsableBio)->parse((string) $this->bio));
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
            'two_factor_confirmed_at' => 'datetime',
            'is_verified' => 'boolean',
            'is_company_verified' => 'boolean',
            'password' => 'hashed',
            'settings' => 'array',
            'prefers_anonymous_questions' => 'boolean',
            'avatar_updated_at' => 'datetime',
            'mail_preference_time' => UserMailPreference::class,
            'views' => 'integer',
            'is_uploaded_avatar' => 'boolean',
        ];
    }
}
