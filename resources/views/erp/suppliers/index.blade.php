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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Suppliers</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Supplier</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('suppliers.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus-circle me-2"></i>Add New Supplier
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Analytics Filters -->
            <div class="premium-card mb-3 shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('suppliers.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Search Registry</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone, Company, Tax #...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">City</label>
                                <select name="city" class="form-select select2-setup" data-placeholder="All Cities">
                                    <option value=""></option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Country</label>
                                <select name="country" class="form-select select2-setup" data-placeholder="All Countries">
                                    <option value=""></option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top p-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('suppliers.index', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-outline-success btn-sm fw-bold px-3 no-loader" target="_blank">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a>
                                    <a href="{{ route('suppliers.index', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-outline-danger btn-sm fw-bold px-3 no-loader" target="_blank">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4 fw-bold text-muted" style="height: 42px; display: flex; align-items: center;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </a>
                                    <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                        <i class="fas fa-search me-2"></i>Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Search Wrapper -->
            <div class="d-flex justify-content-end align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted mb-0">Search:</label>
                    <input type="text" id="tableSearch" class="form-control form-control-sm table-search-input" placeholder="Quick Search..." style="width: 250px;">
                </div>
            </div>

            <!-- Supplier Table -->
            <div class="premium-card shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-truck me-2 text-primary"></i>Supplier Audit Registry</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact reporting-table table-hover align-middle mb-0" id="supplierTable">
                            <thead>
                                <tr>
                                    <th class="ps-3">Supplier / Company</th>
                                    <th>Contact Info</th>
                                    <th>Location</th>
                                    <th class="text-end">Current Balance</th>
                                    <th class="text-center">Tax #</th>
                                    <th class="text-center pe-3">Actions</th>
                                </tr>
                            </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle-sm me-2 bg-soft-primary text-primary d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 32px; height: 32px; background-color: #eef2ff; font-size: 11px;">
                                            {{ strtoupper(substr($supplier->name, 0, 1)) }}{{ strtoupper(substr(strpos($supplier->name, ' ') !== false ? explode(' ', $supplier->name)[1] : $supplier->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $supplier->name }}</div>
                                            @if($supplier->company_name)
                                                <div class="text-muted extra-small">{{ $supplier->company_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-bold">{{ $supplier->phone }}</span>
                                        @if($supplier->email)
                                            <span class="text-muted extra-small">{{ $supplier->email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($supplier->city || $supplier->country)
                                        <div class="text-dark">{{ $supplier->city }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country }}</div>
                                    @else
                                        <span class="text-muted">-</span>
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
                                    <span>{{ $supplier->tax_number ?? '-' }}</span>
                                </td>
                                <td class="text-center pe-3">
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
            // Table search functionality with Debounce
            let searchTimeout;
            $('#tableSearch').on('input', function() {
                const value = $(this).val().toLowerCase();
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(function() {
                    $('#supplierTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                }, 300);
            });
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
