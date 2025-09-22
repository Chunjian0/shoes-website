<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Consolidated from the following migrations:
     * - 2024_02_09_000001_create_media_table.php
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('temp_id')->nullable()->index();
            $table->string('model_type')->index();
            $table->unsignedBigInteger('model_id')->nullable()->index();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->string('disk');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->json('custom_properties')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index(['temp_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};