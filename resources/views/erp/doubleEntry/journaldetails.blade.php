@extends('erp.master')

@section('title', 'Journal Details')

@push('css')
<style>
    .btn-action-premium {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #eef0f7;
        background: #fff;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .btn-action-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        border-color: currentColor;
    }
    .icon-box-sm {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ls-1 { letter-spacing: 0.5px; }
    .fw-500 { font-weight: 500; }
    .fw-600 { font-weight: 600; }
    .fw-700 { font-weight: 700; }
    
    @media print {
        .glass-header, .btn-create-premium, .btn-action-premium, .modal, .sidebar-wrapper, .main-header {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .premium-card {
            box-shadow: none !important;
            border: 1px solid #eee !important;
        }
    }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('journal.list') }}" class="text-decoration-none text-muted">Journal Entries</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Voucher Details</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Voucher #{{ $journal->voucher_no }}</h4>
                        @if($journal->isBalanced())
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                <i class="fas fa-check-circle me-1"></i>Balanced
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">
                                <i class="fas fa-exclamation-circle me-1"></i>Unbalanced
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button class="btn btn-light border shadow-sm fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                    <button class="btn btn-create-premium text-nowrap shadow-sm" onclick="exportJournal()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Journal Information & Summary Section -->
        <div class="container-fluid px-4 py-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="premium-card shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                <span class="icon-box-sm bg-primary bg-opacity-10 text-primary me-2 rounded">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                General Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Voucher No</label>
                                    <div class="h6 mb-0 fw-700 text-dark">{{ $journal->voucher_no ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Journal Date</label>
                                    <div class="h6 mb-0 fw-700 text-dark">{{ $journal->entry_date ? $journal->entry_date->format('d M, Y') : 'N/A' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Journal Type</label>
                                    <div>
                                        @if($journal->type)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1">{{ $journal->type }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border px-3 py-1">General</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Created By</label>
                                    <div class="h6 mb-0 fw-700 text-dark">{{ $journal->createdBy->first_name ?? ($journal->creator->first_name ?? 'N/A') }}</div>
                                </div>
                            </div>

                            @if($journal->description)
                            <div class="mt-4 pt-4 border-top">
                                <label class="form-label text-muted small text-uppercase fw-bold ls-1 mb-1">Memo / Description</label>
                                <p class="mb-0 text-dark opacity-75 fw-500">{{ $journal->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="premium-card shadow-sm h-100 border-0 overflow-hidden position-relative bg-white">
                        <div class="card-body p-4 z-index-1 position-relative d-flex flex-column h-100">
                            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Voucher Financials</h5>
                            
                            <div class="flex-grow-1">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-500">Total Debits</span>
                                    <span class="h5 mb-0 fw-bold text-success">৳{{ number_format($journal->total_debit, 2) }}</span>
                                </div>
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-500">Total Credits</span>
                                    <span class="h5 mb-0 fw-bold text-warning">৳{{ number_format($journal->total_credit, 2) }}</span>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Balanced Status</span>
                                @if($journal->isBalanced())
                                    <div class="d-flex align-items-center text-success fw-bold">
                                        <i class="fas fa-check-circle me-2 fa-lg"></i>
                                        <span>Yes, Balanced</span>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center text-danger fw-bold">
                                        <i class="fas fa-times-circle me-2 fa-lg"></i>
                                        <span>Unbalanced</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Subtle background icon -->
                        <div class="position-absolute" style="bottom: -15px; right: -15px; opacity: 0.03; pointer-events: none;">
                            <i class="fas fa-balance-scale fa-10x text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entries Section -->
        <div class="container-fluid px-4 pb-4">
            <div class="premium-card shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom py-4">
                     <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <span class="icon-box-sm bg-primary bg-opacity-10 text-primary me-2 rounded">
                            <i class="fas fa-list-ul"></i>
                        </span>
                        Journal Entries
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Chart of Account</th>
                                    <th>Financial Account</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th>Memo</th>
                                </tr>
                            </thead>
                            <tbody id="entriesTableBody">
                                @forelse($journal->entries as $entry)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $entry->chartOfAccount->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $entry->chartOfAccount->code ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($entry->financialAccount)
                                                <div class="text-dark">{{ $entry->financialAccount->provider_name ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $entry->financialAccount->account_number ?? 'N/A' }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($entry->debit > 0)
                                                <span class="fw-bold text-success">৳{{ number_format($entry->debit, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($entry->credit > 0)
                                                <span class="fw-bold text-warning">৳{{ number_format($entry->credit, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $entry->memo ?? '—' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <div class="opacity-25 mb-3">
                                                <i class="fas fa-receipt fa-4x"></i>
                                            </div>
                                            <h6 class="fw-bold">No transactions recorded yet</h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light bg-opacity-75">
                                <tr class="fw-bold border-top-0">
                                    <td colspan="2" class="text-end ps-4 py-4 text-dark h6 mb-0">Closing Totals</td>
                                    <td class="text-end text-success py-4 h6 mb-0">৳{{ number_format($journal->total_debit, 2) }}</td>
                                    <td class="text-end text-warning py-4 h6 mb-0">৳{{ number_format($journal->total_credit, 2) }}</td>
                                    <td class="pe-4 text-end">
                                        @if($journal->isBalanced())
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-4 py-2 rounded-pill">
                                                <i class="fas fa-shield-alt me-2"></i>Voucher Balanced
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-4 py-2 rounded-pill">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Unbalanced
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection