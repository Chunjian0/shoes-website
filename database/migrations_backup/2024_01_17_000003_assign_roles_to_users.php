<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Assign administrator roles to all existing users
        $adminRole = Role::where('name', 'admin')->first();
        User::query()->each(function ($user) use ($adminRole) {
            $user->assignRole($adminRole);
        });
    }

    public function down(): void
    {
        // Remove all user roles
        User::query()->each(function ($user) {
            $user->roles()->detach();
        });
    }
}; 