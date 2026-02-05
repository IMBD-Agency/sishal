# Branch Isolation Implementation Guide

## Overview
This system ensures that employees can only see and manage data from their assigned branch, while admins have access to all branches.

---

## How It Works

### 1. **Employee Assignment**
When you create or edit an employee:
- Assign them to a specific `branch_id`
- This automatically restricts their access to that branch only

### 2. **Access Control Levels**

| User Type | Access Level |
|-----------|-------------|
| **Super Admin** | All branches, all data |
| **Admin** (`is_admin = 1`) | All branches, all data |
| **Employee with branch** | Only their assigned branch |
| **Employee without branch** | Restricted (redirected to dashboard) |
| **Customer** | Not affected by branch isolation |

---

## Implementation in Controllers

### Method 1: Using the BranchScoped Trait (Recommended)

```php
<?php

namespace App\Http\Controllers\Erp;

use App\Traits\BranchScoped;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use BranchScoped;

    public function index()
    {
        // Automatically filters by user's branch (if restricted)
        $products = $this->scopeToBranch(Product::query())->paginate(20);
        
        return view('erp.products.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if user can access this product's branch
        if (!$this->canAccessBranch($product->branch_id)) {
            abort(403, 'You do not have access to this branch.');
        }
        
        return view('erp.products.show', compact('product'));
    }

    public function create()
    {
        // Get only branches the user can access
        $branches = $this->getAccessibleBranches();
        
        return view('erp.products.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'branch_id' => 'required|exists:branches,id'
        ]);

        // Verify user can access this branch
        if (!$this->canAccessBranch($validated['branch_id'])) {
            return back()->withErrors(['branch_id' => 'You cannot create items for this branch.']);
        }

        Product::create($validated);
        
        return redirect()->route('products.index');
    }
}
```

### Method 2: Using Middleware (Route-Level Protection)

In `app/Http/Kernel.php`, register the middleware:

```php
protected $middlewareAliases = [
    // ... existing middleware
    'branch.isolation' => \App\Http\Middleware\BranchIsolation::class,
];
```

Then in your routes (`routes/web.php`):

```php
// Apply to specific routes
Route::middleware(['auth', 'branch.isolation'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('sales', SaleController::class);
    // ... other branch-specific routes
});
```

---

## Common Patterns

### Pattern 1: Filter Index Pages by Branch

```php
public function index()
{
    $query = Order::with('customer', 'branch');
    
    // Apply branch filter
    $orders = $this->scopeToBranch($query)->latest()->paginate(20);
    
    return view('erp.orders.index', compact('orders'));
}
```

### Pattern 2: Auto-Assign Branch on Create

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
    
    // If user is branch-restricted, auto-assign their branch
    if ($this->isBranchRestricted()) {
        $validated['branch_id'] = $this->getUserBranchId();
    }
    
    Sale::create($validated);
}
```

### Pattern 3: Restrict Editing to Own Branch

```php
public function update(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Prevent editing orders from other branches
    if (!$this->canAccessBranch($order->branch_id)) {
        abort(403, 'You cannot edit orders from other branches.');
    }
    
    $order->update($request->validated());
}
```

### Pattern 4: Branch Dropdown in Forms

```php
public function create()
{
    // Admins see all branches, employees see only theirs
    $branches = $this->getAccessibleBranches();
    
    return view('erp.sales.create', compact('branches'));
}
```

---

## Models to Apply Branch Isolation

Apply the `BranchScoped` trait to controllers managing these models:

- ✅ **Products** (branch_id)
- ✅ **Sales/Orders** (branch_id)
- ✅ **Employees** (branch_id)
- ✅ **Stock Transfers** (from_branch_id, to_branch_id)
- ✅ **POS Transactions** (via pos.branch_id)
- ✅ **Invoices** (branch_id)
- ✅ **Expenses** (branch_id)

---

## Testing Branch Isolation

### Test Case 1: Employee Login
1. Create an employee and assign to Branch A
2. Login as that employee
3. Verify they can only see Branch A data

### Test Case 2: Admin Login
1. Login as admin
2. Verify you can see all branches
3. Verify you can switch between branches

### Test Case 3: Unauthorized Access
1. Login as Branch A employee
2. Try to access Branch B data directly (via URL)
3. Should get 403 error or redirect

---

## Helper Methods Reference

### In Controllers (using BranchScoped trait):

```php
// Get current user's branch ID (null for admins)
$branchId = $this->getUserBranchId();

// Filter query by user's branch
$query = $this->scopeToBranch(Product::query());

// Check if user can access a specific branch
if ($this->canAccessBranch($branchId)) { ... }

// Check if user is restricted to a branch
if ($this->isBranchRestricted()) { ... }

// Get branches user can access
$branches = $this->getAccessibleBranches();
```

### In Views/Blade:

```blade
@if(Auth::user()->isBranchRestricted())
    <p>You are viewing data for: {{ Auth::user()->getBranch()->name }}</p>
@else
    <p>Viewing all branches (Admin)</p>
@endif

@if(Auth::user()->canAccessBranch($branch->id))
    <a href="{{ route('branch.show', $branch) }}">View Branch</a>
@endif
```

---

## Important Notes

1. **Warehouse vs Branch**: Warehouses are hubs that contain branches. Branch isolation applies to branches, not warehouses.

2. **Ecommerce Orders**: These typically don't have a branch_id initially. Assign them to a branch when they're fulfilled or picked up.

3. **Performance**: The `scopeToBranch()` method adds a WHERE clause, which is indexed and fast.

4. **Bypass for Specific Cases**: Some reports or dashboards might need to show aggregated data across branches for managers. Use `canAccessBranch()` checks for these.

---

## Next Steps

1. Register the middleware in `Kernel.php`
2. Apply the `BranchScoped` trait to relevant controllers
3. Update existing controllers to use `scopeToBranch()` in index methods
4. Test with employee and admin accounts
5. Update forms to show only accessible branches in dropdowns
