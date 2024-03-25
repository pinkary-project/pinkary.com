<?php

declare(strict_types=1);

use App\Console\Commands\SyncVerifiedUsersCommand;
use App\Jobs\SyncVerifiedUser;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('sync verified users', function () {
    User::factory(5)->create();

    User::factory(2)->create([
        'is_verified' => true,
    ]);

    Queue::fake(SyncVerifiedUser::class);

    $this->artisan(SyncVerifiedUsersCommand::class)
        ->assertExitCode(0);

    Queue::assertPushed(SyncVerifiedUser::class, 2);
});
