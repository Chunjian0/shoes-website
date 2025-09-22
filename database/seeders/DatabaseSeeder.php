<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminSeeder::class,
            MessageTemplateSeeder::class,
            // NotificationLogSeeder::class, // 已被移除避免自动生成测试数据
        ]);
    }
}
