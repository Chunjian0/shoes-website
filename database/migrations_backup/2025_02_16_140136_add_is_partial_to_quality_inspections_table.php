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
        Schema::table('quality_inspections', function (Blueprint $table) {
            $table->boolean('is_partial')->default(false)->after('status')->comment('Is it a partial test');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_inspections', function (Blueprint $table) {
            $table->dropColumn('is_partial');
        });
    }
};
