<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface DatabaseBackupProvider
{
    /**
     * Parse the given "parsable" content.
     */
    public function performBackup(string $sourcePath, string $backupPath): void;
}
