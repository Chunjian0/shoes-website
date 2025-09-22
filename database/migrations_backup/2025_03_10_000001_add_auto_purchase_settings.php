<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 添加自动采购启用设置
        Setting::create([
            'key' => 'auto_purchase_enabled',
            'value' => 'false',
            'type' => 'boolean',
            'group' => 'purchase',
            'label' => '启用自动采购',
            'description' => '是否启用自动检查库存并生成采购单功能',
            'options' => null,
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 添加自动采购频率设置
        Setting::create([
            'key' => 'auto_purchase_frequency',
            'value' => 'daily',
            'type' => 'string',
            'group' => 'purchase',
            'label' => '自动采购频率',
            'description' => '系统自动检查库存并生成采购单的频率',
            'options' => json_encode(['daily', 'weekly', 'twice_weekly']),
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 添加采购数量计算方法设置
        Setting::create([
            'key' => 'auto_purchase_quantity_method',
            'value' => 'min_stock',
            'type' => 'string',
            'group' => 'purchase',
            'label' => '自动采购数量计算方法',
            'description' => '系统计算自动采购商品数量的方法',
            'options' => json_encode([
                'min_stock' => '按最小库存水平',
                'double_min_stock' => '按两倍最小库存水平',
                'replenish_only' => '仅补充至最小库存',
            ]),
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 添加默认仓库设置
        Setting::create([
            'key' => 'default_warehouse_id',
            'value' => null,
            'type' => 'integer',
            'group' => 'inventory',
            'label' => '默认仓库',
            'description' => '自动采购时使用的默认仓库ID',
            'options' => null,
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 添加自动采购通知设置
        Setting::create([
            'key' => 'auto_purchase_notify_users',
            'value' => '[]',
            'type' => 'json',
            'group' => 'purchase',
            'label' => '自动采购通知用户',
            'description' => '系统生成采购单后需要通知的用户ID列表',
            'options' => null,
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 添加自动采购黑名单设置
        Setting::create([
            'key' => 'auto_purchase_blacklist',
            'value' => '[]',
            'type' => 'json',
            'group' => 'purchase',
            'label' => '自动采购黑名单',
            'description' => '不进行自动采购的商品ID列表',
            'options' => null,
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', [
            'auto_purchase_enabled',
            'auto_purchase_frequency',
            'auto_purchase_quantity_method',
            'default_warehouse_id',
            'auto_purchase_notify_users',
            'auto_purchase_blacklist',
        ])->delete();
    }
}; 