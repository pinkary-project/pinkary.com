<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Livewire\Livewire;

it('can be listed', function (): void {
    $users = User::factory()->count(10)->create();

    Livewire::test(UserResource\Pages\Index::class)
        ->assertCanSeeTableRecords($users);
});

it('lists the newest users first', function (): void {
    $oldest = User::factory()->create(['created_at' => now()->subDay()]);
    $newest = User::factory()->create(['created_at' => now()]);

    Livewire::test(UserResource\Pages\Index::class)
        ->assertCanSeeTableRecords(collect([$newest, $oldest]), inOrder: true);
});

it('can delete user', function (): void {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    Livewire::test(UserResource\Pages\Index::class)
        ->callTableAction('delete', $user);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseHas('users', ['id' => $anotherUser->id]);
});
