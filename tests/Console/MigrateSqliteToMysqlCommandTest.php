<?php

declare(strict_types=1);

use App\Console\Commands\MigrateSqliteToMysqlCommand;

test('command is registered with correct signature', function () {
    $this->artisan('list')
        ->expectsOutputToContain('migrate:sqlite-to-mysql');
});

test('command has expected options', function () {
    $command = new MigrateSqliteToMysqlCommand;

    $definition = $command->getDefinition();

    expect($definition->hasOption('force'))->toBeTrue()
        ->and($definition->hasOption('fresh'))->toBeTrue();
});
