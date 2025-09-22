<?php

declare(strict_types=1);

namespace App\Console;

use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 清空数组
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 每天凌晨1点检查过期付款
        $schedule->command('payments:check-overdue')
            ->dailyAt('01:00')
            ->withoutOverlapping();

        // 每小时检查一次是否需要自动生成采购单
        $schedule->command('purchase:auto-generate')
            ->hourly()
            ->withoutOverlapping();

        // 每周一凌晨4点执行一次数据库备份
        $schedule->command('db:backup')
            ->weekly()
            ->mondays()
            ->at('04:00')
            ->withoutOverlapping();

        // 每天凌晨4点30分清理旧日志
        $schedule->command('log:clear --older-than=30 --without-confirmation')
            ->dailyAt('04:30')
            ->withoutOverlapping();

        // 自动采购功能调度
        $autoEnabled = Setting::where('key', 'auto_purchase_enabled')->first()?->value === 'true';
        if ($autoEnabled) {
            $frequency = Setting::where('key', 'auto_purchase_frequency')->first()?->value ?? 'daily';
            
            $command = $schedule->command('purchase:auto-generate');
            
            switch ($frequency) {
                case 'daily':
                    $command->dailyAt('08:00');
                    break;
                case 'weekly':
                    $command->weeklyOn(1, '08:00'); // 每周一上午8点
                    break;
                case 'twice_weekly':
                    $command->weeklyOn(1, '08:00')->weeklyOn(4, '08:00'); // 每周一和周四上午8点
                    break;
                default:
                    $command->dailyAt('08:00');
            }
            
            $command->appendOutputTo(storage_path('logs/auto-purchase.log'));
        }

        // 备份功能调度
        $backupEnabled = Setting::where('key', 'backup_enabled')->first()?->value === 'true';
        if ($backupEnabled) {
            $backupFrequency = Setting::where('key', 'backup_frequency')->first()?->value ?? 'daily';
            
            $backupCommand = $schedule->command('db:backup');
            
            switch ($backupFrequency) {
                case 'daily':
                    $backupCommand->dailyAt('23:00');
                    break;
                case 'weekly':
                    $backupCommand->weeklyOn(0, '23:00'); // 每周日晚上11点
                    break;
                case 'monthly':
                    $backupCommand->monthlyOn(1, '23:00'); // 每月1日晚上11点
                    break;
                default:
                    $backupCommand->dailyAt('23:00');
            }
            
            $backupCommand->appendOutputTo(storage_path('logs/db-backup.log'));
        }
        
        // 清理过期备份（每天下午4:45运行）
        $schedule->command('backup:clean')
            ->dailyAt('16:45')
            ->appendOutputTo(storage_path('logs/backup-cleanup.log'));

        // 每天凌晨2点聚合前一天的产品展示数据
        $schedule->command('analytics:aggregate-stats')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/analytics-aggregation.log'));

        // 每天00:00执行新品状态更新
        $schedule->command('products:update-new-status')
            ->dailyAt('00:00')
            ->appendOutputTo(storage_path('logs/new-products-status.log'));

        // 每日凌晨0点处理自动添加折扣商品
        $schedule->command('products:auto-add-discounted')
            ->dailyAt('00:00')
            ->appendOutputTo(storage_path('logs/auto-add-discounted.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * 获取所有命令
     */
    protected function getCommands(): array
    {
        return [
            \App\Console\Commands\BackupDatabase::class,
            \App\Console\Commands\AutoGeneratePurchaseOrders::class,
        ];
    }
}
