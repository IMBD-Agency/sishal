@extends('erp.master')

@section('title', 'Fund Transfers')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Fund Transfers</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-info text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Fund Transfer History</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle shadow-sm border-0 bg-white" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportData('excel')"><i class="fas fa-file-excel me-2 text-success"></i>Excel Report</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportData('pdf')"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF Report</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('transfers.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus-circle me-2"></i>New Transfer
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="summary-card-premium p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 p-3">
                            <i class="fas fa-exchange-alt fs-4"></i>
                        </div>
                        <div>
                            <div class="small opacity-75 text-uppercase fw-bold">Total Transfers</div>
                            <h4 class="fw-bold mb-0">{{ $transfers->total() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card p-3 d-flex align-items-center gap-3 border-0 shadow-sm">
                        <div class="rounded-circle bg-success-subtle p-3 text-success">
                            <i class="fas fa-money-bill-wave fs-4"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Total Amount</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalTransfers, 2) }}৳</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('transfers.index') }}" class="row g-3" id="filterForm">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">To Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Branch</label>
                            <select name="branch_id" class="form-select select2-simple">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Account</label>
                            <select name="from_account_id" class="form-select select2-simple">
                                <option value="">All Accounts</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ request('from_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->provider_name }} - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transfers Table -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>From (Source)</th>
                                    <th>To (Destination)</th>
                                    <th class="text-end">Amount</th>
                                    <th>Reference</th>
                                    <th>Memo</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $transfer)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $transfer->transfer_date->format('d M, Y') }}</div>
                                        <div class="small text-muted">{{ $transfer->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $transfer->fromAccount->provider_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            @if($transfer->fromAccount->branch_id)
                                                <span class="badge bg-soft-primary">{{ $transfer->fromAccount->branch->name ?? 'Branch' }}</span>
                                            @elseif($transfer->fromAccount->warehouse_id)
                                                <span class="badge bg-soft-info">{{ $transfer->fromAccount->warehouse->name ?? 'Warehouse' }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $transfer->toAccount->provider_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            @if($transfer->toAccount->branch_id)
                                                <span class="badge bg-soft-primary">{{ $transfer->toAccount->branch->name ?? 'Branch' }}</span>
                                            @elseif($transfer->toAccount->warehouse_id)
                                                <span class="badge bg-soft-info">{{ $transfer->toAccount->warehouse->name ?? 'Warehouse' }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary fs-5">{{ number_format($transfer->amount, 2) }}৳</span>
                                    </td>
                                    <td>{{ $transfer->reference ?: '-' }}</td>
                                    <td>{{ $transfer->memo ?: '-' }}</td>
                                    <td class="text-center pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                                <li><a class="dropdown-item" href="{{ route('transfers.show', $transfer->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST" onsubmit="return confirm('Delete this transfer? This will reverse the amounts.')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted opacity-50">
                                            <i class="fas fa-exchange-alt fa-4x mb-3"></i>
                                            <p class="fw-bold mb-0">No fund transfers found.</p>
                                            <a href="{{ route('transfers.create') }}" class="btn btn-primary mt-3">Create First Transfer</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($transfers->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function exportData(format) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        
        let url = '';
        if (format === 'excel') {
            url = "{{ route('transfers.export.excel') }}";
        } else {
            url = "{{ route('transfers.export.pdf') }}";
        }
        
        window.isDownloadNavigation = true;
        window.location.href = url + '?' + params;
        
        // Reset flag after a delay
        setTimeout(() => { window.isDownloadNavigation = false; }, 2000);
    }
</script>
@endpush
