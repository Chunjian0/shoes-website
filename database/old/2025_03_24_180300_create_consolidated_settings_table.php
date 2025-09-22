<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidated from the following migrations:
     * - 2025_02_17_013641_create_settings_table.php
     * - 2025_03_21_113118_add_new_settings.php
     * - 2025_03_22_140922_add_homepage_settings_to_settings_table.php
     * - 2025_03_22_185124_add_auto_new_arrivals_settings.php
     * - 2025_03_10_000001_add_auto_purchase_settings.php
     * - 2025_03_24_002726_create_setting_descriptions_table.php
     * - 2025_03_24_002802_check_setting_descriptions_table_exists.php
     */
    public function up(): void
    {
        // Create settings table
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->string('type')->default('text');
                $table->boolean('is_public')->default(false);
                $table->json('options')->nullable();
                $table->timestamps();
            });
            
            // 插入默认设置
            $this->insertDefaultSettings();
        }
        
        // Create setting_descriptions table
        if (!Schema::hasTable('setting_descriptions')) {
            Schema::create('setting_descriptions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->foreign('key')
                      ->references('key')
                      ->on('settings')
                      ->onDelete('cascade');
            });
        }
    }
    
    /**
     * Insert all default settings
     */
    private function insertDefaultSettings()
    {
        $settings = [
            // General settings
            [
                'key' => 'site_name', 
                'value' => 'Optic System', 
                'group' => 'general', 
                'type' => 'text', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'site_description', 
                'value' => 'Your complete eyewear solution', 
                'group' => 'general', 
                'type' => 'textarea', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'site_logo', 
                'value' => null, 
                'group' => 'general', 
                'type' => 'image', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'admin_email', 
                'value' => 'admin@example.com', 
                'group' => 'general', 
                'type' => 'email', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'currency', 
                'value' => 'USD', 
                'group' => 'general', 
                'type' => 'select', 
                'is_public' => true, 
                'options' => json_encode(['USD', 'EUR', 'GBP', 'CNY']),
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Company information
            [
                'key' => 'company_name', 
                'value' => 'Optic System Inc.', 
                'group' => 'company', 
                'type' => 'text', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_address', 
                'value' => '123 Main St', 
                'group' => 'company', 
                'type' => 'textarea', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_phone', 
                'value' => '+1-234-567-8900', 
                'group' => 'company', 
                'type' => 'text', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_email', 
                'value' => 'info@example.com', 
                'group' => 'company', 
                'type' => 'email', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Inventory settings
            [
                'key' => 'low_stock_threshold', 
                'value' => '5', 
                'group' => 'inventory', 
                'type' => 'number', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_low_stock_notification', 
                'value' => '1', 
                'group' => 'inventory', 
                'type' => 'boolean', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_purchase_enabled', 
                'value' => '0', 
                'group' => 'inventory', 
                'type' => 'boolean', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_purchase_threshold', 
                'value' => '3', 
                'group' => 'inventory', 
                'type' => 'number', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_purchase_quantity', 
                'value' => '10', 
                'group' => 'inventory', 
                'type' => 'number', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Homepage settings
            [
                'key' => 'homepage_featured_products_count', 
                'value' => '8', 
                'group' => 'homepage', 
                'type' => 'number', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'homepage_new_arrivals_count', 
                'value' => '8', 
                'group' => 'homepage', 
                'type' => 'number', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'homepage_on_sale_count', 
                'value' => '8', 
                'group' => 'homepage', 
                'type' => 'number', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_new_arrivals_days', 
                'value' => '30', 
                'group' => 'homepage', 
                'type' => 'number', 
                'is_public' => true,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_new_arrivals_enabled', 
                'value' => '1', 
                'group' => 'homepage', 
                'type' => 'boolean', 
                'is_public' => false,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        
        DB::table('settings')->insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_descriptions');
        Schema::dropIfExists('settings');
    }
}; 