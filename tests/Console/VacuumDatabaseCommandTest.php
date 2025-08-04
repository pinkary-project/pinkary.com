<?php

declare(strict_types=1);

use App\Console\Commands\VacuumDatabaseCommand;
use Illuminate\Support\Facades\DB;

test('vacuum database', function () {
    DB::shouldReceive('statement')
        ->once()
        ->with('VACUUM')
        ->andReturnTrue();

    $this->artisan(VacuumDatabaseCommand::class)
        ->assertExitCode(0);
});
