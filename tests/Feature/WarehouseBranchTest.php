<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Product;
use App\Models\BranchProductStock;
use App\Models\WarehouseProductStock;
use App\Models\StockTransfer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;

class WarehouseBranchTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create or find an admin user with unique email
        $this->adminUser = User::firstOrCreate([
            'email' => 'admin_test_unique@test.com',
        ], [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);
        
        $this->adminUser->is_admin = 1;
        $this->adminUser->save();

        // Create the required permissions
        Permission::firstOrCreate(['name' => 'manage branches', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage transfers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage purchases', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create purchases', 'guard_name' => 'web']);

        // Assign permission to admin user (check first to avoid duplicates)
        if (!$this->adminUser->hasPermissionTo('manage branches')) {
            $this->adminUser->givePermissionTo('manage branches');
        }
        if (!$this->adminUser->hasPermissionTo('manage transfers')) {
            $this->adminUser->givePermissionTo('manage transfers');
        }
        if (!$this->adminUser->hasPermissionTo('manage purchases')) {
            $this->adminUser->givePermissionTo('manage purchases');
        }
        if (!$this->adminUser->hasPermissionTo('create purchases')) {
            $this->adminUser->givePermissionTo('create purchases');
        }
    }

    public function test_can_create_branch_marked_as_warehouse()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('branches.store'), [
                'name' => 'Warehouse Branch Alpha',
                'location' => 'Main Industrial Area',
                'contact_info' => '01700000001',
                'is_warehouse' => '1',
            ]);

        $response->assertRedirect(route('branches.index'));
        
        $branch = Branch::where('name', 'Warehouse Branch Alpha')->first();
        $this->assertNotNull($branch);
        $this->assertTrue((bool)$branch->is_warehouse);
    }

    public function test_can_update_branch_warehouse_status()
    {
        $branch = Branch::create([
            'name' => 'Standard Branch Beta',
            'location' => 'Standard Area',
            'contact_info' => '01700000002',
            'is_warehouse' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('branches.update', $branch->id), [
                'name' => 'Updated Branch Beta',
                'location' => 'Standard Area',
                'contact_info' => '01700000002',
                'is_warehouse' => '1',
            ]);

        $response->assertRedirect(route('branches.index'));
        
        $branch->refresh();
        $this->assertEquals('Updated Branch Beta', $branch->name);
        $this->assertTrue((bool)$branch->is_warehouse);
    }

    public function test_stock_transfer_validation_rules()
    {
        $suffix = uniqid();

        // 1. Create Outlets
        $fromWarehouseBranch = Branch::create([
            'name' => 'Warehouse Branch ' . $suffix,
            'location' => 'Zone A',
            'contact_info' => '01700000003',
            'is_warehouse' => true,
        ]);

        $fromStandardBranch = Branch::create([
            'name' => 'Standard Branch ' . $suffix,
            'location' => 'Zone B',
            'contact_info' => '01700000004',
            'is_warehouse' => false,
        ]);

        $toBranch = Branch::create([
            'name' => 'Destination Branch ' . $suffix,
            'location' => 'Zone C',
            'contact_info' => '01700000005',
            'is_warehouse' => false,
        ]);

        // 2. Create standard product
        $product = Product::create([
            'name' => 'Test Product ' . $suffix,
            'slug' => 'test-product-' . $suffix,
            'type' => 'product',
            'sku' => 'TP-' . $suffix,
            'price' => 100.00,
            'cost' => 80.00,
            'status' => 'active',
            'show_in_ecommerce' => true,
            'manage_stock' => true,
        ]);

        // Seed stock for fromWarehouseBranch (which is a Branch type, so stored in BranchProductStock)
        BranchProductStock::create([
            'branch_id' => $fromWarehouseBranch->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'updated_by' => $this->adminUser->id,
        ]);

        // Seed stock for fromStandardBranch
        BranchProductStock::create([
            'branch_id' => $fromStandardBranch->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'updated_by' => $this->adminUser->id,
        ]);

        // 3. Test Transfer from Standard Branch -> fails validation
        $response1 = $this->actingAs($this->adminUser)
            ->from(route('stocktransfer.create'))
            ->post(route('stocktransfer.store'), [
                'transfer_date' => '2026-06-21',
                'from_outlet' => "branch_{$fromStandardBranch->id}",
                'to_outlet' => "branch_{$toBranch->id}",
                'is_direct' => '1',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 10,
                        'unit_price' => 80.00,
                    ]
                ],
            ]);

        $response1->assertRedirect(route('stocktransfer.create'));
        $response1->assertSessionHas('error', 'Transfers are only allowed from Warehouse (or Warehouse Branch) to Branch.');

        // 4. Test Transfer from Warehouse Branch -> passes validation
        $response2 = $this->actingAs($this->adminUser)
            ->post(route('stocktransfer.store'), [
                'transfer_date' => '2026-06-21',
                'from_outlet' => "branch_{$fromWarehouseBranch->id}",
                'to_outlet' => "branch_{$toBranch->id}",
                'is_direct' => '1',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 10,
                        'unit_price' => 80.00,
                    ]
                ],
            ]);

        $response2->assertSessionHasNoErrors();
        $response2->assertSessionDoesntHaveErrors(['error']);

        // Check stock updates
        $this->assertEquals(40, BranchProductStock::where('branch_id', $fromWarehouseBranch->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertEquals(10, BranchProductStock::where('branch_id', $toBranch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_purchase_can_be_received_at_warehouse_branch()
    {
        $suffix = uniqid();

        // 1. Create a Warehouse Branch
        $warehouseBranch = Branch::create([
            'name' => 'Purchase Wh Branch ' . $suffix,
            'location' => 'Procurement Zone',
            'contact_info' => '01700000099',
            'is_warehouse' => true,
        ]);

        // 2. Create standard product
        $product = Product::create([
            'name' => 'Purchase Test Product ' . $suffix,
            'slug' => 'pur-test-product-' . $suffix,
            'type' => 'product',
            'sku' => 'PUR-TP-' . $suffix,
            'price' => 120.00,
            'cost' => 90.00,
            'status' => 'active',
            'show_in_ecommerce' => true,
            'manage_stock' => true,
        ]);

        // 3. Post a new purchase targeting the Warehouse Branch
        $response = $this->actingAs($this->adminUser)
            ->post(route('purchase.store'), [
                'purchase_date' => '2026-06-21',
                'ship_location_type' => 'branch',
                'location_id' => $warehouseBranch->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 100,
                        'unit_price' => 90.00,
                    ]
                ],
                'paid_amount' => 0,
                'discount_value' => 0,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('purchase.list'));
    }
}
