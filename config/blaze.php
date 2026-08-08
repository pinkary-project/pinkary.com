<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Blaze
    |--------------------------------------------------------------------------
    |
    | When set to false, Blaze will skip all compilation and optimization.
    | Templates will be rendered using standard Blade without any Blaze
    | processing. This is useful for debugging or disabling Blaze in
    | specific environments.
    |
    */

    'enabled' => env('BLAZE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, Blaze will register the debugger middleware and output
    | additional diagnostic information about the compilation pipeline.
    |
    */

    'debug' => env('BLAZE_DEBUG', false),

];
