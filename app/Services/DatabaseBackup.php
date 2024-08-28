<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\DatabaseBackupProvider;
use Exception;
use SQLite3;

final class DatabaseBackup implements DatabaseBackupProvider
{
    public function performBackup(string $sourcePath, string $backupPath): void
    {
        $sourceDB = null;
        $backupDB = null;

        try {
            $sourceDB = new SQLite3($sourcePath, SQLITE3_OPEN_READONLY);
            $backupDB = new SQLite3($backupPath, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

            $sourceDB->backup($backupDB);
        } catch (Exception $e) {
            // Log the error or handle it as per your application's error handling policy
            throw new Exception('Backup failed: '.$e->getMessage(), 0, $e);
        } finally {
            $this->closeDatabase($backupDB);
            $this->closeDatabase($sourceDB);
        }
    }

    private function closeDatabase(?SQLite3 $database): void
    {
        if ($database instanceof SQLite3) {
            $database->close();
        }
    }
}
