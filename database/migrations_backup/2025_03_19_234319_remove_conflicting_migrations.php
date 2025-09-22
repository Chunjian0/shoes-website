<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 删除冲突的迁移记录
        DB::table('migrations')->where('migration', '2025_03_19_171227_add_homepage_fields_to_products_table')->delete();
        DB::table('migrations')->where('migration', '2025_03_19_171233_create_promotions_table')->delete();
        DB::table('migrations')->where('migration', '2025_03_19_231216_add_missing_columns_to_products_table')->delete();
        DB::table('migrations')->where('migration', '2025_03_19_233308_add_default_image_index_to_products_table')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 不需要恢复，因为这是解决迁移冲突的一次性操作
    }
};
