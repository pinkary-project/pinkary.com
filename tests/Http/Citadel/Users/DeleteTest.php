<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

it('blocks the email when admin deletes a user', function (): void {
    $admin = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $user = User::factory()->create();

    $email = $user->email;

    $this->actingAs($admin);

    Livewire::test(UserResource\Pages\Index::class)
        ->callAction(TestAction::make('delete')->table($user));

    $this->assertDatabaseHas('blocked_accounts', ['email' => $email]);
    expect($user->fresh())->toBeNull();
});

it('does not duplicate blocked accounts when deleting same user email twice', function (): void {
    $admin = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $user = User::factory()->create();

    $email = $user->email;

    $this->actingAs($admin);

    Livewire::test(UserResource\Pages\Index::class)
        ->callAction(TestAction::make('delete')->table($user));

    $this->assertDatabaseCount('blocked_accounts', 1);
    $this->assertDatabaseHas('blocked_accounts', ['email' => $email]);
});
