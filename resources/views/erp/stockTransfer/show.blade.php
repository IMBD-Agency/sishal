@extends('erp.master')

@section('title', 'Transfer Voucher Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('stocktransfer.list') }}" class="text-decoration-none text-muted">Stock Transfer</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Voucher Details</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Transfer Voucher #{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button onclick="window.print()" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-print me-2"></i>Print Voucher
                    </button>
                    <a href="{{ route('stocktransfer.list') }}" class="btn btn-create-premium">
                        <i class="fas fa-list me-2"></i>Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="premium-card overflow-hidden">
                        <!-- High Impact Status Header -->
                        <div class="card-header bg-dark text-white p-5 text-center position-relative">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="fas fa-shipping-fast fa-4x text-white"></i>
                            </div>
                            <div class="small text-uppercase tracking-widest opacity-50 mb-2">Total Quantity Transferred</div>
                            <h1 class="display-4 fw-bold mb-0">{{ number_format($transfer->quantity, 0) }} Units</h1>
                            <div class="mt-3">
                                <span class="badge {{ $transfer->status === 'delivered' ? 'bg-success' : ($transfer->status === 'rejected' ? 'bg-danger' : 'bg-info') }} px-3 py-2 rounded-pill fw-bold text-uppercase">
                                    Status: {{ $transfer->status }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 p-xl-5">
                            <!-- Information Grid -->
                            <div class="row g-4 mb-5 pb-4 border-bottom">
                                <div class="col-md-6">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Source Location (From)</label>
                                    <h6 class="fw-bold text-dark mb-0">
                                        @if($transfer->from_type === 'branch')
                                            Branch: {{ $transfer->fromBranch->name ?? '-' }}
                                        @else
                                            Warehouse: {{ $transfer->fromWarehouse->name ?? '-' }}
                                        @endif
                                    </h6>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Destination Target (To)</label>
                                    <h6 class="fw-bold text-dark mb-0">
                                        @if($transfer->to_type === 'branch')
                                            Branch: {{ $transfer->toBranch->name ?? '-' }}
                                        @else
                                            Warehouse: {{ $transfer->toWarehouse->name ?? '-' }}
                                        @endif
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Product Details</label>
                                    <h6 class="fw-bold text-primary mb-0">{{ $transfer->product->name ?? '-' }}</h6>
                                    <p class="small text-muted mb-0">Style No: {{ $transfer->product->style_number ?? '-' }} | Variant: {{ $transfer->variation->name ?? 'Standard' }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Initiation Date</label>
                                    <h6 class="fw-bold text-dark mb-0">{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d F, Y') : '-' }}</h6>
                                    <p class="small text-muted mb-0">Timestamp: {{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('h:i A') : '-' }}</p>
                                </div>
                            </div>

                            <!-- Workflow History -->
                            <div class="mb-5">
                                <h6 class="fw-bold text-uppercase text-muted extra-small mb-3">Workflow Audit Trail</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3">
                                            <span class="extra-small fw-bold text-muted d-block uppercase mb-1">Approved At</span>
                                            <span class="small fw-bold">{{ $transfer->approved_at ? \Carbon\Carbon::parse($transfer->approved_at)->format('d/m/Y') : 'Waiting...' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3">
                                            <span class="extra-small fw-bold text-muted d-block uppercase mb-1">Shipped At</span>
                                            <span class="small fw-bold">{{ $transfer->shipped_at ? \Carbon\Carbon::parse($transfer->shipped_at)->format('d/m/Y') : 'Waiting...' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3">
                                            <span class="extra-small fw-bold text-muted d-block uppercase mb-1">Delivered At</span>
                                            <span class="small fw-bold">{{ $transfer->delivered_at ? \Carbon\Carbon::parse($transfer->delivered_at)->format('d/m/Y') : 'Waiting...' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 h-100">
                                            <span class="extra-small fw-bold text-muted d-block uppercase mb-1">Category</span>
                                            <span class="small fw-bold">{{ $transfer->product->category->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks Block -->
                            <div class="p-4 bg-light rounded-4 border mb-5">
                                <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Internal Instructions/Notes</label>
                                <p class="small text-dark mb-0 font-italic">"{{ $transfer->notes ?: 'No additional notes provided for this consignment.' }}"</p>
                            </div>

                            <!-- Personnel Block -->
                            <div class="pt-4 border-top">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Requested By</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold small">
                                                {{ strtoupper(substr($transfer->requestedPerson->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <span class="small fw-bold text-dark">{{ $transfer->requestedPerson->name ?? 'Admin User' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-3 mt-md-0 text-md-end">
                                        <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Final Approval</label>
                                        <span class="small fw-bold text-dark">{{ $transfer->approvedPerson->name ?? 'Pending Authorization' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        @if(in_array($transfer->status, ['pending', 'rejected']))
                        <div class="card-footer bg-white border-top p-4 text-center">
                            <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transfer? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger px-4 fw-bold">
                                    <i class="fas fa-trash-alt me-2"></i>VOID TRANSFER
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .breadcrumb-premium { font-size: 0.85rem; }
        .extra-small { font-size: 0.72rem; }
        .avatar-xs { width: 30px; height: 30px; }
    </style>
@endpush