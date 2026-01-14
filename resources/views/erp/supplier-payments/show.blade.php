@extends('erp.master')

@section('title', 'Payment Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Payment Details</li>
                </ol>
            </nav>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white p-4 text-center">
                            <h2 class="fw-bold mb-0">tk {{ number_format($supplierPayment->amount, 2) }}</h2>
                            <p class="mb-0 opacity-75">Paid on {{ $supplierPayment->payment_date->format('d M, Y') }}</p>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                                    <span class="text-muted">Supplier</span>
                                    <span class="fw-bold text-dark text-end">{{ $supplierPayment->supplier->name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                                    <span class="text-muted">Payment Method</span>
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 text-uppercase">{{ str_replace('_', ' ', $supplierPayment->payment_method) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                                    <span class="text-muted">Reference</span>
                                    <span class="fw-bold text-dark">{{ $supplierPayment->reference ?: 'None' }}</span>
                                </li>
                                @if($supplierPayment->bill)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                                    <span class="text-muted">Associated Bill</span>
                                    <span class="fw-bold text-primary">#{{ $supplierPayment->bill->bill_number ?: $supplierPayment->bill->id }}</span>
                                </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 px-0">
                                    <span class="text-muted">Created By</span>
                                    <span class="fw-bold text-dark">{{ $supplierPayment->creator->name ?? 'System' }}</span>
                                </li>
                            </ul>
                            @if($supplierPayment->note)
                            <div class="mt-4 pt-4 border-top">
                                <h6 class="fw-bold text-muted small text-uppercase mb-2">Notes</h6>
                                <p class="mb-0 text-dark p-3 bg-light rounded-3">{{ $supplierPayment->note }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light border-0 p-4 text-center gap-2 d-flex justify-content-center">
                            <a href="{{ route('supplier-payments.index') }}" class="btn btn-outline-secondary px-4">Back to List</a>
                            <button onclick="window.print()" class="btn btn-primary px-4">Print Receipt</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
