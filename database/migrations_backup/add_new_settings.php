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
        if (!Schema::hasColumn('products', 'new_until_date')) {
            Schema::table('products', function (Blueprint $table) {
                $table->date('new_until_date')->nullable()->after('is_new_arrival');
            });
        }
        
        // 添加新的设置项
        $this->addNewSetting('homepage_new_product_days', '30', 'Number of days to display new products');
        $this->addNewSetting('homepage_auto_add_new_products', 'false', 'Automatically add new products to new arrivals');
        $this->addNewSetting('homepage_auto_add_sale_products', 'false', 'Automatically add discounted products to sale');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'new_until_date')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('new_until_date');
            });
        }
        
        // 删除设置项
        Setting::where('key', 'homepage_new_product_days')->delete();
        Setting::where('key', 'homepage_auto_add_new_products')->delete();
        Setting::where('key', 'homepage_auto_add_sale_products')->delete();
    }
    
    /**
     * 添加新的设置项如果不存在
     */
    private function addNewSetting(string $key, string $value, string $description)
    {
        if (!Setting::where('key', $key)->exists()) {
            Setting::create([
                'key' => $key,
                'value' => $value,
                'description' => $description
            ]);
        }
    }
}; 