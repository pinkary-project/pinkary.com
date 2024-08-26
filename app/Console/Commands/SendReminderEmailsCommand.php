<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserMailPreference;
use App\Mail\PendingNotifications;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

final class SendReminderEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminder-emails
                            {--weekly : Send the reminder emails to the users who have set their mail preference to weekly.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the reminder emails to the users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::query()
            ->when($this->option('weekly'), function (Builder $query): void {
                $query->where('mail_preference_time', UserMailPreference::Weekly);
            }, function (Builder $query): void {
                $query->where('mail_preference_time', UserMailPreference::Daily);
            })
            ->whereHas('notifications')
            ->each(fn (User $user) => Mail::to($user)->queue(new PendingNotifications($user)));
    }
}
