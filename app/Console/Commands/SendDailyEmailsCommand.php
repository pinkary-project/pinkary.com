<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\PendingNotifications;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

final class SendDailyEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:daily-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the daily emails to the users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::where('mail_preference_time', 'daily')
            ->whereHas('notifications')
            ->each(fn (User $user) => Mail::to($user)->queue(new PendingNotifications($user)));
    }
}
