<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\User;

test('to array', function () {
    $user = User::factory()->create()->fresh();

    expect(array_keys($user->toArray()))->toBe([
        'id',
        'name',
        'username',
        'bio',
        'email',
        'email_verified_at',
        'created_at',
        'updated_at',
        'links_sort',
        'settings',
        'avatar',
        'is_verified',
        'mail_preference_time',
        'github_username',
        'prefers_anonymous_questions',
        'is_company_verified',
        'avatar_updated_at',
        'views',
        'is_uploaded_avatar',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ]);
});

test('is verified', function () {
    $user = User::factory()->create([
        'is_verified' => true,
        'username' => 'test',
    ]);

    expect($user->is_verified)->toBeTrue();
});

test('is verified because in list of fixed sponsors', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'username' => 'test',
    ]);

    config()->set('sponsors.github_usernames', ['test']);

    expect($user->is_verified)->toBeTrue()
        ->and($user->is_company_verified)->toBeFalse();
});

test('is not verified because not in sponsors', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'username' => 'test',
    ]);

    config()->set('sponsors.github_usernames', ['test2']);

    expect($user->is_verified)->toBeFalse()
        ->and($user->is_company_verified)->toBeFalse();
});

test('is verified because in list of fixed company sponsors', function () {
    $user = User::factory()->create([
        'is_verified' => false,
        'username' => 'test',
    ]);

    config()->set('sponsors.github_company_usernames', ['test']);

    expect($user->is_verified)->toBeTrue()
        ->and($user->is_company_verified)->toBeTrue();
});

test('increment views', function () {
    $user = User::factory()->create();

    User::incrementViews([$user->id]);

    expect($user->fresh()->views)->toBe(1);
});

test('default avatar url', function () {
    $user = User::factory()->create();

    expect($user->avatar)->toBeNull()
        ->and($user->avatar_url)->toBe(asset('img/default-avatar.png'));
});

test('custom avatar url', function () {
    $user = User::factory()->create([
        'avatar' => 'avatars/123.png',
    ]);

    expect($user->avatar)->toBe('avatars/123.png')
        ->and($user->avatar_url)->toBe(Storage::disk('public')->url('avatars/123.png'));
});

test('following', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $user->following()->attach($target->id);

    expect($user->following->count())->toBe(1)
        ->and($user->following->first()->id)->toBe($target->id);
});

test('followers', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $target->following()->attach($user->id);

    expect($user->followers->count())->toBe(1)
        ->and($user->followers->first()->id)->toBe($target->id);
});

test('purge followers with user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $user->followers()->attach($target->id);

    $target->following()->attach(User::factory()->create()->id);

    $user->purge();

    expect($target->following()->count())->toBe(1);
});

test('purge following with user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $user->following()->attach($target->id);

    $target->followers()->attach(User::factory()->create()->id);

    $user->purge();

    expect($target->followers()->count())->toBe(1);
});

test('purge links with user', function () {
    $user = User::factory()->hasLinks(2)->create();
    $this->assertDatabaseCount('links', 2);

    $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $this->assertNull($user->fresh());
    $this->assertDatabaseCount('links', 0);
});

test('purge notifications with user', function () {
    $user = User::factory()->create();
    Question::factory(2)->create([
        'to_id' => $user->id,
    ]);
    $this->assertDatabaseCount('notifications', 2);

    $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $this->assertNull($user->fresh());
    $this->assertDatabaseCount('notifications', 0);
});

test('purge questions, comments and decendants with user', function () {
    $user = User::factory()->hasQuestionsSent(2)->create();
    Question::factory()
        ->hasChildren(2)
        ->hasDescendants(2)
        ->create([
            'from_id' => $user->id,
            'to_id' => $user->id,
        ]);

    $this->assertDatabaseCount('questions', 7);

    $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $this->assertNull($user->fresh());
    $this->assertDatabaseCount('questions', 0);
});

test('parse the bio', function () {
    $user = User::factory()->create([
        'bio' => 'Hello, https://example.com is my website.',
    ]);

    expect($user->parsed_bio->toHtml())->toBe((new App\Services\ParsableBio)->parse($user->bio));
});
