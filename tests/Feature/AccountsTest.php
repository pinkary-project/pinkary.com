<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

test('complete account switching flow works', function () {
    $user1 = User::factory()->create(['username' => 'john', 'email' => 'john@example.com']);
    $user2 = User::factory()->create(['username' => 'jane', 'email' => 'jane@example.com']);

    $this->actingAs($user1);

    Accounts::push('john');

    request()->cookies->set('accounts', json_encode(['john' => true]));

    $accounts = Accounts::all();
    expect($accounts)->toHaveKey('john');

    $this->actingAs($user2);
    Accounts::push('jane');

    request()->cookies->set('accounts', json_encode(['john' => true, 'jane' => true]));

    $accounts = Accounts::all();
    expect($accounts)->toHaveKey('john');
    expect($accounts)->toHaveKey('jane');

    Accounts::switch('john');

    expect(auth()->user()->username)->toBe('john');
    expect(auth()->user()->email)->toBe('john@example.com');

    Accounts::switch('jane');

    expect(auth()->user()->username)->toBe('jane');
    expect(auth()->user()->email)->toBe('jane@example.com');
});

test('removing account works correctly', function () {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $this->actingAs($user1);
    Accounts::push('john');

    $this->actingAs($user2);
    Accounts::push('jane');

    request()->cookies->set('accounts', json_encode(['john' => true, 'jane' => true]));

    $accounts = Accounts::all();
    expect($accounts)->toHaveKey('john');
    expect($accounts)->toHaveKey('jane');

    Accounts::remove('john');

    request()->cookies->set('accounts', json_encode(['jane' => true]));

    $accounts = Accounts::all();
    expect($accounts)->not->toHaveKey('john');
    expect($accounts)->toHaveKey('jane');
});

test('cannot switch to account not in cookie', function () {
    $user = User::factory()->create(['username' => 'john']);

    expect(fn () => Accounts::switch('john'))
        ->toThrow('Unauthorized action.');
});

test('account switch handles non-existent account gracefully', function () {
    $user = User::factory()->create(['username' => 'john']);
    $this->actingAs($user);
    Accounts::push('john');

    request()->cookies->set('accounts', json_encode(['john' => true]));

    try {
        Accounts::switch('nonexistent');
        $this->fail('Expected exception was not thrown');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
        expect($e->getMessage())->toContain('Unauthorized action');
    }
});
