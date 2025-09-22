<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('auto_generated')->default(false)->after('received_at')->comment('是否由系统自动生成');
            $table->string('generated_by')->nullable()->after('auto_generated')->comment('生成者（系统或用户ID）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('auto_generated');
            $table->dropColumn('generated_by');
        });
    }
}; 