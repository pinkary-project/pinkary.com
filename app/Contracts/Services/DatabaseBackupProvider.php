<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface DatabaseBackupProvider
{
    public function performBackup(string $sourcePath, string $backupPath): void;
}
