<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanNavicatBackups extends Command
{
    protected $signature = 'backup:clean-navicat {path} {--days=30}';
    protected $description = 'Clean up Navicat Backup files';

    public function handle(): void
    {
        $path = $this->argument('path');
        $days = $this->option('days');

        if (!file_exists($path)) {
            $this->error("The backup directory does not exist: {$path}");
            return;
        }

        $now = Carbon::now();
        $count = 0;
        $size = 0;

        // Get all backup files
        $files = glob($path . '/*.sql');
        $files = array_merge($files, glob($path . '/*.sql.zip'));
        $files = array_merge($files, glob($path . '/*.bak'));
        $files = array_merge($files, glob($path . '/*.psc'));

        foreach ($files as $file) {
            $fileDate = Carbon::createFromTimestamp(filemtime($file));
            
            // If the file exceeds the specified number of days
            if ($fileDate->diffInDays($now) > $days) {
                $fileSize = filesize($file);
                try {
                    unlink($file);
                    $count++;
                    $size += $fileSize;
                    $this->info("Deleted: " . basename($file));
                } catch (\Exception $e) {
                    $this->error("Deletion failed: " . basename($file) . " - " . $e->getMessage());
                }
            }
        }

        // Convert size to readable format
        $readableSize = $this->formatBytes($size);

        $this->info("Cleaning is complete!");
        $this->info("Delete in total {$count} A file");
        $this->info("Free up space: {$readableSize}");
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 