@extends('erp.master')

@section('title', 'Payment Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}" class="text-decoration-none text-muted">Payments</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Voucher Details</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Payment Voucher #{{ str_pad($supplierPayment->id, 6, '0', STR_PAD_LEFT) }}</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button onclick="window.print()" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-print me-2"></i>Print Voucher
                    </button>
                    <a href="{{ route('supplier-payments.index') }}" class="btn btn-create-premium">
                        <i class="fas fa-list me-2"></i>Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="premium-card overflow-hidden">
                        <div class="card-header bg-dark text-white p-5 text-center position-relative">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="fas fa-shield-alt fa-4x text-white"></i>
                            </div>
                            <div class="small text-uppercase tracking-widest opacity-50 mb-2">Total Amount Disbursed</div>
                            <h1 class="display-4 fw-bold mb-0">{{ number_format($supplierPayment->amount, 2) }}৳</h1>
                            <div class="bg-success d-inline-block px-3 py-1 rounded-pill small fw-bold mt-3">TRANSACTION SUCCESSFUL</div>
                        </div>
                        <div class="card-body p-4 p-xl-5">
                            <div class="row g-4 mb-5">
                                <div class="col-6">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Beneficiary</label>
                                    <h6 class="fw-bold text-dark mb-0">{{ $supplierPayment->supplier->name }}</h6>
                                    <p class="small text-muted mb-0">{{ $supplierPayment->supplier->company_name }}</p>
                                </div>
                                <div class="col-6 text-end">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Execution Date</label>
                                    <h6 class="fw-bold text-dark mb-0">{{ $supplierPayment->payment_date->format('d F, Y') }}</h6>
                                    <p class="small text-muted mb-0">{{ $supplierPayment->payment_date->format('h:i A') }}</p>
                                </div>
                                <div class="col-6">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Payment Channel</label>
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-2 fw-bold">
                                        {{ strtoupper(str_replace('_', ' ', $supplierPayment->payment_method)) }}
                                    </span>
                                </div>
                                <div class="col-6 text-end">
                                    <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Txn Reference</label>
                                    <h6 class="fw-bold text-dark mb-0">{{ $supplierPayment->reference ?: 'System Internal' }}</h6>
                                </div>
                            </div>

                            @if($supplierPayment->bill)
                            <div class="p-4 bg-light rounded-4 border mb-5">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Allocation Detail</label>
                                        <h6 class="fw-bold text-dark mb-0">Purchase Bill #{{ $supplierPayment->bill->bill_number }}</h6>
                                    </div>
                                    <a href="{{ route('purchase.show', $supplierPayment->bill->purchase_id) }}" class="btn btn-sm btn-white border fw-bold">View Bill</a>
                                </div>
                            </div>
                            @endif

                            <div class="pt-4 border-top">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Authorized By</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold small">
                                                {{ strtoupper(substr($supplierPayment->creator->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <span class="small fw-bold text-dark">{{ $supplierPayment->creator->name ?? 'System Admin' }}</span>
                                        </div>
                                    </div>
                                    @if($supplierPayment->note)
                                    <div class="col-md-6 mt-3 mt-md-0">
                                        <label class="extra-small fw-bold text-muted text-uppercase d-block mb-1">Official Remarks</label>
                                        <p class="small text-muted mb-0 font-italic">"{{ $supplierPayment->note }}"</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .breadcrumb-premium { font-size: 0.85rem; }
    </style>
@endpush
