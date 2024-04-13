<?php

declare(strict_types=1);

use App\Livewire\Home\Users;
use App\Models\Link;
use App\Models\User;
use Livewire\Livewire;

test('lists no users when there are no users', function () {
    $component = Livewire::test(Users::class);

    $component->assertSee('No users found.');
});

test('lists by default users with GitHub or Twitter links', function () {
    Link::factory(3)->create([
        'url' => 'twitter.com/nunomaduro',
    ]);

    $component = Livewire::test(Users::class);

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

    $component = Livewire::test(Users::class);

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

    $nuno->questionsReceived()->createMany([[
        'from_id' => $punyapal->id,
        'content' => 'What is the best PHP framework?',
        'answer' => 'Laravel',
    ], [
        'from_id' => $punyapal->id,
        'content' => 'What is the best PHP testing framework?',
        'answer' => 'Pest',
    ]]);

    $punyapal->questionsReceived()->create([
        'from_id' => $nuno->id,
        'content' => 'What is the best PHP frontend framework?',
        'answer' => 'Livewire',
    ]);

    $component = Livewire::test(Users::class);

    $component->set('query', 'Artisan');

    $component->assertSeeInOrder([
        'Artisan Nuno Maduro',
        'Artisan Punyapal Shah',
    ]);
});

test('default users should have 2 verified users', function () {
    config(['sponsors.github_company_usernames' => ['MrPunyapal']]);

    User::factory(2)
        ->sequence([
            'name' => 'Nuno Maduro',
            'username' => 'nunomaduro',
            'is_verified' => true,
        ], [
            'name' => 'Punyapal Shah',
            'username' => 'MrPunyapal',
        ])
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'this is an answer'])
        ->create();

    User::factory(10)
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'this is an answer'])
        ->create();

    $component = Livewire::test(Index::class);

    $component->assertSee('Nuno Maduro')
        ->assertSee('Punyapal Shah');

});

test('default users should be from top 50 famous users', function () {

    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'this is an answer'])
        ->create();

    User::factory()
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'this is an answer'])
        ->create(['name' => 'Adam Lee']);

    $component = Livewire::test(Index::class);

    $component->assertDontSee('Adam Lee');

})->repeat(50);
