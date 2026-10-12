<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserMailPreference;
use App\Mail\PendingNotifications;
use App\Models\SuppressedEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class SendUnreadNotificationEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:unread-notification-emails
                            {--weekly : Send the unread notification emails to the users who have set their mail preference to weekly.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the unread notification emails to the users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $reminderCutoff = now()->subDays(30);

        User::query()
            ->when($this->option('weekly'), function (Builder $query): void {
                $query->where('mail_preference_time', UserMailPreference::Weekly);
            }, function (Builder $query): void {
                $query->where('mail_preference_time', UserMailPreference::Daily);
            })
            ->whereHas('unreadNotifications', function (Builder $query) use ($reminderCutoff): void {
                $query->where(function (Builder $query) use ($reminderCutoff): void {
                    $query->whereNull('email_reminded_at')
                        ->orWhere('email_reminded_at', '<=', $reminderCutoff);
                });
            })
            ->whereNotIn('email', SuppressedEmail::query()->select('email'))
            ->each(function (User $user) use ($reminderCutoff): void {
                DB::transaction(function () use ($user, $reminderCutoff): void {
                    $notifications = $user->unreadNotifications()
                        ->where(function (Builder $query) use ($reminderCutoff): void {
                            $query->whereNull('email_reminded_at')
                                ->orWhere('email_reminded_at', '<=', $reminderCutoff);
                        })
                        ->lockForUpdate()
                        ->get(['id']);

                    if ($notifications->isEmpty()) {
                        return;
                    }

                    $notificationIds = $notifications->modelKeys();
                    $remindedAt = now()->startOfSecond();

                    $user->notifications()->whereKey($notificationIds)
                        ->update(['email_reminded_at' => $remindedAt]);

                    Mail::to($user)->queue(new PendingNotifications(
                        $user,
                        $user->unreadNotifications()->count(),
                        $notificationIds,
                        $remindedAt,
                    ));
                });
            });
    }
}
