<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

test('a guest cannot update profile or manage links', function (): void {
    patchJson(route('api.v1.profile.update'), ['name' => 'New Name'])->assertUnauthorized();
    postJson(route('api.v1.links.store'), ['description' => 'Test', 'url' => 'https://test.com'])->assertUnauthorized();
});

test('an authenticated user can update their profile information and settings', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'bio' => 'Old bio',
    ]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    patchJson(route('api.v1.profile.update'), [
        'name' => 'New Name',
        'bio' => 'New bio',
        'link_shape' => 'rounded-full',
        'gradient' => 'from-red-500 to-orange-600',
    ], $headers)->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.bio', 'New bio')
        ->assertJsonPath('data.link_shape', 'rounded-full')
        ->assertJsonPath('data.gradient', 'from-red-500 to-orange-600');

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->bio)->toBe('New bio')
        ->and($user->link_shape)->toBe('rounded-full')
        ->and($user->gradient)->toBe('from-red-500 to-orange-600');
});

test('an authenticated user can upload an avatar through API profile update', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    patchJson(route('api.v1.profile.update'), [
        'avatar' => $avatar,
    ], $headers)->assertOk();

    expect($user->fresh()->is_uploaded_avatar)->toBeTrue();
});

test('link CRUD and sort work through the API', function (): void {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $userHeaders = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    $strangerHeaders = ['Authorization' => 'Bearer '.$stranger->createToken('test')->plainTextToken];

    // Create
    $response = postJson(route('api.v1.links.store'), [
        'description' => 'My GitHub',
        'url' => 'github.com/myuser',
    ], $userHeaders);

    $response->assertCreated()
        ->assertJsonPath('data.description', 'My GitHub')
        ->assertJsonPath('data.url', 'https://github.com/myuser?ref=pinkary');

    $linkId = (int) $response->json('data.id');
    $link = Link::findOrFail($linkId);

    // Update
    auth()->forgetGuards();
    putJson(route('api.v1.links.update', $link), [
        'description' => 'Updated GitHub',
        'url' => 'https://github.com/updated',
        'is_visible' => false,
    ], $strangerHeaders)->assertForbidden();

    auth()->forgetGuards();
    putJson(route('api.v1.links.update', $link), [
        'description' => 'Updated GitHub',
        'url' => 'https://github.com/updated',
        'is_visible' => false,
    ], $userHeaders)->assertOk()
        ->assertJsonPath('data.description', 'Updated GitHub')
        ->assertJsonPath('data.is_visible', false);

    expect($link->fresh()->is_visible)->toBeFalse();

    // Create a second link for sorting
    $response2 = postJson(route('api.v1.links.store'), [
        'description' => 'Second Link',
        'url' => 'https://second.com',
    ], $userHeaders)->assertCreated();

    $link2Id = (int) $response2->json('data.id');

    // Sort
    postJson(route('api.v1.links.sort'), [
        'sort' => [$link2Id, $linkId],
    ], $userHeaders)->assertOk()
        ->assertJsonPath('data.sorted', true);

    expect($user->fresh()->links_sort)->toBe([$link2Id, $linkId]);

    // Delete
    auth()->forgetGuards();
    deleteJson(route('api.v1.links.destroy', $link), [], $strangerHeaders)->assertForbidden();
    auth()->forgetGuards();
    deleteJson(route('api.v1.links.destroy', $link), [], $userHeaders)->assertNoContent();

    expect(Link::find($linkId))->toBeNull();
});

test('link creation enforces link count limits', function (): void {
    $user = User::factory()->create(['is_verified' => false]);
    Link::factory()->count(10)->create(['user_id' => $user->id]);

    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    postJson(route('api.v1.links.store'), [
        'description' => '11th link',
        'url' => 'https://example.com/11',
    ], $headers)->assertStatus(422);
});
