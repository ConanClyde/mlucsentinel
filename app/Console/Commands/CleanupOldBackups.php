<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupOldBackups extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:cleanup {--days=30 : Number of days to keep backups} {--keep-monthly : Keep one backup per month}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old backup files based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting backup cleanup...');

        $days = (int) $this->option('days');
        $keepMonthly = $this->option('keep-monthly');

        try {
            $databasePath = storage_path('app/backups/database');
            $filesPath = storage_path('app/backups/files');

            $totalDeleted = 0;
            $totalKept = 0;
            $monthlyArchives = [];

            // Clean database backups
            if (file_exists($databasePath)) {
                $this->info("Cleaning database backups (older than {$days} days)...");
                [$deleted, $kept, $monthly] = $this->cleanupDirectory($databasePath, $days, $keepMonthly);
                $totalDeleted += $deleted;
                $totalKept += $kept;
                $monthlyArchives = array_merge($monthlyArchives, $monthly);
            }

            // Clean files backups
            if (file_exists($filesPath)) {
                $this->info("Cleaning files backups (older than {$days} days)...");
                [$deleted, $kept, $monthly] = $this->cleanupDirectory($filesPath, $days, $keepMonthly);
                $totalDeleted += $deleted;
                $totalKept += $kept;
                $monthlyArchives = array_merge($monthlyArchives, $monthly);
            }

            $this->info('✓ Cleanup completed!');
            $this->info("  Deleted: {$totalDeleted} old backups");
            $this->info("  Kept: {$totalKept} recent backups");

            if ($keepMonthly && count($monthlyArchives) > 0) {
                $this->info('  Monthly archives: '.count($monthlyArchives));
            }

            // Log cleanup
            Log::channel('security')->info('Backup cleanup completed', [
                'days' => $days,
                'deleted' => $totalDeleted,
                'kept' => $totalKept,
                'monthly_archives' => count($monthlyArchives),
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Cleanup failed: '.$e->getMessage());
            Log::channel('security')->error('Backup cleanup exception', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Clean up backups in a directory
     */
    protected function cleanupDirectory(string $path, int $days, bool $keepMonthly): array
    {
        $files = File::files($path);
        $cutoffDate = now()->subDays($days);
        $deleted = 0;
        $kept = 0;
        $monthlyArchives = [];

        // Group files by month if keeping monthly backups
        $filesByMonth = [];
        if ($keepMonthly) {
            foreach ($files as $file) {
                $fileTime = filectime($file->getPathname());
                $month = date('Y-m', $fileTime);
                if (! isset($filesByMonth[$month])) {
                    $filesByMonth[$month] = [];
                }
                $filesByMonth[$month][] = $file;
            }
        }

        foreach ($files as $file) {
            $fileTime = filectime($file->getPathname());
            $fileDate = \Carbon\Carbon::createFromTimestamp($fileTime);

            // Check if file is older than cutoff date
            if ($fileDate->lessThan($cutoffDate)) {
                // Check if we should keep as monthly archive
                $isMonthlyArchive = false;
                if ($keepMonthly) {
                    $month = date('Y-m', $fileTime);
                    // Keep the newest file of each month
                    if (isset($filesByMonth[$month])) {
                        $newestFile = collect($filesByMonth[$month])
                            ->sortByDesc(fn ($f) => filectime($f->getPathname()))
                            ->first();

                        if ($newestFile->getPathname() === $file->getPathname()) {
                            $isMonthlyArchive = true;
                            $monthlyArchives[] = $file->getFilename();
                        }
                    }
                }

                if ($isMonthlyArchive) {
                    $kept++;
                } else {
                    // Delete old backup
                    File::delete($file->getPathname());
                    $deleted++;
                }
            } else {
                $kept++;
            }
        }

        return [$deleted, $kept, $monthlyArchives];
    }
}
