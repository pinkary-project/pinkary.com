<?php

declare(strict_types=1);

arch('parsable content providers')
    ->expect('App\Services\ParsableContentProviders')
    ->toImplement('App\Contracts\ParsableContentProvider');
