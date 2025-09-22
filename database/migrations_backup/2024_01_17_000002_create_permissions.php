<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create a role
        $roles = ['admin', 'manager', 'staff'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create permissions
        $permissions = [
            // Product management permissions
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'manage_products',
            
            // Customer management permissions
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            'manage_customers',
            
            // Optometry data management permissions
            'view_prescriptions',
            'create_prescriptions',
            'edit_prescriptions',
            'delete_prescriptions',
            'manage_prescriptions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to the administrator role
        $adminRole = Role::findByName('admin');
        $adminRole->syncPermissions($permissions);

        // Assign partial permissions to the store manager role
        $managerRole = Role::findByName('manager');
        $managerRole->syncPermissions([
            'view_products',
            'create_products',
            'edit_products',
            'manage_products',
            'view_customers',
            'create_customers',
            'edit_customers',
            'manage_customers',
            'view_prescriptions',
            'create_prescriptions',
            'edit_prescriptions',
            'manage_prescriptions',
        ]);

        // Assign basic permissions to ordinary employees
        $staffRole = Role::findByName('staff');
        $staffRole->syncPermissions([
            'view_products',
            'view_customers',
            'create_customers',
            'view_prescriptions',
            'create_prescriptions',
        ]);
    }

    public function down(): void
    {
        // Delete all roles and permissions
        Role::query()->delete();
        Permission::query()->delete();
    }
}; 