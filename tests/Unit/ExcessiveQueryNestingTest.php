<?php

declare(strict_types=1);

use App\Support\ExcessiveQueryNesting;

test('matches the parse_str nesting warning', function (): void {
    $exception = new ErrorException(
        'parse_str(): Input variable nesting level exceeded 64. To increase the limit change max_input_nesting_level in php.ini.',
        0,
        E_WARNING,
        'vendor/symfony/http-foundation/HeaderUtils.php',
        244,
    );

    expect(ExcessiveQueryNesting::matches($exception))->toBeTrue();
});

test('matches when the reported level is not sixty four', function (): void {
    $exception = new ErrorException(
        'parse_str(): Input variable nesting level exceeded 512. To increase the limit change max_input_nesting_level in php.ini.',
        0,
        E_WARNING,
        'vendor/symfony/http-foundation/HeaderUtils.php',
        244,
    );

    expect(ExcessiveQueryNesting::matches($exception))->toBeTrue();
});

test('does not match an unrelated error exception', function (): void {
    $exception = new ErrorException('Undefined array key "page"', 0, E_WARNING, 'app/Support/Foo.php', 12);

    expect(ExcessiveQueryNesting::matches($exception))->toBeFalse();
});

test('does not match an unrelated throwable', function (): void {
    expect(ExcessiveQueryNesting::matches(new RuntimeException('parse_str(): Input variable nesting level exceeded 64.')))
        ->toBeFalse();
});

test('does not match a non parse_str nesting warning', function (): void {
    $exception = new ErrorException(
        'json_decode(): Input variable nesting level exceeded 64.',
        0,
        E_WARNING,
        'app/Support/Foo.php',
        12,
    );

    expect(ExcessiveQueryNesting::matches($exception))->toBeFalse();
});
