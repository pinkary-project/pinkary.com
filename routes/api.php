<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Each domain owns one file below so a slice touching one domain cannot
| collide with another slice editing the same region of a shared route file.
|
*/

require __DIR__.'/api/v1/identity.php';
