<?php

declare(strict_types=1);

use App\Livewire\Autocomplete;
use App\Services\Autocomplete as AutocompleteService;
use App\Services\Autocomplete\Result;
use Illuminate\Support\Collection;
use Livewire\Livewire;

test('component can be rendered', function () {
    Livewire::test(Autocomplete::class)->assertStatus(200);
});

test('the render method returns the correct view', function () {
    $view = Livewire::test(Autocomplete::class)->instance()->render();

    expect($view->name())->toBe('livewire.autocomplete');
});

test('autocompleteTypes computed property returns correct data', function () {
    $result = Livewire::test(Autocomplete::class)->instance()->autocompleteTypes;
    $expected = collect(AutocompleteService::types())
        ->map(function (string $type) {
            return (new $type)->toArray();
        })
        ->all();

    expect($result)->toBe($expected);
});

test('setAutocompleteSearchParams sets matchedTypes and query when not empty', function () {
    $component = Livewire::test(Autocomplete::class);
    $component->call('setAutocompleteSearchParams', ['mentions'], 'username');

    $component->assertSet('matchedTypes', ['mentions'])
        ->assertSet('query', 'username');
});

test('setAutocompleteSearchParams does not set values when matchedTypes is empty', function () {
    $component = Livewire::test(Autocomplete::class);

    $component->call('setAutocompleteSearchParams', [], 'username');

    $component->assertSet('matchedTypes', [])
        ->assertSet('query', '');
});

test('setAutocompleteSearchParams resets values when matchedTypes is empty', function () {
    $component = Livewire::test(Autocomplete::class);

    $component->set('matchedTypes', ['mentions']);
    $component->set('query', 'user');

    $component->call('setAutocompleteSearchParams', [], 'bazz');

    $component->assertSet('matchedTypes', [])
        ->assertSet('query', '');
});

test('setAutocompleteSearchParams only uses matchedTypes that exist as an Autocomplete Type alias', function () {
    $component = Livewire::test(Autocomplete::class);

    $component->call('setAutocompleteSearchParams', ['mentions', 'foobar'], 'username');

    $component->assertSet('matchedTypes', ['mentions']) // note 'foobar' is missing
        ->assertSet('query', 'username');
});

test('autocompleteResults computed property returns correct data', function () {
    $user = App\Models\User::factory()->create(['username' => 'bazz']);
    App\Models\User::factory()->create(['username' => 'fellow']);

    $component = Livewire::test(Autocomplete::class);
    $component->set('matchedTypes', ['mentions']);
    $component->set('query', 'baz');

    /** @var Collection $result */
    $result = $component->instance()->autocompleteResults;

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1)
        ->and($result->first())->toBeInstanceOf(Result::class)
        ->and($result->first()->id)->toBe($user->id);
});

test('autocompleteResults returns empty collection when no matched types are set', function () {
    $component = Livewire::test(Autocomplete::class);

    $component->set('matchedTypes', []);
    $component->set('query', 'username');

    /** @var Collection $result */
    $result = $component->instance()->autocompleteResults;

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->isEmpty())->toBeTrue();
});

test('component properties are initialized correctly', function () {
    $component = Livewire::test(Autocomplete::class);

    expect($component->instance()->matchedTypes)->toBe([])
        ->and($component->instance()->query)->toBe('');
});
