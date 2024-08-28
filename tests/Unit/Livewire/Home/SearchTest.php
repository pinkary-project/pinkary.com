<?php

declare(strict_types=1);

use App\Livewire\Home\Search;
use App\Models\Link;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('lists no result when there are no users', function () {
    $component = Livewire::test(Search::class);

    $component->assertSee('No matching users or content found.');
});

test('lists by default users with GitHub or Twitter links', function () {
    Link::factory(3)->create([
        'url' => 'twitter.com/nunomaduro',
    ]);

    $component = Livewire::test(Search::class);

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

    $component = Livewire::test(Search::class);

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

    $component = Livewire::test(Search::class);

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

    $component = Livewire::test(Search::class);

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

    $component = Livewire::test(Search::class);

    foreach (range(1, 50) as $index) {
        $component->refresh();
        $component->assertDontSee('Adam Lee');
    }
});

test('famous users are cached for a day', function () {
    $famousUsers = User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'this is an answer'])
        ->create();

    Cache::forget('top-50-users');

    Livewire::test(Search::class);

    $this->assertTrue(Cache::has('top-50-users'));

    $CachedFamousUsers = Cache::get('top-50-users');

    $this->assertEquals($famousUsers->pluck('id')->toArray(), $CachedFamousUsers);
});

test('cached famous users are refreshed after a day', function () {
    $famousUsers = User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'this is an answer'])
        ->create();

    Cache::forget('top-50-users');

    $component = Livewire::test(Search::class);

    $this->assertTrue(Cache::has('top-50-users'));

    $this->travel(1)->days();

    $newFamousUsers = User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user) {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(3, ['answer' => 'this is an answer'])
        ->create();

    $this->assertFalse(Cache::has('top-50-users'));

    $component->refresh();

    $this->assertTrue(Cache::has('top-50-users'));

    $CachedFamousUsers = Cache::get('top-50-users');

    $this->assertNotEquals($famousUsers->pluck('id')->toArray(), $CachedFamousUsers);
    $this->assertEquals($newFamousUsers->pluck('id')->toArray(), $CachedFamousUsers);
});

test('search for questions when query at least 3 characters', function () {
    User::factory()->create([
        'name' => 'Nuno Maduro',
        'email_verified_at' => now(),
    ]);

    Question::factory()->create([
        'content' => 'How to start?',
        'answer' => 'Hello world!',
    ]);

    $component = Livewire::test(Search::class);

    $component->assertDontSee('Nuno Maduro')
        ->assertDontSee('Hello world');

    $component->set('query', 'Hello');

    $component->assertDontSee('Nuno Maduro')
        ->assertSee('Hello world');
});

test('returns up to 4 questions in welcome search with enough matching users', function () {
    User::factory(10)->create([
        'name' => 'Nuno Maduro',
        'email_verified_at' => now(),
    ]);

    Question::factory(5)->create([
        'content' => 'Who created Pest?',
        'answer' => 'Nuno Maduro',
    ]);

    $component = Livewire::test(Search::class);

    $component->set('welcomeSearch', true);
    $component->set('query', 'Nuno');

    $component->assertViewHas('results', function (Collection $results) {
        $counts = $results->countBy(function (Model $result) {
            return $result::class;
        });

        return $counts->get(Question::class) === 4
            && $counts->get(User::class) === 6;
    });
});

test('returns more questions in welcome search with less than 6 matching users', function () {
    User::factory(5)->create([
        'name' => 'Nuno Maduro',
        'email_verified_at' => now(),
    ]);

    Question::factory(10)->create([
        'content' => 'Who created Pest?',
        'answer' => 'Nuno Maduro',
    ]);

    $component = Livewire::test(Search::class);

    $component->set('welcomeSearch', true);
    $component->set('query', 'Nuno');

    $component->assertViewHas('results', function (Collection $results) {
        $counts = $results->countBy(function (Model $result) {
            return $result::class;
        });

        return $counts->get(User::class) === 5
            && $counts->get(Question::class) === 5;
    });
});
