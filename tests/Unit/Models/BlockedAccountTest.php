<?php

declare(strict_types=1);

use App\Models\BlockedAccount;

test('to array', function (): void {
    $blocked = BlockedAccount::factory()->create()->fresh();

    expect(array_keys($blocked->toArray()))->toBe([
        'id',
        'email',
        'created_at',
    ]);
});

test('factory creates blocked account', function (): void {
    $blocked = BlockedAccount::factory()->create();

    expect($blocked->email)->not->toBeNull();
});

test('email is unique', function (): void {
    $email = 'test@example.com';

    BlockedAccount::factory()->create(['email' => $email]);

    expect(BlockedAccount::count())->toBe(1);

    $this->assertDatabaseHas('blocked_accounts', ['email' => $email]);
});

test('can find by email', function (): void {
    $email = 'blocked@example.com';

    BlockedAccount::factory()->create(['email' => $email]);

    $found = BlockedAccount::where('email', $email)->first();

    expect($found)->not->toBeNull()
        ->and($found->email)->toBe($email);
});
