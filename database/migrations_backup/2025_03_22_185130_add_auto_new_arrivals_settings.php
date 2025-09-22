<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 添加new_until_date字段到products表
        if (!Schema::hasColumn('products', 'new_until_date')) {
            Schema::table('products', function (Blueprint $table) {
                $table->date('new_until_date')->nullable()->comment('新品展示截止日期');
            });
        }
        
        // 添加新品自动设置相关的配置
        $settings = [
            [
                'key' => 'auto_add_new_products',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'homepage',
                'label' => '自动添加新品',
                'description' => '是否自动将新创建的商品添加到新品区域',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'new_products_display_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'homepage',
                'label' => '新品展示天数',
                'description' => '新品在首页展示的默认天数',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($settings as $setting) {
            // 检查设置是否已存在
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            
            if (!$exists) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 移除新品日期字段
        if (Schema::hasColumn('products', 'new_until_date')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('new_until_date');
            });
        }
        
        // 删除相关设置
        DB::table('settings')->whereIn('key', [
            'auto_add_new_products',
            'new_products_display_days'
        ])->delete();
    }
};
