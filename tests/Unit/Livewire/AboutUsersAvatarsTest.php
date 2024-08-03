<?php

declare(strict_types=1);

use App\Livewire\AboutUsersAvatars;
use App\Models\User;
use Livewire\Livewire;

it('renders', function () {
    $users = User::factory(15)->create();

    $component = Livewire::test(AboutUsersAvatars::class);

    $component->assertOk();
});
