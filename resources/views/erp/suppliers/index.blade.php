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
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('suppliers.index') }}" id="filterForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Anything</label>
                                <input type="text" class="form-control form-control-sm border-primary" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone, Company, Tax #...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">City</label>
                                <select name="city" class="form-select form-select-sm select2" data-placeholder="All Cities">
                                    <option value=""></option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Country</label>
                                <select name="country" class="form-select form-select-sm select2" data-placeholder="All Countries">
                                    <option value=""></option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-create-premium btn-sm flex-grow-1" style="height: 31px;">
                                    <i class="fas fa-search me-1"></i> APPLY FILTERS
                                </button>
                                <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm border shadow-sm px-3" style="height: 31px;" title="Reset">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Supplier Table -->
            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-truck me-2 text-primary"></i>Supplier Directory</h6>
                    <div class="d-flex align-items-center gap-2 ms-auto" style="max-width: 400px; width: 100%;">
                        <form action="{{ route('suppliers.index') }}" method="GET" class="w-100">
                            @foreach(request()->except(['search', 'page']) as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Quick Search Suppliers..." value="{{ request('search') }}">
                                <button class="btn btn-primary px-3 fw-bold" type="submit">SEARCH</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table premium-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Supplier / Company</th>
                                <th>Contact Info</th>
                                <th>Location</th>
                                <th class="text-end">Current Balance</th>
                                <th class="text-center">Tax #</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-soft-primary text-primary d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #eef2ff;">
                                            {{ strtoupper(substr($supplier->name, 0, 1)) }}{{ strtoupper(substr(strpos($supplier->name, ' ') !== false ? explode(' ', $supplier->name)[1] : $supplier->name, 0, 1)) }}
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
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    @php $balance = $supplier->balance; @endphp
                                    <span class="{{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-success' : 'text-muted') }}">
                                        {{ number_format(abs($balance), 2) }}
                                        <small class="ms-1 fw-normal opacity-75">{{ $balance > 0 ? 'DUE' : ($balance < 0 ? 'ADV' : '') }}</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted small">{{ $supplier->tax_number ?? '-' }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('supplier-payments.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-outline-success border-0 rounded-circle" title="Record Payment">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg premium-dropdown">
                                                <li><a class="dropdown-item" href="{{ route('suppliers.show', $supplier->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Profile</a></li>
                                                <li><a class="dropdown-item" href="{{ route('suppliers.ledger', $supplier->id) }}"><i class="fas fa-list-alt me-2 text-warning"></i>Ledger History</a></li>
                                                <li><a class="dropdown-item" href="{{ route('suppliers.edit', $supplier->id) }}"><i class="fas fa-edit me-2 text-info"></i>Edit Details</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="post" onsubmit="return confirm('Delete this supplier?')">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Remove Supplier</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
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
@push('scripts')
    <script>
        $(document).ready(function() {
            // Additional page-specific scripts can go here
        });
    </script>
    <style>
        /* Fix dropdown clipping in responsive tables */
        .table-responsive {
            overflow: clip !important; /* Allow vertical overflow while keeping horizontal scrolling */
            padding-bottom: 80px; /* Buffer for dropdowns */
            min-height: 400px; /* Ensure space for short tables */
        }
        .premium-dropdown {
            z-index: 1060 !important; /* Above table headers and other fixed elements */
        }
    </style>
@endpush
@endsection
