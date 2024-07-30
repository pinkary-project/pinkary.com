<?php

declare(strict_types=1);

use App\Services\Autocomplete\DynamicAutocompleteService;
use App\Services\Autocomplete\Results\Collection;
use App\Services\Autocomplete\Types\Mentions;
use App\Services\Autocomplete\Types\Type;

final readonly class TestType extends Type
{
    public function search(string $query): Collection
    {
        return new Collection([$query]); // @phpstan-ignore-line
    }

    public function delimiter(): string
    {
        return '/';
    }

    public function matchExpression(): string
    {
        return '[a-z]+';
    }
}

test('types method returns registered autocomplete types', function () {
    $types = DynamicAutocompleteService::types();

    expect($types)->toBe(['mentions' => Mentions::class]);
});

test('typeClassFor method returns the correct class for a given type', function () {
    $class = DynamicAutocompleteService::typeClassFor('mentions');

    expect($class)->toBe(Mentions::class);
});

// @phpstan-ignore-next-line
test('typeClassFor method throws an exception for an invalid type', function () {
    DynamicAutocompleteService::typeClassFor('invalid_type');
})->throws(ErrorException::class);

test('search method works when given a Type instance', function () {
    addTestTypeToService();

    $autocomplete = new DynamicAutocompleteService();
    $testType = new TestType();

    $result = $autocomplete->search($testType, '/query');
    expect($result->toArray())->toBe(['query']); // also proves the query was prepared

    resetService();
});

test('search method works when given a string type', function () {
    addTestTypeToService();

    $autocomplete = new DynamicAutocompleteService();

    $result = $autocomplete->search('test_type', '/query');
    expect($result->toArray())->toBe(['query']); // also proves the query was prepared

    resetService();
});

function addTestTypeToService(): void
{
    $reflection = new ReflectionClass(DynamicAutocompleteService::class);
    $reflection->setStaticPropertyValue(
        'types',
        [
            'test_type' => TestType::class,
            ...DynamicAutocompleteService::types(),
        ]
    );
}

function resetService(): void
{
    $reflection = new ReflectionClass(DynamicAutocompleteService::class);
    $property = $reflection->getProperty('types');
    $types = $property->getValue();
    unset($types['test_type']); // @phpstan-ignore-line
    $property->setValue(null, $types);
}
