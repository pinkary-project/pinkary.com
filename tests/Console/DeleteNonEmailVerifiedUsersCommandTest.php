<?php

declare(strict_types=1);

use App\Console\Commands\DeleteNonEmailVerifiedUsersCommand;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

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

    $this->artisan(DeleteNonEmailVerifiedUsersCommand::class)
        ->assertExitCode(0);

    expect(User::count())->toBe(2);
});

test('does not delete users with old links', function () {
    User::factory()->count(3)->create([
        'email_verified_at' => null,
    ]);

    User::factory()->hasLinks(1, [
        'created_at' => now()->subDays(2),
    ])->create([
        'email_verified_at' => null,
        'name' => 'Punyapal Shah',
    ]);

    $this->artisan(DeleteNonEmailVerifiedUsersCommand::class)
        ->assertExitCode(0);

    assertDatabaseHas('users', ['name' => 'Punyapal Shah']);
});

test('does not delete users with old send questions', function () {
    User::factory()->count(3)->create([
        'email_verified_at' => null,
    ]);

    User::factory()->hasQuestionsSent(1, [
        'created_at' => now()->subDays(2),
    ])->create([
        'email_verified_at' => null,
        'name' => 'Punyapal Shah',
    ]);

    $this->artisan(DeleteNonEmailVerifiedUsersCommand::class)
        ->assertExitCode(0);

    assertDatabaseHas('users', ['name' => 'Punyapal Shah']);
});

test('does not delete users with old received questions', function () {
    User::factory()->count(3)->create([
        'email_verified_at' => null,
    ]);

    User::factory()->hasQuestionsReceived(1, [
        'created_at' => now()->subDays(2),
    ])->create([
        'email_verified_at' => null,
        'name' => 'Punyapal Shah',
    ]);

    $this->artisan(DeleteNonEmailVerifiedUsersCommand::class)
        ->assertExitCode(0);

    assertDatabaseHas('users', ['name' => 'Punyapal Shah']);
});
