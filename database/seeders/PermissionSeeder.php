<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions with categories
        $permissions = [
            // User & Role Management
            ['name' => 'view users', 'category' => 'User Management'],
            ['name' => 'create users', 'category' => 'User Management'],
            ['name' => 'edit users', 'category' => 'User Management'],
            ['name' => 'delete users', 'category' => 'User Management'],
            ['name' => 'view roles', 'category' => 'User Management'],
            ['name' => 'manage roles', 'category' => 'User Management'],
            
            // Branch & Warehouse
            ['name' => 'view branches', 'category' => 'Branch Management'],
            ['name' => 'create branches', 'category' => 'Branch Management'],
            ['name' => 'edit branches', 'category' => 'Branch Management'],
            ['name' => 'delete branches', 'category' => 'Branch Management'],
            ['name' => 'manage branches', 'category' => 'Branch Management'],
            ['name' => 'view warehouses', 'category' => 'Warehouse Management'],
            ['name' => 'manage warehouses', 'category' => 'Warehouse Management'],
            
            // Product Management
            ['name' => 'view products', 'category' => 'Product Management'],
            ['name' => 'create products', 'category' => 'Product Management'],
            ['name' => 'edit products', 'category' => 'Product Management'],
            ['name' => 'delete products', 'category' => 'Product Management'],
            ['name' => 'manage products', 'category' => 'Product Management'],
            ['name' => 'manage categories', 'category' => 'Product Management'],
            ['name' => 'manage brands', 'category' => 'Product Management'],
            ['name' => 'manage units', 'category' => 'Product Management'],
            ['name' => 'manage seasons', 'category' => 'Product Management'],
            ['name' => 'manage genders', 'category' => 'Product Management'],
            ['name' => 'manage attributes', 'category' => 'Product Management'],
            
            // Inventory & Stock
            ['name' => 'view stock', 'category' => 'Inventory Management'],
            ['name' => 'adjust stock', 'category' => 'Inventory Management'],
            ['name' => 'view transfers', 'category' => 'Inventory Management'],
            ['name' => 'manage transfers', 'category' => 'Inventory Management'],
            
            // Purchases
            ['name' => 'view purchases', 'category' => 'Purchase Management'],
            ['name' => 'create purchases', 'category' => 'Purchase Management'],
            ['name' => 'edit purchases', 'category' => 'Purchase Management'],
            ['name' => 'delete purchases', 'category' => 'Purchase Management'],
            ['name' => 'view suppliers', 'category' => 'Purchase Management'],
            ['name' => 'manage suppliers', 'category' => 'Purchase Management'],
            ['name' => 'view payments', 'category' => 'Purchase Management'],
            ['name' => 'manage payments', 'category' => 'Purchase Management'],
            
            // Sales & POS
            ['name' => 'use pos', 'category' => 'Sales Management'],
            ['name' => 'view sales', 'category' => 'Sales Management'],
            ['name' => 'manage sales', 'category' => 'Sales Management'],
            ['name' => 'view invoices', 'category' => 'Sales Management'],
            ['name' => 'manage invoices', 'category' => 'Sales Management'],
            ['name' => 'view returns', 'category' => 'Sales Management'],
            ['name' => 'manage returns', 'category' => 'Sales Management'],
            ['name' => 'view exchanges', 'category' => 'Sales Management'],
            ['name' => 'manage exchanges', 'category' => 'Sales Management'],
            ['name' => 'view money receipts', 'category' => 'Sales Management'],
            ['name' => 'manage money receipts', 'category' => 'Sales Management'],

            // Ecommerce
            ['name' => 'view online orders', 'category' => 'Ecommerce'],
            ['name' => 'manage online orders', 'category' => 'Ecommerce'],
            ['name' => 'view customers', 'category' => 'Ecommerce'],
            ['name' => 'manage customers', 'category' => 'Ecommerce'],
            ['name' => 'view internal invoices', 'category' => 'Ecommerce'],
            
            // Accounting
            ['name' => 'view accounts', 'category' => 'Accounting'],
            ['name' => 'manage accounts', 'category' => 'Accounting'],
            ['name' => 'view vouchers', 'category' => 'Accounting'],
            ['name' => 'manage vouchers', 'category' => 'Accounting'],
            ['name' => 'view ledger', 'category' => 'Accounting'],
            ['name' => 'view salary', 'category' => 'Accounting'],
            ['name' => 'manage salary', 'category' => 'Accounting'],
            
            // Marketing
            ['name' => 'view coupons', 'category' => 'Marketing'],
            ['name' => 'manage coupons', 'category' => 'Marketing'],
            ['name' => 'view bulk discounts', 'category' => 'Marketing'],
            ['name' => 'manage bulk discounts', 'category' => 'Marketing'],
            ['name' => 'view banners', 'category' => 'Marketing'],
            ['name' => 'manage banners', 'category' => 'Marketing'],
            ['name' => 'view vlogs', 'category' => 'Marketing'],
            ['name' => 'manage vlogs', 'category' => 'Marketing'],
            ['name' => 'view reviews', 'category' => 'Marketing'],
            ['name' => 'manage reviews', 'category' => 'Marketing'],
            
            // Reports
            ['name' => 'view reports', 'category' => 'Reports'],
            ['name' => 'view financial reports', 'category' => 'Reports'],
            ['name' => 'view executive reports', 'category' => 'Reports'],
            
            // Setup & Settings
            ['name' => 'view employees', 'category' => 'Setup'],
            ['name' => 'manage employees', 'category' => 'Setup'],
            ['name' => 'manage settings', 'category' => 'Setup'],
            ['name' => 'view shipping', 'category' => 'Setup'],
            ['name' => 'manage shipping', 'category' => 'Setup'],
            ['name' => 'view additional pages', 'category' => 'Setup'],
            ['name' => 'manage additional pages', 'category' => 'Setup'],
            ['name' => 'manage pages', 'category' => 'Setup'],

            // Customer Support
            ['name' => 'view services', 'category' => 'Customer Support'],
            ['name' => 'manage services', 'category' => 'Customer Support'],

            // Dashboard
            ['name' => 'view dashboard', 'category' => 'Dashboard'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => 'web'
                ],
                [
                    'category' => $permission['category']
                ]
            );
        }

        echo "Permissions created successfully!\n";

        // Assign all permissions to Super Admin role
        $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $superAdmin->syncPermissions($allPermissions);

        // Ensure the super admin user has the role
        // You mentioned User ID 18 is your Super Admin on live
        $adminUser = \App\Models\User::find(18) ?? \App\Models\User::first();
        if ($adminUser) {
            $adminUser->assignRole($superAdmin);
        }
        
        echo "All permissions assigned to Super Admin role and assigned to User (" . ($adminUser->email ?? 'None') . ")!\n";
    }
} 