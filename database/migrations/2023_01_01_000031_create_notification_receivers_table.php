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
        Schema::create('notification_receivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_setting_id');
            $table->string('email');
            $table->timestamps();
            
            $table->unique(['notification_setting_id', 'email']);
            
            $table->foreign('notification_setting_id')
                ->references('id')
                ->on('notification_settings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_receivers');
    }
}; 