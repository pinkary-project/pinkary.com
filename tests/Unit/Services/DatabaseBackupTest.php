<?php

declare(strict_types=1);

use App\Services\DatabaseBackup;

test('backs up a sqlite database', function () {
    // Create temporary database files
    $sourceDBPath = tempnam(sys_get_temp_dir(), 'source_db_');
    $backupDBPath = tempnam(sys_get_temp_dir(), 'backup_db_');

    $sourceDB = new SQLite3($sourceDBPath);
    $backupDB = new SQLite3($backupDBPath);

    try {
        // Check if backup DB is empty
        $result = $backupDB->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $this->assertEmpty($result->fetchArray(), 'Backup database should not contain any user tables initially');

        // Set up source database
        $sourceDB->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
        $sourceDB->exec("INSERT INTO test (name) VALUES ('data1'), ('data2'), ('data3')");

        // Perform backup
        $backupService = new DatabaseBackup();
        $backupService->performBackup($sourceDBPath, $backupDBPath);

        // Verify backup
        $result = $backupDB->query('SELECT name FROM test ORDER BY id');
        $expected = ['data1', 'data2', 'data3'];
        $i = 0;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->assertEquals($expected[$i], $row['name'], "Row $i should match");
            $i++;
        }
        $this->assertEquals(3, $i, 'Should have backed up 3 rows');
    } finally {
        if (isset($sourceDB)) {
            $sourceDB->close();
        }
        if (isset($backupDB)) {
            $backupDB->close();
        }
        if (file_exists($sourceDBPath)) {
            unlink($sourceDBPath);
        }
        if (file_exists($backupDBPath)) {
            unlink($backupDBPath);
        }
    }
});
