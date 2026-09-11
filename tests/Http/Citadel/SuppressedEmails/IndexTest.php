<?php

declare(strict_types=1);

use App\Filament\Resources\SuppressedEmailResource;
use App\Models\SuppressedEmail;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

test('auth', function (): void {
    $response = $this->get(SuppressedEmailResource::getUrl('index', isAbsolute: false));

    $response->assertStatus(302)->assertRedirect(route('login'));
});

it('is only accessible to nuno', function (): void {
    $user = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $response = $this->actingAs($user)->get(SuppressedEmailResource::getUrl('index', isAbsolute: false));

    $response->assertStatus(200);
});

it('is not accessible to other users', function (): void {
    $user = User::factory()->create([
        'email' => 'nuno@laravel.com',
    ]);

    $response = $this->actingAs($user)->get(SuppressedEmailResource::getUrl('index', isAbsolute: false));

    $response->assertStatus(403);
});

it('shows masked emails', function (): void {
    $admin = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $this->actingAs($admin);

    $record = SuppressedEmail::factory()->create([
        'email' => 'jonathan@example.com',
    ]);

    Livewire::test(SuppressedEmailResource\Pages\Index::class)
        ->assertCanSeeTableRecords([$record])
        ->assertSee('jo****an@example.com');
});

it('shows profile and delete actions only when a user exists', function (): void {
    $admin = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $this->actingAs($admin);

    $user = User::factory()->create();

    $withUser = SuppressedEmail::factory()->create([
        'email' => $user->email,
    ]);

    $orphan = SuppressedEmail::factory()->create();

    Livewire::test(SuppressedEmailResource\Pages\Index::class)
        ->assertTableActionVisible('visit_profile', $withUser)
        ->assertTableActionVisible('delete_user', $withUser)
        ->assertTableActionHidden('visit_profile', $orphan)
        ->assertTableActionHidden('delete_user', $orphan);
});

it('blocks the email when admin deletes the user', function (): void {
    $admin = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $user = User::factory()->create();

    $record = SuppressedEmail::factory()->create([
        'email' => $user->email,
    ]);

    $this->actingAs($admin);

    Livewire::test(SuppressedEmailResource\Pages\Index::class)
        ->callAction(TestAction::make('delete_user')->table($record));

    $this->assertDatabaseHas('blocked_accounts', ['email' => $user->email]);
    expect($user->fresh())->toBeNull();
});

it('removes the suppression', function (): void {
    $admin = User::factory()->create([
        'email' => 'enunomaduro@gmail.com',
    ]);

    $record = SuppressedEmail::factory()->create();

    $this->actingAs($admin);

    Livewire::test(SuppressedEmailResource\Pages\Index::class)
        ->callAction(TestAction::make('remove')->table($record));

    $this->assertDatabaseMissing('suppressed_emails', ['id' => $record->id]);
});
