<?php

declare(strict_types=1);

use App\Services\Autocomplete;
use App\Services\Autocomplete\Types\Hashtags;
use App\Services\Autocomplete\Types\Mentions;
use Tests\Fixtures\TestType;

test('types method returns registered autocomplete types', function () {
    $types = Autocomplete::types();

    expect($types)->toBe([
        'hashtags' => Hashtags::class,
        'mentions' => Mentions::class,
    ]);
});

test('typeClassFor method returns the correct class for a given type', function () {
    $class = Autocomplete::typeClassFor('mentions');

    expect($class)->toBe(Mentions::class);
});

// @phpstan-ignore-next-line
test('typeClassFor method throws an exception for an invalid type', function () {
    Autocomplete::typeClassFor('invalid_type');
})->throws(ErrorException::class);

test('search method works when given a Type instance', function () {
    addTestTypeToService();

    $autocomplete = new Autocomplete();
    $testType = new TestType();

    $result = $autocomplete->search($testType, '/query');
    expect($result->toArray())->toBe(['query']); // also proves the query was prepared

    resetService();
});

test('search method works when given a string type', function () {
    addTestTypeToService();

    $autocomplete = new Autocomplete();

    $result = $autocomplete->search('test_type', '/query');
    expect($result->toArray())->toBe(['query']); // also proves the query was prepared

    resetService();
});

function addTestTypeToService(): void
{
    $reflection = new ReflectionClass(Autocomplete::class);
    $reflection->setStaticPropertyValue(
        'types',
        [
            'test_type' => TestType::class,
            ...Autocomplete::types(),
        ]
    );
}

function resetService(): void
{
    $reflection = new ReflectionClass(Autocomplete::class);
    $property = $reflection->getProperty('types');
    $types = $property->getValue();
    unset($types['test_type']); // @phpstan-ignore-line
    $property->setValue(null, $types);
}
