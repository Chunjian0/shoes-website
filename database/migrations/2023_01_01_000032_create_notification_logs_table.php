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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('recipient');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('message_template_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('recipient');
            
            $table->foreign('message_template_id')
                ->references('id')
                ->on('message_templates')
                ->onDelete('set null');
                
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
}; 