<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('name')->comment('Parameter name');
            $table->string('code')->comment('Parameter code');
            $table->string('type')->comment('Parameter type:text/number/select/radio/checkbox');
            $table->boolean('is_required')->default(false)->comment('Is it required');
            $table->json('options')->nullable()->comment('Options (forselect/radio/checkboxtype)');
            $table->string('validation_rules')->nullable()->comment('Verification rules');
            $table->boolean('is_searchable')->default(false)->comment('Is it searchable');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0)->comment('Sort');
            $table->text('description')->nullable()->comment('Parameter description');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_parameters');
    }
}; 