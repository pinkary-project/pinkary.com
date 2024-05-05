<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;
use Livewire\Livewire;

it('can be listed', function () {
    $users = User::factory()->count(10)->create();

    Livewire::test(UserResource\Pages\Index::class)
        ->assertCanSeeTableRecords($users);
});
