@extends('erp.master')

@section('title', 'Branch Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}"
                                    class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('branches.index') }}"
                                    class="text-decoration-none">Branches</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $branch->name }}</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">{{ $branch->name }}</h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>{{ $branch->location }}
                        @if($branch->contact_info)
                            <span class="ms-3"><i class="fas fa-phone me-2"></i>{{ $branch->contact_info }}</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group me-2">
                        @can('edit branch')
                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Edit Branch
                            </a>
                        @endcan
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="fas fa-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">

            {{-- Manager Details Card hidden for ecommerce-only setup --}}

            <!-- Statistics Cards -->
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-warehouse text-info fs-4"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold text-info mb-0">{{ $warehouses_count }}</h3>
                                    <p class="text-muted mb-0">Warehouses</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-box text-warning fs-4"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold text-warning mb-0">{{ $products_count }}</h3>
                                    <p class="text-muted mb-0">Products</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Enhanced Warehouse Info -->
            <div class="card mb-5 shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-warehouse text-primary me-2"></i>Branch Warehouses
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#addWarehouseModal">
                            <i class="fas fa-plus me-1"></i>Add Warehouse
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branch->warehouses as $warehouse)
                                    <tr>
                                        <td><a href="{{ route('warehouses.show', $warehouse->id) }}"
                                                class="text-decoration-none text-primary">{{ $warehouse->name }}</a></td>
                                        <td>{{ $warehouse->location }}</td>
                                        <td><span
                                                class="badge bg-{{ $warehouse->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($warehouse->status ?? 'active') }}</span>
                                        </td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editWarehouseModal{{ $warehouse->id }}" title="Edit Warehouse">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <!-- Delete Form -->
                                            <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST"
                                                style="display:inline-block"
                                                onsubmit="return confirm('Delete this warehouse?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Warehouse">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editWarehouseModal{{ $warehouse->id }}" tabindex="-1"
                                                aria-labelledby="editWarehouseModalLabel{{ $warehouse->id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="editWarehouseModalLabel{{ $warehouse->id }}">Edit Warehouse
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('warehouses.update', $warehouse->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="warehouse_name_{{ $warehouse->id }}"
                                                                        class="form-label">Warehouse Name</label>
                                                                    <input type="text" class="form-control"
                                                                        id="warehouse_name_{{ $warehouse->id }}" name="name"
                                                                        value="{{ $warehouse->name }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="warehouse_location_{{ $warehouse->id }}"
                                                                        class="form-label">Location</label>
                                                                    <input type="text" class="form-control"
                                                                        id="warehouse_location_{{ $warehouse->id }}"
                                                                        name="location" value="{{ $warehouse->location }}"
                                                                        required>
                                                                </div>
                                                                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Update
                                                                    Warehouse</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No warehouses found for this branch.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Branch Products Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-box text-primary me-2"></i>Branch Products
                        </h5>
                        <div class="d-flex gap-2">
                            <input type="search" class="form-control form-control-sm"
                                placeholder="Search products..." style="width: 200px;" id="productSearch">
                        </div>
                    </div>
                </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="productsTable">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="border-0">Product</th>
                                            <th class="border-0">SKU</th>
                                            <th class="border-0">Sale Price</th>
                                            <th class="border-0">Purchase Price</th>
                                            <th class="border-0">Category</th>
                                            <th class="border-0">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($branch_products as $product)
                                            <tr>
                                                <td class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                                            <i class="fas fa-box text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-semibold">{{ $product->product->name ?? 'N/A' }}
                                                            </h6>
                                                            <small class="text-muted">ID:
                                                                #{{ $product->product->id ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="border-0">
                                                    <code
                                                        class="bg-light px-2 py-1 rounded">{{ $product->product->sku ?? 'N/A' }}</code>
                                                </td>
                                                <td class="border-0">
                                                    <span
                                                        class="fw-semibold text-success">৳{{ number_format(($product->product->discount && $product->product->discount > 0) ? $product->product->discount : ($product->product->price ?? 0), 2) }}</span>
                                                </td>
                                                <td class="border-0">
                                                    <span
                                                        class="fw-semibold">৳{{ number_format($product->product->cost ?? 0, 2) }}</span>
                                                </td>
                                                <td class="border-0">
                                                    <span class="badge bg-info bg-opacity-25 text-info">
                                                        {{ $product->product->category->name ?? 'No Category' }}
                                                    </span>
                                                </td>
                                                <td class="border-0">
                                                    <span
                                                        class="badge {{ $product->quantity > 0 ? 'bg-success bg-opacity-25 text-success' : 'bg-danger bg-opacity-25 text-danger' }}">
                                                        {{ $product->quantity }} in stock
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-box text-muted mb-3"
                                                            style="font-size: 3rem; opacity: 0.3;"></i>
                                                        <h5>No products found</h5>
                                                        <p>This branch doesn't have any products assigned yet.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Warehouse Modal -->
    <div class="modal fade" id="addWarehouseModal" tabindex="-1" aria-labelledby="addWarehouseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWarehouseModalLabel">Add Warehouse to Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('branches.warehouses.store', $branch->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="warehouse_name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="warehouse_name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="warehouse_location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                   id="warehouse_location" name="location" value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="warehouse_manager_id" class="form-label">Manager (Optional)</label>
                            <select class="form-control @error('manager_id') is-invalid @enderror" 
                                    id="warehouse_manager_id" name="manager_id">
                                <option value="">-- Select Manager --</option>
                                @foreach(\App\Models\User::where('is_admin', 1)->get() as $user)
                                    <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>This warehouse will be linked to <strong>{{ $branch->name }}</strong> branch for POS operations.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection