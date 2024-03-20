<?php

declare(strict_types=1);

use App\Livewire\WelcomeUsersAvatars;
use App\Models\User;
use Livewire\Livewire;

it('renders', function () {
    $users = User::factory(15)->create();

    $component = Livewire::test(WelcomeUsersAvatars::class);

    $component->assertStatus(200);
});
