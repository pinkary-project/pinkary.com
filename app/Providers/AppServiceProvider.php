<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Blaze\Blaze;
use Livewire\Livewire;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configurePasswordValidation();
        $this->configureDates();
        $this->configureEmailVerification();
        $this->configurePasswordResetUrl();
        $this->configureBlaze();

        Route::bind('username', fn (string $username): User => User::where(DB::raw('LOWER(username)'), mb_strtolower($username))->firstOrFail());

        Livewire::component('notifications-index', \App\Livewire\Notifications\Index::class);
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard();
    }

    /**
     * Configure the password reset URL so it is always pinned to the
     * application's configured URL, and may never be poisoned by an
     * attacker controlled "Host" header of the incoming request.
     */
    private function configurePasswordResetUrl(): void
    {
        ResetPassword::createUrlUsing(function (mixed $notifiable, string $token): string {
            assert($notifiable instanceof CanResetPassword);

            return mb_rtrim(Config::string('app.url'), '/').route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], absolute: false);
        });
    }

    /**
     * Personalize the verification email without changing Laravel's signed URL or expiry.
     */
    private function configureEmailVerification(): void
    {
        VerifyEmail::toMailUsing(function (mixed $notifiable, string $url): MailMessage {
            $name = $notifiable instanceof User ? $notifiable->name : __('there');

            return (new MailMessage)
                ->subject(__('Verify your email address'))
                ->greeting(__('Hello, :name!', ['name' => $name]))
                ->line(__('Please click the button below to verify your email address.'))
                ->action(__('Verify Email Address'), $url)
                ->line(__('If you did not create an account, no further action is required.'));
        });
    }

    /**
     * Configure the password validation rules.
     */
    private function configurePasswordValidation(): void
    {
        Password::defaults(fn () => $this->app->isProduction() ? Password::min(8)->uncompromised() : null);
    }

    /**
     * Configure Blaze optimizations for anonymous Blade components.
     */
    private function configureBlaze(): void
    {
        Blaze::optimize()
            ->in(resource_path('views/components'))
            ->in(resource_path('views/components/footer.blade.php'), compile: false)
            ->in(resource_path('views/components/link-preview-card.blade.php'), compile: false)
            ->in(resource_path('views/components/icons'), memo: true);
    }
}
