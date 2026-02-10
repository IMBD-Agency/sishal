@extends('erp.master')

@section('title', 'Warehouse Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid">
            <!-- Header Section -->
            <div class="row my-4">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-white shadow-sm btn-sm me-3 border-0 rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-arrow-left text-muted small"></i>
                            </a>
                            <h4 class="mb-0 fw-bold text-dark">{{ $warehouse->name }}</h4>
                            <span class="badge bg-{{ $warehouse->status == 'active' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $warehouse->status == 'active' ? 'success' : 'danger' }} border border-{{ $warehouse->status == 'active' ? 'success' : 'danger' }} border-opacity-25 rounded-pill px-3 ms-3 small">
                                {{ ucfirst($warehouse->status ?? 'active') }}
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-white btn-sm shadow-sm border-0 rounded-3 text-dark transition-2" data-bs-toggle="modal" data-bs-target="#editWarehouseModal">
                                <i class="fas fa-edit me-1 text-warning"></i> Edit
                            </button>
                        </div>
                    </div>
                    <p class="text-muted small ms-5 ps-1 mb-0"><i class="fas fa-map-marker-alt me-1"></i> {{ $warehouse->location }}</p>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-info bg-opacity-10 p-3 rounded-4">
                                    <i class="fas fa-code-branch text-info fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <h3 class="mb-0 fw-bold text-dark">{{ $warehouse->branches->count() }}</h3>
                                    <p class="text-muted small mb-0">Total Branches</p>
                                </div>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info rounded-pill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-success bg-opacity-10 p-3 rounded-4">
                                    <i class="fas fa-user-tie text-success fs-4"></i>
                                </div>
                                <div class="text-end overflow-hidden">
                                    @if($warehouse->manager && $warehouse->manager->user)
                                        <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $warehouse->manager->user->first_name }} {{ $warehouse->manager->user->last_name }}</h6>
                                        <p class="text-muted small mb-0 text-truncate">{{ $warehouse->manager->position ?? 'Manager' }}</p>
                                    @else
                                        <h6 class="mb-0 fw-bold text-muted">Unassigned</h6>
                                        <p class="text-muted small mb-0">Manager</p>
                                    @endif
                                </div>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success rounded-pill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-warning bg-opacity-10 p-3 rounded-4">
                                    <i class="fas fa-phone-alt text-warning fs-4"></i>
                                </div>
                                <div class="text-end overflow-hidden">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $warehouse->contact_phone ?? 'N/A' }}</h6>
                                    <p class="text-muted small mb-0 text-truncate">{{ $warehouse->contact_email ?? 'No Email' }}</p>
                                </div>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning rounded-pill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                                    <i class="fas fa-calendar-alt text-primary fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-0 fw-bold text-dark">{{ $warehouse->created_at->format('M d, Y') }}</h6>
                                    <p class="text-muted small mb-0">Established</p>
                                </div>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary rounded-pill" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouse Inventory -->
            <div class="row g-4 mb-4">
                <!-- Standard Products -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                             <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-boxes text-primary me-2"></i>Current Inventory (Standard Products)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Product Name</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Stock Quantity</th>
                                            <th class="text-end pe-4">Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($simpleStocks as $stock)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            @if($stock->product && $stock->product->image)
                                                                <img src="{{ asset($stock->product->image) }}" class="rounded-3" width="40" height="40" style="object-fit: cover;">
                                                            @else
                                                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted opacity-50"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark small">{{ $stock->product->name ?? 'Unknown Product' }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><small class="text-muted">{{ $stock->product->sku ?? '-' }}</small></td>
                                                <td><span class="badge bg-light text-dark border">{{ $stock->product->category->name ?? 'Uncategorized' }}</span></td>
                                                <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-3">{{ $stock->quantity }}</span></td>
                                                <td class="text-end pe-4"><small class="text-muted">{{ $stock->updated_at->format('M d, Y h:i A') }}</small></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted small">No standard products in stock.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($simpleStocks->hasPages())
                                <div class="p-3 border-top border-light">
                                    {{ $simpleStocks->appends(['variation_page' => $variationStocks->currentPage()])->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Variation Products -->
                @if($variationStocks->count() > 0)
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                             <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tags text-info me-2"></i>Current Inventory (Product Variations)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Product Name</th>
                                            <th>Variation</th>
                                            <th>SKU</th>
                                            <th>Stock Quantity</th>
                                            <th class="text-end pe-4">Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($variationStocks as $stock)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                         <div class="me-3">
                                                            @if($stock->variation && $stock->variation->product && $stock->variation->product->image)
                                                                <img src="{{ asset($stock->variation->product->image) }}" class="rounded-3" width="40" height="40" style="object-fit: cover;">
                                                            @else
                                                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted opacity-50"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark small">{{ $stock->variation->product->name ?? 'Unknown Product' }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($stock->variation && $stock->variation->combinations)
                                                        @foreach($stock->variation->combinations as $combo)
                                                            <span class="badge bg-light text-dark border me-1">{{ $combo->attribute->name }}: {{ $combo->attributeValue->value }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                                <td><small class="text-muted">{{ $stock->variation->sku ?? $stock->variation->product->sku ?? '-' }}</small></td>
                                                <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10 rounded-pill px-3">{{ $stock->quantity }}</span></td>
                                                <td class="text-end pe-4"><small class="text-muted">{{ $stock->updated_at->format('M d, Y h:i A') }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($variationStocks->hasPages())
                                <div class="p-3 border-top border-light">
                                    {{ $variationStocks->appends(['simple_page' => $simpleStocks->currentPage()])->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-code-branch text-info me-2"></i>Linked Branches
                                </h5>
                                <span class="badge bg-light text-dark fw-bold px-3 py-2">{{ $warehouse->branches->count() }} Branch{{ $warehouse->branches->count() != 1 ? 'es' : '' }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($warehouse->branches->count() > 0)
                                <div class="table-responsive" style="overflow: visible;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-4">Branch Name</th>
                                                <th>Location</th>
                                                <th>Contact</th>
                                                <th>Employees</th>
                                                <th>Stock Items</th>
                                                <th class="text-end pe-4">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($warehouse->branches as $branch)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-info bg-opacity-10 p-2 rounded-3 me-2">
                                                                <i class="fas fa-store text-info small"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold text-dark small">{{ $branch->name }}</h6>
                                                                <small class="text-muted">ID: #{{ $branch->id }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><small class="text-muted">{{ $branch->location }}</small></td>
                                                    <td><small class="text-muted">{{ $branch->contact_info }}</small></td>
                                                    <td>
                                                        <span class="badge bg-light text-dark fw-bold">
                                                            <i class="fas fa-users tiny-icon me-1"></i>{{ $branch->employees_count ?? 0 }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark fw-bold">
                                                            <i class="fas fa-box tiny-icon me-1"></i>{{ $branch->branch_product_stocks_count ?? 0 }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <span class="badge bg-{{ $branch->status == 'active' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $branch->status == 'active' ? 'success' : 'secondary' }} border border-{{ $branch->status == 'active' ? 'success' : 'secondary' }} border-opacity-25 rounded-pill">
                                                            {{ ucfirst($branch->status ?? 'active') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="opacity-25 mb-3">
                                        <i class="fas fa-store-slash fa-4x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted fw-bold">No Branches Linked</h5>
                                    <p class="text-muted small">This warehouse doesn't have any branches assigned yet.</p>
                                    <a href="{{ route('branches.index') }}" class="btn btn-sm btn-outline-primary rounded-3 mt-2">
                                        <i class="fas fa-plus me-1"></i>Create Branch
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Warehouse Modal -->
    <div class="modal fade" id="editWarehouseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-light p-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Update Warehouse Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Warehouse Name</label>
                                <input type="text" class="form-control rounded-3 shadow-sm-hover" name="name" value="{{ $warehouse->name }}" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Location</label>
                                <input type="text" class="form-control rounded-3 shadow-sm-hover" name="location" value="{{ $warehouse->location }}" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Contact Phone</label>
                                <input type="text" class="form-control rounded-3 shadow-sm-hover" name="contact_phone" value="{{ $warehouse->contact_phone }}">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Contact Email</label>
                                <input type="email" class="form-control rounded-3 shadow-sm-hover" name="contact_email" value="{{ $warehouse->contact_email }}">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted">Status</label>
                                <select class="form-select rounded-3 shadow-sm-hover" name="status" required>
                                    <option value="active" {{ $warehouse->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $warehouse->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-light p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-3 transition-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm transition-2">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection