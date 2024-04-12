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

test('order by the number of answered questions', function () {
    $punyapal = User::factory()->create([
        'name' => 'Artisan Punyapal Shah',
        'email_verified_at' => now(),
    ]);

    $punyapal->links()->create([
        'url' => 'https://twitter.com/mrpunyapal',
        'description' => 'twitter',
    ]);

    $nuno = User::factory()->create([
        'name' => 'Artisan Nuno Maduro',
        'email_verified_at' => now(),
    ]);

    $nuno->links()->create([
        'url' => 'https://twitter.com/enunomaduro',
        'description' => 'twitter',
    ]);

    $nuno->questionsReceived()->create([
        'from_id' => $punyapal->id,
        'content' => 'What is the best PHP framework?',
        'answer' => 'Laravel',
    ]);

    $nuno->questionsReceived()->create([
        'from_id' => $punyapal->id,
        'content' => 'What is the best PHP testing framework?',
        'answer' => 'Pest',
    ]);

    $punyapal->questionsReceived()->create([
        'from_id' => $nuno->id,
        'content' => 'What is the best PHP frontend framework?',
        'answer' => 'Livewire',
    ]);

    $component = Livewire::test(Index::class);

    $component->assertSeeInOrder([
        'Artisan Nuno Maduro',
        'Artisan Punyapal Shah',
    ]);

    $component->set('query', 'Artisan');

    $component->assertSeeInOrder([
        'Artisan Nuno Maduro',
        'Artisan Punyapal Shah',
    ]);
});
