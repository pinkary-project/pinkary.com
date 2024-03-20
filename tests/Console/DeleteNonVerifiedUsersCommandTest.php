<?php

declare(strict_types=1);

use App\Console\Commands\DeleteNonVerifiedUsersCommand;
use App\Models\User;

test('deletes non-verified users', function () {
    $users = User::factory()->count(3)->create();

    $users->get(1)->update([
        'updated_at' => now()->subDays(8),
        'email_verified_at' => null,
    ]);

    $users->get(2)->update([
        'updated_at' => now()->subHour(),
        'email_verified_at' => null,
    ]);

    $this->artisan(DeleteNonVerifiedUsersCommand::class)
        ->assertExitCode(0);

    expect(User::count())->toBe(2);
});
