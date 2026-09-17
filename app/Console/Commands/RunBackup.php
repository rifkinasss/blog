<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RunBackup extends Command
{
    protected $signature = 'operations:backup';

    protected $description = 'Run the configured backup and record a safe operational summary.';

    public function handle(): int
    {
        try {
            $exitCode = Artisan::call('backup:run', ['--disable-notifications' => true]);

            if ($exitCode !== self::SUCCESS) {
                throw new \RuntimeException('Backup command returned a non-zero exit status.');
            }

            $disk = Storage::disk(config('backup.backup.destination.disks.0', 'local'));
            $files = collect($disk->allFiles(config('backup.backup.name')));
            $latest = $files->sortByDesc(fn (string $path) => $disk->lastModified($path))->first();

            SiteSetting::put('backup_last_success_at', now()->toIso8601String());
            SiteSetting::put('backup_last_size_bytes', (string) ($latest ? $disk->size($latest) : 0));
            SiteSetting::put('backup_last_failure_at', null);
            AuditLog::recordSystem('backup.completed', null, ['size_bytes' => $latest ? $disk->size($latest) : 0]);
            $this->info('Backup completed.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            SiteSetting::put('backup_last_failure_at', now()->toIso8601String());
            AuditLog::recordSystem('backup.failed');
            report($exception);
            $this->error('Backup failed. Check the application logs using the request or command timestamp.');

            return self::FAILURE;
        }
    }
}
