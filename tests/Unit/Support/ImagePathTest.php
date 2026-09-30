<?php

declare(strict_types=1);

use App\Support\ImagePath;

test('a composer style relative reference is returned unchanged', function (): void {
    expect(ImagePath::toRelative('images/2026-09-29/abc.jpg'))
        ->toBe('images/2026-09-29/abc.jpg');
});

test('an absolute url reduces to the disk relative path', function (): void {
    // What the mobile API hands a client to embed in a post.
    expect(ImagePath::toRelative('https://cdn.example.com/images/2026-09-29/abc.jpg'))
        ->toBe('images/2026-09-29/abc.jpg');
});

test('a disk rooted reference reduces to the disk relative path', function (): void {
    expect(ImagePath::toRelative('/storage/images/2026-09-29/abc.jpg'))
        ->toBe('images/2026-09-29/abc.jpg')
        ->and(ImagePath::toRelative('https://pinkary.test/storage/images/2026-09-29/abc.jpg'))->toBe('images/2026-09-29/abc.jpg');
});

test('a query string does not defeat the match', function (): void {
    expect(ImagePath::toRelative('https://cdn.example.com/images/2026-09-29/abc.jpg?v=2'))
        ->toBe('images/2026-09-29/abc.jpg');
});

test('a reference to something other than an upload is not mistaken for one', function (): void {
    expect(ImagePath::toRelative('https://example.com/logo.png'))
        ->toBe('logo.png');
});

test('absolute references are detected', function (): void {
    expect(ImagePath::isAbsolute('https://cdn.example.com/images/a.jpg'))->toBeTrue()
        ->and(ImagePath::isAbsolute('http://cdn.example.com/images/a.jpg'))->toBeTrue()
        ->and(ImagePath::isAbsolute('images/a.jpg'))->toBeFalse()
        ->and(ImagePath::isAbsolute('/storage/images/a.jpg'))->toBeFalse();
});
