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
     * - 2024_01_17_000003_create_customers_table.php
     * - 2025_03_19_000934_add_password_to_customers_table.php
     * - 2025_03_19_100039_add_email_verified_at_to_customers_table.php
     * - 2025_03_19_124621_create_verification_codes_table.php
     * - 2025_03_19_132115_add_fields_to_verification_codes_table.php
     * - 2025_03_24_000003_add_last_visit_at_to_customers_table.php
     * - 2025_03_24_000004_add_member_level_to_customers_table.php
     */
    public function up(): void
    {
        // Create customers table with all fields from all migrations
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone', 20)->nullable();
                $table->string('address')->nullable();
                $table->string('city', 50)->nullable();
                $table->string('state', 50)->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->string('country', 50)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
                $table->string('password')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('last_visit_at')->nullable(); // From 2025_03_24_000003
                $table->enum('member_level', ['normal', 'silver', 'gold', 'platinum'])->default('normal'); // From 2025_03_24_000004
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create verification_codes table
        if (!Schema::hasTable('verification_codes')) {
            Schema::create('verification_codes', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('code');
                $table->string('type')->default('email');
                $table->boolean('used')->default(false);
                $table->timestamp('expires_at');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->timestamps();
                
                $table->foreign('customer_id')
                      ->references('id')
                      ->on('customers')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
        Schema::dropIfExists('customers');
    }
}; 