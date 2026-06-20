<?php

declare(strict_types=1);

use App\Console\Commands\MigrateSqliteToMysqlCommand;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['migration_source', 'migration_target'] as $connection) {
        config()->set("database.connections.{$connection}", [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge($connection);

        Schema::connection($connection)->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }
});

test('command is registered with correct signature', function () {
    $this->artisan('list')
        ->expectsOutputToContain('migrate:sqlite-to-mysql');
});

test('command has expected options', function () {
    $command = new MigrateSqliteToMysqlCommand;

    $definition = $command->getDefinition();

    expect($definition->hasOption('force'))->toBeTrue()
        ->and($definition->hasOption('fresh'))->toBeTrue()
        ->and($definition->hasOption('source'))->toBeTrue()
        ->and($definition->hasOption('target'))->toBeTrue()
        ->and($definition->hasOption('skip-migrations'))->toBeTrue();
});

test('copies source data into an empty target', function () {
    DB::connection('migration_source')->table('users')->insert([
        ['id' => 10, 'name' => 'Taylor'],
        ['id' => 20, 'name' => 'Abigail'],
    ]);

    $this->artisan(MigrateSqliteToMysqlCommand::class, [
        '--source' => 'migration_source',
        '--target' => 'migration_target',
        '--skip-migrations' => true,
        '--force' => true,
    ])->assertSuccessful();

    expect(DB::connection('migration_target')->table('users')->orderBy('id')->get()->all())
        ->toHaveCount(2)
        ->and(DB::connection('migration_target')->table('users')->where('id', 20)->value('name'))
        ->toBe('Abigail');
});

test('refuses to import into a non-empty target', function () {
    DB::connection('migration_source')->table('users')->insert(['id' => 10, 'name' => 'Source']);
    DB::connection('migration_target')->table('users')->insert(['id' => 99, 'name' => 'Target']);

    $this->artisan(MigrateSqliteToMysqlCommand::class, [
        '--source' => 'migration_source',
        '--target' => 'migration_target',
        '--skip-migrations' => true,
        '--force' => true,
    ])->assertFailed();

    expect(DB::connection('migration_target')->table('users')->pluck('name')->all())
        ->toBe(['Target']);
});
