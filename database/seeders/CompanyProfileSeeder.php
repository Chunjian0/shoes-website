<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        try {
            Log::info('Start creating default company information');

            CompanyProfile::create([
                'name' => 'Your Company Name',
                'address' => 'Your Company Address',
                'phone' => 'Your Company Phone',
                'email' => 'your@company.com',
                'registration_number' => 'REG123456',
                'tax_number' => 'TAX123456',
                'website' => 'https://www.yourcompany.com',
                'bank_name' => 'Your Bank',
                'bank_account' => '1234567890',
                'bank_holder' => 'Your Company Name',
            ]);

            Log::info('Default company information is successfully created');
        } catch (\Exception $e) {
            Log::error('Failed to create default company information', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 