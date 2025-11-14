<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:restore {file : Backup file name (without path)}';

    /**
     * The console command description.
     */
    protected $description = 'Restore database from a backup file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filename = $this->argument('file');
        $backupPath = storage_path('app/backups/database');
        $fullPath = $backupPath.'/'.$filename;

        // Check if backup file exists
        if (! file_exists($fullPath)) {
            $this->error("Backup file not found: {$filename}");
            $this->info('Available backups:');
            $this->listBackups();

            return self::FAILURE;
        }

        // Confirm restore
        $this->warn('WARNING: This will replace your current database with the backup!');
        $this->info("Backup file: {$filename}");
        $this->info('Size: '.$this->formatBytes(filesize($fullPath)));

        if (! $this->confirm('Do you want to continue?')) {
            $this->info('Restore cancelled.');

            return self::SUCCESS;
        }

        try {
            // Get database configuration
            $database = config('database.default');
            $connection = config("database.connections.{$database}");

            if ($database !== 'mysql') {
                $this->error('Only MySQL databases are supported for restore.');

                return self::FAILURE;
            }

            $host = $connection['host'];
            $port = $connection['port'] ?? 3306;
            $dbName = $connection['database'];
            $username = $connection['username'];
            $password = $connection['password'];

            // Try to find mysql
            $mysqlPath = $this->findMysql();

            if (! $mysqlPath) {
                $this->error('mysql not found. Please ensure MySQL is installed and in your PATH.');

                return self::FAILURE;
            }

            $this->info('Restoring database...');

            // Build mysql restore command
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s --port=%d %s < "%s" 2>&1',
                $mysqlPath,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                $port,
                escapeshellarg($dbName),
                $fullPath
            );

            // Execute restore
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $this->error('Database restore failed!');
                $this->error('Error: '.implode("\n", $output));

                Log::channel('security')->error('Database restore failed', [
                    'filename' => $filename,
                    'output' => $output,
                ]);

                return self::FAILURE;
            }

            $this->info("✓ Database restored successfully from {$filename}");

            // Log successful restore
            Log::channel('security')->warning('Database restored from backup', [
                'filename' => $filename,
                'size' => filesize($fullPath),
                'restored_by' => get_current_user(),
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Restore failed: '.$e->getMessage());
            Log::channel('security')->error('Database restore exception', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * List available backups
     */
    protected function listBackups(): void
    {
        $backupPath = storage_path('app/backups/database');

        if (! file_exists($backupPath)) {
            $this->info('  No backups found.');

            return;
        }

        $files = File::files($backupPath);

        if (empty($files)) {
            $this->info('  No backups found.');

            return;
        }

        foreach ($files as $file) {
            $size = $this->formatBytes(filesize($file->getPathname()));
            $date = date('Y-m-d H:i:s', filectime($file->getPathname()));
            $this->info("  - {$file->getFilename()} ({$size}) - {$date}");
        }
    }

    /**
     * Find mysql executable
     */
    protected function findMysql(): ?string
    {
        // Common Windows paths
        $possiblePaths = [
            'C:\xampp\mysql\bin\mysql.exe',
            'C:\wamp64\bin\mysql\mysql8.0.27\bin\mysql.exe',
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
            'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe',
            'mysql', // If in PATH
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try to find in PATH
        exec('where mysql 2>nul', $output, $returnCode);
        if ($returnCode === 0 && ! empty($output[0])) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Format bytes to human-readable size
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
