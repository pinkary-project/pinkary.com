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

        Schema::connection($connection)->create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');
        });

        Schema::connection($connection)->create('cache', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::connection($connection)->create('jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
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
    DB::connection('migration_source')->table('sessions')->insert([
        'id' => 'session-1',
        'user_id' => 10,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'serialized',
        'last_activity' => 123456,
    ]);
    DB::connection('migration_source')->table('cache')->insert([
        'key' => 'settings',
        'value' => 'value',
        'expiration' => 999999,
    ]);
    DB::connection('migration_source')->table('jobs')->insert([
        'id' => 15,
        'queue' => 'default',
        'payload' => '{"job":"test"}',
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => 123,
        'created_at' => 123,
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
        ->toBe('Abigail')
        ->and(DB::connection('migration_target')->table('sessions')->where('id', 'session-1')->value('user_id'))
        ->toBe(10)
        ->and(DB::connection('migration_target')->table('cache')->where('key', 'settings')->value('value'))
        ->toBe('value')
        ->and(DB::connection('migration_target')->table('jobs')->where('id', 15)->value('queue'))
        ->toBe('default');
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
