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
        // Define permissions with categories
        $permissions = [
            // Dashboard
            ['name' => 'view dashboard', 'category' => 'Dashboard'],

            // Branches
            ['name' => 'view branches', 'category' => 'Branches'],
            ['name' => 'create branches', 'category' => 'Branches'],
            ['name' => 'edit branches', 'category' => 'Branches'],
            ['name' => 'delete branches', 'category' => 'Branches'],
            ['name' => 'manage branches', 'category' => 'Branches'],

            // Warehouses
            ['name' => 'view warehouses', 'category' => 'Warehouses'],
            ['name' => 'create warehouses', 'category' => 'Warehouses'],
            ['name' => 'edit warehouses', 'category' => 'Warehouses'],
            ['name' => 'delete warehouses', 'category' => 'Warehouses'],
            ['name' => 'manage warehouses', 'category' => 'Warehouses'],

            // Products
            ['name' => 'view products', 'category' => 'Products'],
            ['name' => 'create products', 'category' => 'Products'],
            ['name' => 'edit products', 'category' => 'Products'],
            ['name' => 'delete products', 'category' => 'Products'],
            ['name' => 'manage products', 'category' => 'Products'],



            // Manage Combos
            ['name' => 'view combos', 'category' => 'Manage Combos'],
            ['name' => 'manage combos', 'category' => 'Manage Combos'],

            // Stock Adjust
            ['name' => 'view stock', 'category' => 'Stock Adjust'],
            ['name' => 'delete stock', 'category' => 'Stock Adjust'],
            ['name' => 'adjust stock', 'category' => 'Stock Adjust'],
            ['name' => 'manage opening stock', 'category' => 'Stock Adjust'],

            // Purchase
            ['name' => 'view purchases', 'category' => 'Purchase'],
            ['name' => 'create purchases', 'category' => 'Purchase'],
            ['name' => 'edit purchases', 'category' => 'Purchase'],
            ['name' => 'delete purchases', 'category' => 'Purchase'],
            ['name' => 'manage purchases', 'category' => 'Purchase'],

            // Purchase Return
            ['name' => 'view purchase returns', 'category' => 'Purchase Return'],
            ['name' => 'create purchase returns', 'category' => 'Purchase Return'],
            ['name' => 'edit purchase returns', 'category' => 'Purchase Return'],
            ['name' => 'delete purchase returns', 'category' => 'Purchase Return'],
            ['name' => 'manage purchase returns', 'category' => 'Purchase Return'],

            // Suppliers
            ['name' => 'view suppliers', 'category' => 'Suppliers'],
            ['name' => 'create suppliers', 'category' => 'Suppliers'],
            ['name' => 'edit suppliers', 'category' => 'Suppliers'],
            ['name' => 'delete suppliers', 'category' => 'Suppliers'],
            ['name' => 'manage suppliers', 'category' => 'Suppliers'],

            // Supplier Pay
            ['name' => 'view payments', 'category' => 'Supplier Pay'],
            ['name' => 'create payments', 'category' => 'Supplier Pay'],
            ['name' => 'delete payments', 'category' => 'Supplier Pay'],
            ['name' => 'manage payments', 'category' => 'Supplier Pay'],

            // POS
            ['name' => 'use pos', 'category' => 'POS'],

            // Sales
            ['name' => 'view sales', 'category' => 'Sales'],
            ['name' => 'create sales', 'category' => 'Sales'],
            ['name' => 'edit sales', 'category' => 'Sales'],
            ['name' => 'delete sales', 'category' => 'Sales'],
            ['name' => 'view invoices', 'category' => 'Sales'],
            ['name' => 'manage invoices', 'category' => 'Sales'],
            ['name' => 'delete invoices', 'category' => 'Sales'],

            // Sale Return
            ['name' => 'view returns', 'category' => 'Sale Return'],
            ['name' => 'manage returns', 'category' => 'Sale Return'],
            ['name' => 'create sale returns', 'category' => 'Sale Return'],
            ['name' => 'edit sale returns', 'category' => 'Sale Return'],
            ['name' => 'delete sale returns', 'category' => 'Sale Return'],

            // Exchange
            ['name' => 'view exchanges', 'category' => 'Exchange'],
            ['name' => 'manage exchanges', 'category' => 'Exchange'],

            // Money Receipt
            ['name' => 'view money receipts', 'category' => 'Money Receipt'],
            ['name' => 'manage money receipts', 'category' => 'Money Receipt'],

            // Stock Transfer
            ['name' => 'view transfers', 'category' => 'Stock Transfer'],
            ['name' => 'create transfers', 'category' => 'Stock Transfer'],
            ['name' => 'approve transfers', 'category' => 'Stock Transfer'],
            ['name' => 'delete transfers', 'category' => 'Stock Transfer'],
            ['name' => 'manage transfers', 'category' => 'Stock Transfer'],
            ['name' => 'reconcile transfers', 'category' => 'Stock Transfer'],

            // Requisition
            ['name' => 'view requisitions', 'category' => 'Requisition'],
            ['name' => 'manage requisitions', 'category' => 'Requisition'],
            ['name' => 'process requisitions', 'category' => 'Requisition'],

            // Vouchers
            ['name' => 'view vouchers', 'category' => 'Vouchers'],
            ['name' => 'manage vouchers', 'category' => 'Vouchers'],

            // General Ledger
            ['name' => 'view ledger', 'category' => 'General Ledger'],

            // Financial Accounts
            ['name' => 'view accounts', 'category' => 'Financial Accounts'],
            ['name' => 'manage accounts', 'category' => 'Financial Accounts'],

            // Salary
            ['name' => 'view salary', 'category' => 'Salary'],
            ['name' => 'manage salary', 'category' => 'Salary'],

            // Sales Target
            ['name' => 'view sales targets', 'category' => 'Sales Target'],
            ['name' => 'manage sales targets', 'category' => 'Sales Target'],

            // Salary Increment
            ['name' => 'view salary increments', 'category' => 'Salary Increment'],
            ['name' => 'manage salary increments', 'category' => 'Salary Increment'],

            // Reports Center
            ['name' => 'view reports', 'category' => 'Reports Center'],
            ['name' => 'view financial reports', 'category' => 'Reports Center'],
            ['name' => 'customer summary', 'category' => 'Reports Center'],
            ['name' => 'customer ledger', 'category' => 'Reports Center'],
            ['name' => 'supplier summary', 'category' => 'Reports Center'],
            ['name' => 'supplier ledger', 'category' => 'Reports Center'],
            ['name' => 'profit and loss', 'category' => 'Reports Center'],
            ['name' => 'top selling products', 'category' => 'Reports Center'],
            ['name' => 'cash profit', 'category' => 'Reports Center'],
            ['name' => 'cash book', 'category' => 'Reports Center'],
            ['name' => 'bank book', 'category' => 'Reports Center'],
            ['name' => 'mobile book', 'category' => 'Reports Center'],
            ['name' => 'expense report', 'category' => 'Reports Center'],
            ['name' => 'view executive reports', 'category' => 'Reports Center'],
            ['name' => 'performance analysis', 'category' => 'Reports Center'],

            // Online Orders
            ['name' => 'view online orders', 'category' => 'Online Orders'],
            ['name' => 'manage online orders', 'category' => 'Online Orders'],

            // Order Returns
            ['name' => 'view online returns', 'category' => 'Order Returns'],
            ['name' => 'manage online returns', 'category' => 'Order Returns'],

            // Order Exchanges
            ['name' => 'view online exchanges', 'category' => 'Order Exchanges'],
            ['name' => 'manage online exchanges', 'category' => 'Order Exchanges'],

            // Customers
            ['name' => 'view customers', 'category' => 'Customers'],
            ['name' => 'manage customers', 'category' => 'Customers'],

            // Invoice List
            ['name' => 'view internal invoices', 'category' => 'Invoice List'],

            // Coupons
            ['name' => 'view coupons', 'category' => 'Coupons'],
            ['name' => 'manage coupons', 'category' => 'Coupons'],

            // Bulk Discounts
            ['name' => 'view bulk discounts', 'category' => 'Bulk Discounts'],
            ['name' => 'manage bulk discounts', 'category' => 'Bulk Discounts'],

            // Visual Stories
            ['name' => 'view vlogs', 'category' => 'Visual Stories'],
            ['name' => 'manage vlogs', 'category' => 'Visual Stories'],

            // Reviews
            ['name' => 'view reviews', 'category' => 'Reviews'],
            ['name' => 'manage reviews', 'category' => 'Reviews'],

            // Banners
            ['name' => 'view banners', 'category' => 'Banners'],
            ['name' => 'manage banners', 'category' => 'Banners'],

            // Employees
            ['name' => 'view employees', 'category' => 'Employees'],
            ['name' => 'manage employees', 'category' => 'Employees'],

            // Master Settings
            ['name' => 'view master settings', 'category' => 'Master Settings'],
            ['name' => 'product category', 'category' => 'Master Settings'],
            ['name' => 'product subcategory', 'category' => 'Master Settings'],
            ['name' => 'product brand', 'category' => 'Master Settings'],
            ['name' => 'product unit', 'category' => 'Master Settings'],
            ['name' => 'product season', 'category' => 'Master Settings'],
            ['name' => 'product gender', 'category' => 'Master Settings'],
            ['name' => 'product variation', 'category' => 'Master Settings'],
            ['name' => 'product attribute', 'category' => 'Master Settings'],


            // App Settings
            ['name' => 'view settings', 'category' => 'App Settings'],

            //custom page
            ['name' => 'view additional pages', 'category' => 'custom page'],
            ['name' => 'manage additional pages', 'category' => 'custom page'],



            // Shipping Methods
            ['name' => 'view shipping', 'category' => 'Shipping Methods'],
            ['name' => 'manage shipping', 'category' => 'Shipping Methods'],


            // User Roles
            ['name' => 'view users', 'category' => 'User Roles'],
            ['name' => 'create users', 'category' => 'User Roles'],
            ['name' => 'edit users', 'category' => 'User Roles'],
            ['name' => 'delete users', 'category' => 'User Roles'],
            ['name' => 'view roles', 'category' => 'User Roles'],
            ['name' => 'manage roles', 'category' => 'User Roles'],
        ];

        // Create/Update permissions
        $permissionNames = array_column($permissions, 'name');

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => 'web'
                ],
                [
                    'category' => $permission['category']
                ]
            );
        }

        // Clean up: Remove any permissions that are NOT in the seeder list
        $deletedCount = Permission::whereNotIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->delete();

        if ($deletedCount > 0) {
            echo "Removed $deletedCount obsolete permissions.\n";
        }

        echo "Permissions synchronized successfully!\n";

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