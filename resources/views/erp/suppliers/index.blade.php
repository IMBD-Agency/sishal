@extends('erp.master')

@section('title', 'Supplier Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Suppliers</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Supplier Database</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('suppliers.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus-circle me-2"></i>Add New Supplier
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Modern Filter Card -->
            <div class="premium-card mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('suppliers.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-9">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Suppliers</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control border-start-0 premium-search-input" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone, Company...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-create-premium flex-grow-1">
                                        <i class="fas fa-filter me-2"></i>Filter
                                    </button>
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-light border fw-bold" title="Reset Filters">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Supplier Table -->
            <div class="premium-card">
                <div class="table-responsive">
                    <table class="table premium-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Supplier / Company</th>
                                <th>Contact Information</th>
                                <th>Location</th>
                                <th class="text-center">Tax Number</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm">
                                            {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $supplier->name }}</div>
                                            @if($supplier->company_name)
                                                <div class="small text-muted">{{ $supplier->company_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column small">
                                        <span class="text-dark fw-500"><i class="fas fa-phone-alt text-muted me-2 extra-small"></i>{{ $supplier->phone }}</span>
                                        @if($supplier->email)
                                            <span class="text-muted"><i class="fas fa-envelope text-muted me-2 extra-small"></i>{{ $supplier->email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($supplier->city || $supplier->country)
                                        <div class="small text-dark">{{ $supplier->city }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country }}</div>
                                        @if($supplier->address)
                                            <div class="extra-small text-muted text-truncate" style="max-width: 150px;">{{ $supplier->address }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted small">{{ $supplier->tax_number ?? '-' }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg premium-dropdown">
                                            <li><a class="dropdown-item" href="{{ route('suppliers.show', $supplier->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                            <li><a class="dropdown-item" href="{{ route('suppliers.ledger', $supplier->id) }}"><i class="fas fa-book me-2 text-warning"></i>Supplier Ledger</a></li>
                                            <li><a class="dropdown-item" href="{{ route('suppliers.edit', $supplier->id) }}"><i class="fas fa-edit me-2 text-info"></i>Edit Info</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <div class="mb-3"><i class="fas fa-truck-loading fa-3x opacity-25"></i></div>
                                        <h5 class="fw-bold">No suppliers found</h5>
                                        <p class="mb-0">Try adjusting your filters or add a new supplier.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} entries
                        </span>
                        {{ $suppliers->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
