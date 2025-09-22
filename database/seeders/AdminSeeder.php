<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'ethankhoo09@gmail.com',
            'password' => Hash::make('106428'),
            'employee_id' => 'EMP001',
            'is_active' => true,
        ]);

        $admin->assignRole('super-admin');
    }
} 