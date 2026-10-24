<?php

declare(strict_types=1);

use App\Console\Commands\ExportProfileRedirectsCommand;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    config()->set('app.url', 'https://pinkary.com');
    URL::forceRootUrl('https://pinkary.com');
});

test('exports configured profile redirects for cloudflare', function (): void {
    User::factory()->create(['username' => 'alice', 'redirect_url' => 'https://alice.dev']);
    User::factory()->create(['username' => 'bob', 'redirect_url' => 'https://github.com/bob']);
    User::factory()->create(['username' => 'carol', 'redirect_url' => null]);
    User::factory()->create([
        'username' => 'dave',
        'redirect_url' => 'https://example.com/?ref=pinkary,test',
    ]);

    $this->artisan(ExportProfileRedirectsCommand::class, [
        'path' => Storage::disk()->path('profile-redirects.csv'),
    ])->expectsOutputToContain('Exported 3 profile redirects')
        ->assertExitCode(0);

    $content = (string) Storage::disk()->get('profile-redirects.csv');

    expect(preg_split('/\R/', mb_trim($content)))->toBe([
        'https://pinkary.com/@alice,https://alice.dev,301',
        'https://pinkary.com/@bob,https://github.com/bob,301',
        'https://pinkary.com/@dave,"https://example.com/?ref=pinkary,test",301',
    ]);
});

test('exports only current usernames and existing accounts', function (): void {
    $renamed = User::factory()->create([
        'username' => 'old_name',
        'redirect_url' => 'https://alice.dev',
    ]);
    $renamed->update(['username' => 'new_name']);

    $deleted = User::factory()->create([
        'username' => 'deleted_user',
        'redirect_url' => 'https://deleted.dev',
    ]);
    $deleted->delete();

    $this->artisan(ExportProfileRedirectsCommand::class, [
        'path' => Storage::disk()->path('profile-redirects.csv'),
    ])->assertExitCode(0);

    expect((string) Storage::disk()->get('profile-redirects.csv'))
        ->toBe('https://pinkary.com/@new_name,https://alice.dev,301'."\n");
});

test('exports an empty list when no redirects are configured', function (): void {
    $this->artisan(ExportProfileRedirectsCommand::class, [
        'path' => Storage::disk()->path('profile-redirects.csv'),
    ])->expectsOutputToContain('Exported 0 profile redirects')
        ->assertExitCode(0);

    expect(Storage::disk()->get('profile-redirects.csv'))->toBe('');
});

test('requires an https application url', function (): void {
    config()->set('app.url', 'http://pinkary.test');

    $this->artisan(ExportProfileRedirectsCommand::class, [
        'path' => Storage::disk()->path('profile-redirects.csv'),
    ])->expectsOutputToContain('APP_URL must be an HTTPS URL')
        ->assertExitCode(1);

    expect(Storage::disk()->exists('profile-redirects.csv'))->toBeFalse();
});

test('fails when the csv cannot be written', function (): void {
    Storage::disk()->makeDirectory('profile-redirects.csv');

    $this->artisan(ExportProfileRedirectsCommand::class, [
        'path' => Storage::disk()->path('profile-redirects.csv'),
    ])->expectsOutputToContain('Unable to open')
        ->assertExitCode(1);
});
