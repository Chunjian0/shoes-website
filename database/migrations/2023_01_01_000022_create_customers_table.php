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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('ic_number')->nullable()->unique();
            $table->string('name');
            $table->date('birthday')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('customer_password')->nullable();
            $table->text('address')->nullable();
            $table->decimal('points', 10, 2)->default(0.00);
            $table->string('last_login_ip', 45)->nullable();
            $table->text('remarks')->nullable();
            $table->longText('tags')->nullable();
            $table->timestamp('last_visit_at')->nullable();
            $table->enum('member_level', ['normal', 'silver', 'gold', 'platinum'])->default('normal');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('store_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
}; 