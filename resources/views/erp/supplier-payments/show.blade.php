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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}" class="text-decoration-none text-muted">Payments</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Voucher Details</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Payment Voucher #{{ str_pad($supplierPayment->id, 6, '0', STR_PAD_LEFT) }}</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">

                    <a href="{{ route('supplier-payments.index') }}" class="btn btn-create-premium">
                        <i class="fas fa-list me-2"></i>Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div id="printableVoucher" class="voucher-container">
                        <div class="premium-card shadow-lg border-0 bg-white">
                            <!-- Official Header Section -->
                            <div class="voucher-header p-5 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-3 mb-4">
                                            <div class="voucher-icon bg-success text-white rounded-circle shadow-sm" style="width: 50px; height: 50px;">
                                                <i class="fas fa-file-invoice-dollar fs-4"></i>
                                            </div>
                                            <div>
                                                <h3 class="fw-bold mb-0 text-dark">PAYMENT VOUCHER</h3>
                                                <div class="extra-small text-muted fw-bold text-uppercase">Official Transaction Record</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="voucher-number-badge d-inline-block">
                                            <span class="text-muted small fw-bold text-uppercase me-2">Voucher No</span>
                                            <span class="h4 fw-bold text-dark mb-0">#SP-{{ str_pad($supplierPayment->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div class="mt-2 small text-muted fw-bold">Issued on {{ $supplierPayment->payment_date->format('d M, Y') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-5">
                                <!-- Transaction Summary Row -->
                                <div class="row g-5 mb-5 align-items-center">
                                    <div class="col-md-7">
                                        <div class="beneficiary-details p-4 rounded-4 bg-light border-0 shadow-sm">
                                            <div class="text-uppercase extra-small fw-bold text-muted mb-3 tracking-wider">Beneficiary Information</div>
                                            <h4 class="fw-bold text-dark mb-1">{{ $supplierPayment->supplier->name }}</h4>
                                            @if($supplierPayment->supplier->company_name)
                                                <div class="fw-bold text-primary small mb-3">{{ $supplierPayment->supplier->company_name }}</div>
                                            @endif
                                            
                                            <div class="row g-3 mt-2">
                                                <div class="col-6">
                                                    <div class="extra-small text-muted fw-bold text-uppercase">Contact Number</div>
                                                    <div class="small fw-bold text-dark">{{ $supplierPayment->supplier->phone }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="extra-small text-muted fw-bold text-uppercase">Email Address</div>
                                                    <div class="small fw-bold text-dark">{{ $supplierPayment->supplier->email ?: 'N/A' }}</div>
                                                </div>
                                                <div class="col-12 mt-3">
                                                    <div class="extra-small text-muted fw-bold text-uppercase">Location</div>
                                                    <div class="small fw-bold text-dark">{{ $supplierPayment->supplier->city ?? '-' }}, {{ $supplierPayment->supplier->country ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="p-4 rounded-4 text-center border-0 bg-success bg-opacity-10 shadow-sm">
                                            <div class="text-uppercase extra-small fw-bold text-muted mb-2 tracking-wider">Total Disbursed</div>
                                            <div class="h2 fw-bold text-success mb-0">{{ number_format($supplierPayment->amount, 2) }} ৳</div>
                                            <div class="mt-3">
                                                <span class="badge bg-success text-white px-3 py-2 rounded border-0">
                                                    <i class="fas fa-check-circle me-1"></i> PAID & CLEARED
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Specifics -->
                                <div class="row g-4 mb-5 pb-4 border-bottom">
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-light rounded-3 text-center h-100">
                                            <div class="extra-small text-muted fw-bold text-uppercase mb-2">Payment Mode</div>
                                            <div class="fw-bold text-dark h6 mb-0 text-uppercase">
                                                @if($supplierPayment->financialAccount)
                                                    <i class="fas {{ $supplierPayment->financialAccount->type == 'bank' ? 'fa-university text-primary' : ($supplierPayment->financialAccount->type == 'cash' ? 'fa-wallet text-success' : 'fa-mobile-alt text-info') }} me-2"></i>
                                                    {{ $supplierPayment->financialAccount->provider_name }}
                                                @else
                                                    <i class="fas {{ $supplierPayment->payment_method == 'cash' ? 'fa-wallet text-success' : 'fa-university text-primary' }} me-2"></i>
                                                    {{ str_replace('_', ' ', $supplierPayment->payment_method) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-light rounded-3 text-center h-100">
                                            <div class="extra-small text-muted fw-bold text-uppercase mb-2">TXN / Reference</div>
                                            <div class="fw-bold text-primary h6 mb-0">{{ $supplierPayment->reference ?: 'System Generated' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-light rounded-3 text-center h-100">
                                            <div class="extra-small text-muted fw-bold text-uppercase mb-2">Branch / Outlet</div>
                                            <div class="fw-bold text-dark h6 mb-0">Head Office</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Allocation Section -->
                                @if($supplierPayment->bill)
                                <div class="allocation-area p-4 rounded-4 border border-info border-opacity-25 mb-5 bg-info bg-opacity-10">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-sm bg-info text-white rounded-circle"><i class="fas fa-link"></i></div>
                                                <div>
                                                    <div class="extra-small text-muted fw-bold text-uppercase mb-0">Allocated Bill Reference</div>
                                                    <h6 class="fw-bold text-dark mb-0">Purchase Bill #{{ $supplierPayment->bill->bill_number }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <a href="{{ route('purchase.show', $supplierPayment->bill->purchase_id) }}" class="btn btn-sm btn-outline-info fw-bold rounded-pill px-3">Review Full Bill</a>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Memo Section -->
                                @if($supplierPayment->note)
                                <div class="memo-box p-4 rounded-4 bg-light mb-5 font-italic text-muted border-start border-4 border-secondary">
                                    <div class="extra-small fw-bold text-uppercase text-secondary mb-2">Internal Remarks:</div>
                                    "{{ $supplierPayment->note }}"
                                </div>
                                @endif

                                <!-- Footer Authorizations -->
                                <div class="row align-items-end mt-5 pt-5">
                                    <div class="col-6">
                                        <div class="recorded-info">
                                            <div class="extra-small fw-bold text-muted text-uppercase mb-3 tracking-wider">Transaction Recorded By</div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                                    {{ strtoupper(substr($supplierPayment->creator->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="small fw-bold text-dark lh-1">{{ $supplierPayment->creator->name ?? 'System Admin' }}</div>
                                                    <div class="extra-small text-muted fw-bold mt-1">Authorized User</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="signature-box d-inline-block text-center border-top pt-2 ps-5 pe-5">
                                            <div class="author-placeholder mb-4"></div>
                                            <div class="extra-small fw-bold text-muted text-uppercase tracking-wider">Authorized Signature</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Final Security Footer -->
                            <div class="card-footer bg-light p-4 text-center border-0 text-muted extra-small fw-bold">
                                <i class="fas fa-shield-alt me-2 text-theme opacity-50"></i> This is an electronically generated document. No signature is required for digital verification.
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
        .fw-800 { font-weight: 800 !important; }
        .fw-900 { font-weight: 900 !important; }
        .tracking-tighter { letter-spacing: -0.05em; }
        .bg-theme { background-color: var(--primary-green, #198754) !important; }
        .text-theme { color: var(--primary-green, #198754) !important; }
        .border-theme { border-color: var(--primary-green, #198754) !important; }
        
        .voucher-container {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
        }

        .voucher-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .amount-card {
            background: linear-gradient(135deg, var(--primary-green, #198754), #157347);
            box-shadow: 0 10px 20px rgba(25, 135, 84, 0.2);
        }

        .author-placeholder {
            height: 40px;
        }

        .btn-outline-theme {
            color: var(--primary-green, #198754);
            border: 2px solid var(--primary-green, #198754);
        }

        .btn-outline-theme:hover {
            background-color: var(--primary-green, #198754);
            color: white;
        }

        @media print {
            .glass-header, .sidebar, .main-header, .btn-create-premium, .btn-light {
                display: none !important;
            }
            .main-content { margin: 0 !important; padding: 0 !important; }
            .voucher-container { background: white !important; padding: 0 !important; }
            .premium-card { box-shadow: none !important; border: 1px solid #eee !important; }
            .amount-card { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
@endpush
