<?php

declare(strict_types=1);

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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Set key name');
            $table->text('value')->nullable()->comment('Set value');
            $table->string('type')->default('string')->comment('Value Type');
            $table->string('group')->default('general')->comment('Set up grouping');
            $table->string('label')->comment('display name');
            $table->text('description')->nullable()->comment('describe');
            $table->json('options')->nullable()->comment('Optional value');
            $table->boolean('is_public')->default(false)->comment('Whether it is public');
            $table->timestamps();
        });

        // Add default settings
        DB::table('settings')->insert([
            [
                'key' => 'company_name',
                'value' => '',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Company Name',
                'description' => 'The official name of the company',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_address',
                'value' => '',
                'type' => 'text',
                'group' => 'company',
                'label' => 'Company Address',
                'description' => 'Company details',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Contact number',
                'description' => 'Company contact number',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_email',
                'value' => '',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Email',
                'description' => 'Company email address',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_tax_number',
                'value' => '',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Tax number',
                'description' => 'Company tax registration number',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'backup_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Enable automatic backup',
                'description' => 'Whether to enable the automatic system backup function',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'group' => 'system',
                'label' => 'Backup frequency',
                'description' => 'The frequency of automatic system backup',
                'options' => json_encode(['daily', 'weekly', 'monthly']),
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'invoice_prefix',
                'value' => '',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Invoice prefix',
                'description' => 'Prefix of invoice number',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'invoice_footer',
                'value' => '',
                'type' => 'text',
                'group' => 'company',
                'label' => 'Invoice footer',
                'description' => 'Text displayed at the bottom of the invoice',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'timezone',
                'value' => config('app.timezone'),
                'type' => 'string',
                'group' => 'system',
                'label' => 'System time zone',
                'description' => 'The time zone used by the system',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'system',
                'label' => 'Date format',
                'description' => 'The format of the system display date',
                'options' => json_encode(['Y-m-d', 'd/m/Y', 'm/d/Y']),
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'admin_notification_email',
                'value' => '',
                'type' => 'string',
                'group' => 'system',
                'label' => 'Administrator notification email',
                'description' => 'The administrator\'s email address for receiving important system notifications' ,
                'options' => null,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Enable email notifications',
                'description' => 'Whether to enable email notification function',
                'options' => null,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notification_recipients',
                'value' => '[]',
                'type' => 'json',
                'group' => 'notifications',
                'label' => 'Notify the recipient',
                'description' => 'List of employees receiving system notifications',
                'options' => null,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
