<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:database {--name= : Custom backup name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database backup...');

        try {
            // Get database configuration
            $database = config('database.default');
            $connection = config("database.connections.{$database}");

            if ($database !== 'mysql') {
                $this->error('Only MySQL databases are supported for backup.');

                return self::FAILURE;
            }

            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups/database');
            if (! file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Generate backup filename
            $filename = $this->option('name')
                ? $this->option('name').'.sql'
                : 'backup-'.date('Y-m-d_H-i-s').'.sql';

            $fullPath = $backupPath.'/'.$filename;

            // Build mysqldump command for Windows
            $host = $connection['host'];
            $port = $connection['port'] ?? 3306;
            $dbName = $connection['database'];
            $username = $connection['username'];
            $password = $connection['password'];

            // Try to find mysqldump
            $mysqldumpPath = $this->findMysqldump();

            if (! $mysqldumpPath) {
                $this->error('mysqldump not found. Please ensure MySQL is installed and in your PATH.');

                return self::FAILURE;
            }

            // Build command (Windows-compatible)
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s --port=%d %s > "%s" 2>&1',
                $mysqldumpPath,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                $port,
                escapeshellarg($dbName),
                $fullPath
            );

            // Execute backup
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || ! file_exists($fullPath) || filesize($fullPath) === 0) {
                $this->error('Database backup failed!');
                $this->error('Error: '.implode("\n", $output));

                Log::channel('security')->error('Database backup failed', [
                    'output' => $output,
                    'command' => $command,
                ]);

                return self::FAILURE;
            }

            // Get file size
            $fileSize = $this->formatBytes(filesize($fullPath));

            $this->info('✓ Database backup created successfully!');
            $this->info("  Location: {$fullPath}");
            $this->info("  Size: {$fileSize}");

            // Log successful backup
            Log::channel('security')->info('Database backup created', [
                'filename' => $filename,
                'size' => filesize($fullPath),
                'path' => $fullPath,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: '.$e->getMessage());
            Log::channel('security')->error('Database backup exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Find mysqldump executable
     */
    protected function findMysqldump(): ?string
    {
        // Common Windows paths
        $possiblePaths = [
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\wamp64\bin\mysql\mysql8.0.27\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysqldump.exe',
            'mysqldump', // If in PATH
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try to find in PATH
        exec('where mysqldump 2>nul', $output, $returnCode);
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
