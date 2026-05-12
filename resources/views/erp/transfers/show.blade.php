@extends('erp.master')

@section('title', 'Transfer Details')

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
                            <li class="breadcrumb-item"><a href="{{ route('transfers.index') }}" class="text-decoration-none text-muted">Fund Transfers</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Transfer #{{ $transfer->id }}</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Transfer Details</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('transfers.index') }}" class="btn btn-light fw-bold shadow-sm me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-uppercase text-muted small">Transfer Receipt</h5>
                                <span class="badge bg-primary rounded-pill px-3">#{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>
                        <div class="card-body p-4 p-xl-5">
                            <div class="text-center mb-5">
                                <h2 class="display-4 fw-bold text-primary">{{ number_format($transfer->amount, 2) }}৳</h2>
                                <p class="text-muted">Transfer Amount</p>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-4">
                                        <p class="small text-muted text-uppercase fw-bold mb-1">From (Source)</p>
                                        <h5 class="fw-bold mb-1">{{ $transfer->fromAccount->provider_name ?? 'N/A' }}</h5>
                                        <p class="small text-muted mb-0">{{ $transfer->fromAccount->account_number ?? '' }}</p>
                                        @if($transfer->fromAccount->branch_id)
                                            <span class="badge bg-soft-primary">{{ $transfer->fromAccount->branch->name ?? 'Branch' }}</span>
                                        @elseif($transfer->fromAccount->warehouse_id)
                                            <span class="badge bg-soft-info">{{ $transfer->fromAccount->warehouse->name ?? 'Warehouse' }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-4">
                                        <p class="small text-muted text-uppercase fw-bold mb-1">To (Destination)</p>
                                        <h5 class="fw-bold mb-1">{{ $transfer->toAccount->provider_name ?? 'N/A' }}</h5>
                                        <p class="small text-muted mb-0">{{ $transfer->toAccount->account_number ?? '' }}</p>
                                        @if($transfer->toAccount->branch_id)
                                            <span class="badge bg-soft-primary">{{ $transfer->toAccount->branch->name ?? 'Branch' }}</span>
                                        @elseif($transfer->toAccount->warehouse_id)
                                            <span class="badge bg-soft-info">{{ $transfer->toAccount->warehouse->name ?? 'Warehouse' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <p class="small text-muted text-uppercase fw-bold mb-1">Transfer Date</p>
                                    <p class="fw-bold">{{ $transfer->transfer_date->format('d M, Y') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="small text-muted text-uppercase fw-bold mb-1">Reference</p>
                                    <p class="fw-bold">{{ $transfer->reference ?: 'N/A' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="small text-muted text-uppercase fw-bold mb-1">Created By</p>
                                    <p class="fw-bold">{{ $transfer->creator->name ?? 'System' }}</p>
                                </div>
                            </div>

                            @if($transfer->memo)
                            <div class="mb-4">
                                <p class="small text-muted text-uppercase fw-bold mb-1">Memo</p>
                                <p class="fw-bold">{{ $transfer->memo }}</p>
                            </div>
                            @endif

                            @if($transfer->journal)
                            <div class="alert alert-info border-0">
                                <p class="small text-muted text-uppercase fw-bold mb-1">Journal Entry</p>
                                <p class="mb-0">
                                    <i class="fas fa-book me-2"></i>
                                    <a href="{{ route('journal.show', $transfer->journal->id) }}" class="fw-bold">
                                        {{ $transfer->journal->voucher_no }}
                                    </a>
                                </p>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-top p-4">
                            <form action="{{ route('transfers.destroy', $transfer->id) }}" method="POST" onsubmit="return confirm('Delete this transfer? This will reverse the amounts.')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Transfer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
