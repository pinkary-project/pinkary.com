<?php

declare(strict_types=1);

use App\Services\Avatar;

test('avatar url', function () {
    $avatar = new Avatar(
        email: 'enunomaduro@gmail.com',
    );

    expect($avatar->url())->toBe('https://gravatar.com/avatar/86cfef5c1f5195df1a9db17a5f8ecb34455e1f0133a725de9acf7f2fb26ac6a1?s=300');
});
