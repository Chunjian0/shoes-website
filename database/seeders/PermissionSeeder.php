<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset roles and permission caches
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Customer management permissions
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Optometry management permissions
            'view prescriptions',
            'create prescriptions',
            'edit prescriptions',
            'delete prescriptions',
            
            // Product management permissions
            'view products',
            'create products',
            'edit products',
            'delete products',
            
            // Sales Management Permissions
            'view sales',
            'create sales',
            'edit sales',
            'delete sales',

            // Cart Management Permissions
            'view carts',
            'manage carts',
            'view customer carts',
            'edit customer carts',
            
            // Order Management Permissions
            'view orders',
            'create orders',
            'edit orders',
            'cancel orders',
            'update order status',
            'update payment status',
            'export order pdf',
            
            // Invoice Management Permissions
            'view invoices',
            'create invoices',
            'edit invoices',
            'submit invoices',
            'download invoice pdf',
            
            // Return Management Permissions
            'view returns',
            'create returns',
            'process returns',

            // Inventory management permissions
            'manage inventory',
            'view inventory',
            'create inventory check',
            'complete inventory check',
            'create inventory loss',
            'approve inventory loss',
            'view inventory alerts',

            // Supplier management permissions
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'manage supplier products',
            'manage price agreements',

            // Warehouse management permissions
            'view warehouses',
            'create warehouses',
            'edit warehouses',
            'delete warehouses',

            // Purchase order management permissions
            'view purchases',
            'create purchases',
            'edit purchases',
            'delete purchases',
            'approve purchases',
            'reject purchases',
            'confirm purchases',
            'cancel purchases',

            // Purchase and return management permissions
            'view purchase returns',
            'create purchase returns',
            'edit purchase returns',
            'delete purchase returns',
            'approve purchase returns',
            'reject purchase returns',
            'complete purchase returns',

            // Purchase refund management permissions
            'view purchase refunds',
            'create purchase refunds',
            'delete purchase refunds',

            // Quality inspection management permissions
            'view quality inspections',
            'create quality inspections',
            'edit quality inspections',
            'delete quality inspections',
            'approve quality inspections',
            'reject quality inspections',
            'receive quality inspection notifications',

            // Company Setting Permissions
            'manage company settings',
            'view company settings',

            // System Setting Permissions
            'manage system settings',
            'view system settings',

            // Notification Setting Permissions
            'manage notification settings',
            'view notification settings',
            'view notification history',
            'manage notification templates',

            // Employee management permissions
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',

            // Coupon management permissions
            'manage coupons',
            'view coupons',
            'create coupons',
            'edit coupons',
            'delete coupons',
            'issue coupons',

            // Homepage management permissions
            'manage homepage',
            'view homepage',
            'edit homepage',
            
            // Analytics management permissions
            'view analytics',
            'manage analytics',
            'view product analytics',
            'view ab tests',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'super-admin' => $permissions,
            'admin' => [
                'view users',
                'view employees',
                'view customers', 'create customers', 'edit customers',
                'view prescriptions', 'create prescriptions', 'edit prescriptions',
                'view products',
                'view sales', 'create sales',
                'view carts', 'manage carts',
                'view orders', 'update order status', 'update payment status',
                'view invoices',
                'view returns', 'process returns',
                'view inventory',
                'view inventory alerts',
                'view suppliers',
                'view warehouses',
                'view purchases',
                'view quality inspections',
                'view company settings',
                'view notification settings',
                'view notification history',
                'view coupons',
                'view homepage',
                'view analytics',
                'view product analytics',
                'view ab tests',
            ],
            'manager' => [
                'view users',
                'view employees',
                'create employees',
                'edit employees',
                'delete employees',
                'view customers', 'create customers', 'edit customers',
                'view prescriptions', 'create prescriptions', 'edit prescriptions',
                'view products', 'create products', 'edit products',
                'view sales', 'create sales', 'edit sales',
                'view carts', 'manage carts', 'view customer carts', 'edit customer carts',
                'view orders', 'create orders', 'edit orders', 'cancel orders', 'update order status', 'update payment status', 'export order pdf',
                'view invoices', 'create invoices', 'edit invoices', 'submit invoices', 'download invoice pdf',
                'view returns', 'create returns', 'process returns',
                'manage inventory',
                'view inventory',
                'create inventory check',
                'complete inventory check',
                'create inventory loss',
                'approve inventory loss',
                'view inventory alerts',
                'view suppliers',
                'create suppliers',
                'edit suppliers',
                'delete suppliers',
                'manage supplier products',
                'manage price agreements',
                'view warehouses',
                'create warehouses',
                'edit warehouses',
                'delete warehouses',
                'view purchases',
                'create purchases',
                'edit purchases',
                'approve purchases',
                'reject purchases',
                'confirm purchases',
                'cancel purchases',
                'view purchase returns',
                'view purchase refunds',
                'view quality inspections',
                'create quality inspections',
                'edit quality inspections',
                'approve quality inspections',
                'reject quality inspections',
                'manage company settings',
                'view company settings',
                'manage coupons',
                'view coupons',
                'create coupons',
                'edit coupons',
                'delete coupons',
                'issue coupons',
                'manage homepage',
                'view homepage',
                'edit homepage',
                'view analytics',
                'view product analytics',
            ],
            'optometrist' => [
                'view customers', 'create customers', 'edit customers',
                'view prescriptions', 'create prescriptions', 'edit prescriptions',
                'view sales', 'create sales',
                'view products',
                'view carts', 'view customer carts',
                'view orders',
            ],
            'sales' => [
                'view customers', 'create customers',
                'view prescriptions',
                'view products',
                'view sales', 'create sales',
                'view carts', 'manage carts', 'view customer carts', 'edit customer carts',
                'view orders', 'create orders', 'export order pdf',
                'view invoices', 'create invoices',
                'view returns', 'create returns',
                'view inventory',
                'view suppliers',
            ],
            'cashier' => [
                'view customers',
                'view products',
                'view inventory',
                'view suppliers',
                'view carts', 'manage carts',
                'view orders', 'update payment status',
                'view invoices',
            ],
            'stockkeeper' => [
                'view products',
                'manage inventory',
                'view inventory',
                'create inventory check',
                'complete inventory check',
                'create inventory loss',
                'view inventory alerts',
                'view suppliers',
                'create suppliers',
                'edit suppliers',
                'manage supplier products',
                'manage price agreements',
                'view warehouses',
                'create warehouses',
                'edit warehouses',
                'view purchases',
                'view quality inspections',
                'create quality inspections',
                'edit quality inspections',
            ],
        ];

        foreach ($roles as $role => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $role]);
            $role->syncPermissions($rolePermissions);
        }

        // System Setting Permissions
        Permission::firstOrCreate(['name' => 'manage system settings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view system settings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage notification settings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view notification settings', 'guard_name' => 'web']);

        // Coupon management permissions
        Permission::firstOrCreate(['name' => 'manage coupons', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view coupons', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create coupons', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit coupons', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete coupons', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'issue coupons', 'guard_name' => 'web']);

        // Homepage management permissions
        Permission::firstOrCreate(['name' => 'manage homepage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view homepage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit homepage', 'guard_name' => 'web']);

        // Assign coupon permissions to administrator roles
        $adminRole = Role::where('name', 'super-admin')->first();
        $adminRole->givePermissionTo([
            'manage coupons',
            'view coupons',
            'create coupons',
            'edit coupons',
            'delete coupons',
            'issue coupons',
            'manage homepage',
            'view homepage',
            'edit homepage',
            'view analytics',
            'manage analytics',
            'view product analytics',
            'view ab tests',
        ]);

        // Assign some coupon permissions to the store manager role
        $managerRole = Role::where('name', 'manager')->first();
        $managerRole->givePermissionTo([
            'view coupons',
            'create coupons',
            'edit coupons',
            'issue coupons',
            'manage homepage',
            'view homepage',
            'edit homepage',
            'view analytics',
            'view product analytics',
        ]);
    }
} 