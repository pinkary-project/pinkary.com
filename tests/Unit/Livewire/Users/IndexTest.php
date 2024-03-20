<?php

declare(strict_types=1);

use App\Livewire\Users\Index;
use App\Models\Link;
use App\Models\User;
use Livewire\Livewire;

test('lists no users when there are no users', function () {
    $component = Livewire::test(Index::class);

    $component->assertSee('No users found.');
});

test('lists by default users with GitHub or Twitter links', function () {
    Link::factory(3)->create([
        'url' => 'twitter.com/nunomaduro',
    ]);

    $component = Livewire::test(Index::class);

    $users = User::all();
    expect($users->count())->toBe(3);

    foreach ($users as $user) {
        $component->assertSee([
            $user->name,
            $user->username,
        ]);
    }
});

test('search by name', function () {
    User::factory()->create([
        'name' => 'Nuno Maduro',
        'email_verified_at' => now(),
    ]);

    User::factory()->create([
        'name' => 'Taylor Otwell',
        'email_verified_at' => now(),
    ]);

    $component = Livewire::test(Index::class);

    $component->assertDontSee('Nuno Maduro')
        ->assertDontSee('Taylor Otwell');

    $component->set('query', 'Nuno');

    $component->assertSee('Nuno Maduro')
        ->assertDontSee('Taylor Otwell');
});
