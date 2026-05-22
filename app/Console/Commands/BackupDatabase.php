<?php

// المسار: app/Console/Commands/BackupDatabase.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature   = 'erp:backup-database {--keep=7 : Number of backup files to keep}';
    protected $description = 'Create a MySQL database backup and store it in storage/backups';

    public function handle(): int
    {
        $this->info('Starting database backup...');

        $dbHost     = config('database.connections.mysql.host');
        $dbPort     = config('database.connections.mysql.port', 3306);
        $dbName     = config('database.connections.mysql.database');
        $dbUser     = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');

        $timestamp  = Carbon::now('Africa/Khartoum')->format('Y-m-d_H-i-s');
        $filename   = "backup_{$dbName}_{$timestamp}.sql";
        $backupDir  = storage_path('app/backups');

        // Ensure backup directory exists
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filePath = "{$backupDir}/{$filename}";

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Backup failed! Return code: ' . $returnCode);
            $this->error(implode("\n", $output));
            return self::FAILURE;
        }

        // Gzip the backup
        $gzipCommand = "gzip {$filePath}";
        exec($gzipCommand);

        $gzFilePath = $filePath . '.gz';
        $sizeMb     = file_exists($gzFilePath)
            ? round(filesize($gzFilePath) / 1048576, 2)
            : 0;

        $this->info("✅ Backup created: {$filename}.gz ({$sizeMb} MB)");

        // Clean old backups, keep only the last N
        $this->cleanOldBackups($backupDir, (int) $this->option('keep'));

        // Notify admins
        $this->notifyAdmins($filename . '.gz', $sizeMb);

        return self::SUCCESS;
    }

    /**
     * Delete old backup files, keeping only the most recent $keep files.
     */
    private function cleanOldBackups(string $dir, int $keep): void
    {
        $files = glob("{$dir}/backup_*.sql.gz");
        if (!$files) return;

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        $toDelete = array_slice($files, $keep);
        foreach ($toDelete as $file) {
            unlink($file);
            $this->line("Deleted old backup: " . basename($file));
        }
    }

    /**
     * Send notification to admin users about successful backup.
     */
    private function notifyAdmins(string $filename, float $sizeMb): void
    {
        try {
            $admins = \App\Models\User::whereHas(
                'roles', fn($q) => $q->where('name', 'admin')
            )->get();

            foreach ($admins as $admin) {
                \App\Models\Notification::notify(
                    userId: $admin->id,
                    type: 'system',
                    titleAr: 'نسخ احتياطي ناجح',
                    titleEn: 'Backup Successful',
                    bodyAr: "تم إنشاء نسخة احتياطية: {$filename} ({$sizeMb} MB)",
                    bodyEn: "Backup created: {$filename} ({$sizeMb} MB)",
                    icon: 'database',
                    color: 'green'
                );
            }
        } catch (\Throwable $e) {
            // Do not fail the backup if notification fails
            $this->warn('Could not send notifications: ' . $e->getMessage());
        }
    }
}
