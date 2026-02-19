@extends('erp.master')

@section('title', 'Transfer Invoice')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <div class="glass-header no-print">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('stocktransfer.list') }}" class="text-decoration-none text-muted">Stock Transfer</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Invoice Details</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">
                        @if($transfer->invoice_number)
                            Invoice #{{ $transfer->invoice_number }}
                        @else
                            Transfer Voucher #{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}
                        @endif
                    </h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button onclick="window.print()" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-print me-2"></i>Print Invoice
                    </button>
                    <a href="{{ route('stocktransfer.list') }}" class="btn btn-create-premium">
                        <i class="fas fa-list me-2"></i>Back to History
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Print-Ready Invoice -->
                    <div class="invoice-container bg-white shadow-sm">
                        <!-- Company Header -->
                        @php
                            $settings = \App\Models\GeneralSetting::first();
                        @endphp
                        <div class="invoice-header border-bottom pb-3 mb-4">
                            <div class="text-center mb-2">
                                <h2 class="fw-bold text-dark mb-2">{{ $settings->site_title ?? config('app.name', 'Your Company Name') }}</h2>
                            </div>
                            <div class="text-center">
                                <p class="text-muted small mb-1">{{ $settings->contact_address ?? 'Address Line 1, City, Country' }}</p>
                                <p class="text-muted small mb-0">
                                    Phone: {{ $settings->contact_phone ?? '+880-XXX-XXXXXX' }} | 
                                    Email: {{ $settings->contact_email ?? 'info@company.com' }}
                                    @if($settings->website_url)
                                     | {{ $settings->website_url }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Invoice Title -->
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-uppercase mb-1" style="letter-spacing: 2px;">Stock Transfer Invoice</h3>
                            <p class="text-muted mb-0">
                                @if($transfer->invoice_number)
                                    Invoice No: <strong>{{ $transfer->invoice_number }}</strong>
                                @else
                                    Voucher No: <strong>ST-{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                @endif
                            </p>
                        </div>

                        <!-- Invoice Info Grid -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold text-uppercase small text-muted mb-2">From</h6>
                                    <p class="mb-0 fw-bold">
                                        @if($transfer->from_type === 'branch')
                                            {{ $transfer->fromBranch->name ?? '-' }}
                                        @else
                                            {{ $transfer->fromWarehouse->name ?? '-' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold text-uppercase small text-muted mb-2">To</h6>
                                    <p class="mb-0 fw-bold">
                                        @if($transfer->to_type === 'branch')
                                            {{ $transfer->toBranch->name ?? '-' }}
                                        @else
                                            {{ $transfer->toWarehouse->name ?? '-' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-4">
                                <p class="mb-1"><strong class="text-muted small">Date:</strong></p>
                                <p class="fw-bold">{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d M Y') : '-' }}</p>
                            </div>
                            <div class="col-4">
                                <p class="mb-1"><strong class="text-muted small">Requested By:</strong></p>
                                <p class="fw-bold">{{ $transfer->requestedPerson->name ?? 'Admin User' }}</p>
                            </div>
                            <div class="col-4">
                                <p class="mb-1"><strong class="text-muted small">Status:</strong></p>
                                <p>
                                    <span class="badge {{ $transfer->status === 'delivered' ? 'bg-success' : ($transfer->status === 'rejected' ? 'bg-danger' : ($transfer->status === 'approved' ? 'bg-info' : 'bg-warning')) }} px-3 py-1">
                                        {{ strtoupper($transfer->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase small text-muted mb-3">Transfer Items</h6>
                            <table class="table table-bordered invoice-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Product Description</th>
                                        <th style="width: 200px;">Attributes</th>
                                        <th style="width: 100px;" class="text-center">Quantity</th>
                                        <th style="width: 120px;" class="text-end">Unit Price</th>
                                        <th style="width: 120px;" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transfers as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $item->product->name ?? '-' }}</div>
                                            <div class="small text-muted">SKU: {{ $item->product->style_number ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($item->variation)
                                                @php
                                                    $color = null; $size = null;
                                                    if($item->variation->combinations) {
                                                        foreach($item->variation->combinations as $combo) {
                                                            $name = strtolower($combo->attribute->name ?? '');
                                                            if(in_array($name, ['color','colour'])) $color = $combo->attributeValue->value ?? '';
                                                            if(in_array($name, ['size','sizes'])) $size = $combo->attributeValue->value ?? '';
                                                        }
                                                    }
                                                @endphp
                                                <span class="badge bg-light text-dark border me-1 small">{{ $size ?? '-' }}</span>
                                                <span class="badge bg-light text-dark border small">{{ $color ?? '-' }}</span>
                                            @else
                                                <span class="text-muted small">Standard</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ number_format($item->quantity, 0) }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">TOTAL</td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($transfers->sum('quantity'), 0) }}</td>
                                        <td></td>
                                        <td class="text-end fw-bold text-success fs-5">{{ number_format($transfers->sum('total_price'), 2) }} ৳</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Financial Details -->
                        @if($transfers->sum('paid_amount') > 0)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-uppercase small text-muted mb-2">Sender Financials</h6>
                                <div class="border rounded p-3 bg-light-success">
                                    <p class="mb-1 small">Account: <strong>{{ $transfer->senderAccount->provider_name ?? ($transfer->sender_account_type ?? 'N/A') }}</strong></p>
                                    <p class="mb-0 small">Number: <strong>{{ $transfer->senderAccount->account_number ?? ($transfer->sender_account_number ?? '-') }}</strong></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-uppercase small text-muted mb-2">Receiver Financials</h6>
                                <div class="border rounded p-3 bg-light-danger">
                                    <p class="mb-1 small">Account: <strong>{{ $transfer->receiverAccount->provider_name ?? ($transfer->receiver_account_type ?? 'N/A') }}</strong></p>
                                    <p class="mb-0 small">Number: <strong>{{ $transfer->receiverAccount->account_number ?? ($transfer->receiver_account_number ?? '-') }}</strong></p>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="alert alert-{{ $transfer->status === 'delivered' ? 'success' : 'info' }} border-0 py-2 d-flex align-items-center mb-0">
                                    <i class="fas fa-{{ $transfer->status === 'delivered' ? 'check-double' : 'clock' }} me-2"></i>
                                    <span class="small fw-bold">
                                        @if($transfer->status === 'delivered')
                                            Financial Settlement Complete: {{ number_format($transfers->sum('paid_amount'), 2) }} ৳ transferred from Receiver to Sender.
                                        @else
                                            Financial Settlement Pending Arrival: {{ number_format($transfers->sum('paid_amount'), 2) }} ৳ will be moved upon delivery.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Notes Section -->
                        @if($transfer->notes)
                        <div class="mb-4">
                            <h6 class="fw-bold text-uppercase small text-muted mb-2">Notes / Remarks</h6>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0 small">{{ $transfer->notes }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Signature Section -->
                        <div class="row mt-5 pt-4 border-top">
                            <div class="col-4 text-center">
                                <div class="border-top pt-2 mt-5 d-inline-block" style="min-width: 200px;">
                                    <p class="mb-0 small fw-bold">Prepared By</p>
                                    <p class="mb-0 small text-muted">{{ $transfer->requestedPerson->name ?? 'Admin' }}</p>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="border-top pt-2 mt-5 d-inline-block" style="min-width: 200px;">
                                    <p class="mb-0 small fw-bold">Approved By</p>
                                    <p class="mb-0 small text-muted">{{ $transfer->approvedPerson->name ?? '___________' }}</p>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="border-top pt-2 mt-5 d-inline-block" style="min-width: 200px;">
                                    <p class="mb-0 small fw-bold">Received By</p>
                                    <p class="mb-0 small text-muted">___________</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="small text-muted mb-0">This is a computer-generated document. No signature is required.</p>
                            <p class="small text-muted mb-0">Printed on: {{ now()->format('d M Y, h:i A') }}</p>
                        </div>

                        <!-- Action Buttons (No Print) -->
                        <div class="no-print mt-4 pt-4 border-top">
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                @if($transfer->status == 'pending')
                                    <form action="{{ route('stocktransfer.status', $transfer->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success px-5 fw-bold" onclick="return confirm('Approve this transfer invoice? Source stock will be deducted for all items.')">
                                            <i class="fas fa-check-circle me-2"></i>APPROVE INVOICE
                                        </button>
                                    </form>
                                    <form action="{{ route('stocktransfer.status', $transfer->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-warning px-4 fw-bold text-white" onclick="return confirm('Reject this transfer invoice?')">
                                            <i class="fas fa-times-circle me-2"></i>REJECT
                                        </button>
                                    </form>
                                @endif

                                @if($transfer->status == 'approved')
                                    <form action="{{ route('stocktransfer.status', $transfer->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="shipped">
                                        <button type="submit" class="btn btn-info px-5 fw-bold text-white" onclick="return confirm('Mark this invoice as Shipped?')">
                                            <i class="fas fa-shipping-fast me-2"></i>MARK AS SHIPPED
                                        </button>
                                    </form>
                                @endif

                                @if($transfer->status == 'shipped')
                                    <form action="{{ route('stocktransfer.status', $transfer->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn btn-primary px-5 fw-bold" onclick="return confirm('Mark this invoice as Delivered? Stock will be added to destination for all items.')">
                                            <i class="fas fa-box-open me-2"></i>CONFIRM DELIVERY
                                        </button>
                                    </form>
                                @endif

                                @if(in_array($transfer->status, ['pending', 'rejected']))
                                    <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entire transfer invoice? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger px-4 fw-bold">
                                            <i class="fas fa-trash-alt me-2"></i>VOID TRANSFER
                                        </button>
                                    </form>
                                @endif
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
        .extra-small { font-size: 0.72rem; }
        
        /* Invoice Styles */
        .invoice-container {
            padding: 40px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .invoice-header h2 {
            color: #2c3e50;
            font-size: 28px;
        }
        
        .invoice-table {
            font-size: 14px;
        }
        
        .invoice-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            padding: 12px 8px;
        }
        
        .invoice-table tbody td {
            padding: 10px 8px;
            vertical-align: middle;
        }
        
        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .invoice-container, .invoice-container * {
                visibility: visible;
            }
            
            .invoice-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
            
            .no-print {
                display: none !important;
            }
            
            .invoice-table {
                page-break-inside: auto;
            }
            
            .invoice-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
            
            @page {
                margin: 1cm;
            }
        }
    </style>
@endpush