<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class VacuumDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:vacuum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs VACUUM command on the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::statement('VACUUM');
    }
}
