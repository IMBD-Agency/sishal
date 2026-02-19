@extends('erp.master')

@section('title', 'Purchase Invoice #' . $purchase->id)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        

        <!-- ON-SCREEN DASHBOARD VIEW -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom mb-4 no-print">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 text-uppercase small">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('purchase.list') }}" class="text-decoration-none text-muted">Purchase Registry</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">Invoice Details</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="fw-bold mb-0 text-dark">Purchase Invoice #{{ $purchase->id }}</h2>
                        @php
                            $statusMap = [
                                'pending' => ['class' => 'bg-warning-soft text-warning border-warning', 'icon' => 'fa-clock'],
                                'received' => ['class' => 'bg-success-soft text-success border-success', 'icon' => 'fa-check-circle'],
                                'cancelled' => ['class' => 'bg-danger-soft text-danger border-danger', 'icon' => 'fa-times-circle'],
                            ];
                            $s = $statusMap[$purchase->status] ?? ['class' => 'bg-secondary-soft text-secondary border-secondary', 'icon' => 'fa-question-circle'];
                        @endphp
                        <span class="badge border {{ $s['class'] }} px-3 py-2 rounded-pill fw-bold">
                            <i class="fas {{ $s['icon'] }} me-1"></i> {{ strtoupper($purchase->status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        @if($purchase->status === 'pending')
                        <div class="dropdown no-print">
                            <button class="btn btn-success px-4 rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-tasks me-2"></i>Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                <li>
                                    <form action="{{ route('purchase.updateStatus', $purchase->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="received">
                                        <button type="submit" class="dropdown-item py-2 px-3 fw-bold text-success">
                                            <i class="fas fa-check-circle me-2"></i>Mark as Received
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('purchase.updateStatus', $purchase->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="dropdown-item py-2 px-3 fw-bold text-danger">
                                            <i class="fas fa-times-circle me-2"></i>Cancel Purchase
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endif

                        <button onclick="window.print()" class="btn btn-dark shadow-sm px-4 rounded-pill no-print">
                            <i class="fas fa-print me-2"></i>Print Invoice
                        </button>
                        <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-outline-primary px-4 rounded-pill">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <a href="{{ route('purchase.list') }}" class="btn btn-light border px-3 rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 pb-5 no-print">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card info-card shadow-sm border-top border-4 border-success">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 text-uppercase text-muted small"><i class="fas fa-truck me-2 text-success"></i>Supplier Details</h6>
                            <div class="mb-3">
                                <div class="info-label">Supplier Name</div>
                                <div class="info-value fs-5">{{ $purchase->supplier->name ?? 'Internal / Unknown' }}</div>
                            </div>
                            <div class="mb-1">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value">{{ $purchase->supplier->mobile ?? $purchase->supplier->phone ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card info-card shadow-sm border-top border-4 border-primary">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 text-uppercase text-muted small"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Destination</h6>
                            <div class="mb-3">
                                <div class="info-label">Location</div>
                                <div class="info-value fs-5">{{ $purchase->location_name }}</div>
                            </div>
                            <div class="mb-1">
                                <div class="info-label">Shipment Type</div>
                                <div class="info-value text-capitalize">{{ $purchase->ship_location_type }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card info-card shadow-sm bg-primary-soft">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 text-uppercase text-muted small text-success"><i class="fas fa-calendar-check me-2"></i>Registry Info</h6>
                            <div class="mb-3">
                                <div class="info-label">Purchase Date</div>
                                <div class="info-value text-success fs-5">
                                    {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d F, Y') : '-' }}
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="info-label">Invoice Reference</div>
                                <div class="info-value fw-bold text-dark">#{{ $purchase->bill->bill_number ?? 'B-'.str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Product Manifest</h5>
                    <span class="badge bg-light text-success fw-bold border border-success border-opacity-25 px-3 py-2 rounded-pill">Total: {{ count($purchase->items) }} Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 item-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">SL</th>
                                    <th>Description & Specifications</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $idx => $item)
                                    <tr>
                                        <td class="ps-4 text-muted small fw-bold">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">{{ $item->product->name ?? 'Unknown Product' }}</div>
                                            @if($item->variation_id && $item->variation)
                                                <div class="small text-muted mt-1">
                                                    <span class="badge bg-light text-dark fw-medium border">Variation: {{ $item->variation->name }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold text-dark">{{ $item->quantity }}</td>
                                        <td class="text-end text-muted">৳{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold text-success">৳{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light border-top-0">
                                <tr>
                                    <td colspan="4" class="text-end py-2 fw-bold text-muted text-uppercase small">Subtotal</td>
                                    <td class="text-end pe-4 py-2 fw-bold text-dark">৳{{ number_format($purchase->bill->sub_total ?? $purchase->items->sum('total_price'), 2) }}</td>
                                </tr>
                                @if(isset($purchase->bill) && $purchase->bill->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end py-2 fw-bold text-muted text-uppercase small">
                                        Discount {{ $purchase->bill->discount_type == 'percent' ? '('.$purchase->bill->discount_value.'%)' : '' }}
                                    </td>
                                    <td class="text-end pe-4 py-2 fw-bold text-danger">-৳{{ number_format($purchase->bill->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="text-end py-3 fw-bold text-muted text-uppercase small">Grand Total</td>
                                    <td class="text-end pe-4 py-3 fw-bold fs-4 text-primary">৳{{ number_format($purchase->bill->total_amount ?? $purchase->items->sum('total_price'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($purchase->notes)
            <div class="mt-4 p-4 bg-white rounded-4 shadow-sm border">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Internal Procurement Notes</h6>
                <p class="mb-0 text-dark opacity-75">{{ $purchase->notes }}</p>
            </div>
            @endif
        </div>

        <!-- REAL WORLD PRINT INVOICE SECTION (HIDDEN ON SCREEN) -->
        <div class="print-invoice-container">
            <div class="invoice-header">
                <div class="company-info">
                    @php $logo = $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/logo.png'); @endphp
                    <img src="{{ $logo }}" alt="Logo" style="height: 60px; margin-bottom: 5px;">
                    <h2>{{ $general_settings->site_title ?? 'Business ERP' }}</h2>
                    <p class="small text-muted mb-0">
                        {{ $general_settings->contact_address ?? 'Add your company address in settings' }}<br>
                        Phone: {{ $general_settings->contact_phone ?? '-' }} | Email: {{ $general_settings->contact_email ?? '-' }}
                    </p>
                </div>
                <div class="invoice-title-block">
                    <h1>PURCHASE INVOICE</h1>
                    <p class="fw-bold mb-0">ID: #{{ $purchase->id }}</p>
                    <p class="text-muted small">Date: {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="meta-summary">
                <div class="meta-col">
                    <h6>Supplier Information</h6>
                    <div class="meta-content">
                        <strong>{{ $purchase->supplier->name ?? 'Internal' }}</strong><br>
                        @if($purchase->supplier->address) {{ $purchase->supplier->address }} <br> @endif
                        Phone: {{ $purchase->supplier->mobile ?? $purchase->supplier->phone ?? '-' }}
                    </div>
                </div>
                <div class="meta-col">
                    <h6>Shipping / Receiving</h6>
                    <div class="meta-content">
                        <strong>{{ $purchase->location_name }}</strong><br>
                        Type: {{ ucfirst($purchase->ship_location_type) }}<br>
                        Reference: {{ $purchase->bill->bill_number ?? 'N/A' }}
                    </div>
                </div>
                <div class="meta-col">
                    <h6>Payment Status</h6>
                    <div class="meta-content">
                        Status: <strong>{{ strtoupper($purchase->status) }}</strong><br>
                        Generated By: {{ Auth::user()->name }}<br>
                        Print Date: {{ date('d/m/Y h:i A') }}
                    </div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th>Item Description</th>
                        <th class="text-center" style="width: 100px;">Qty</th>
                        <th class="text-end" style="width: 150px;">Price</th>
                        <th class="text-end" style="width: 150px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->variation) <br><small>Variation: {{ $item->variation->name }}</small> @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-item">
                        <span>Subtotal Value:</span>
                        <span>{{ number_format($purchase->bill->sub_total ?? $purchase->items->sum('total_price'), 2) }}</span>
                    </div>
                    @if(isset($purchase->bill) && $purchase->bill->discount_amount > 0)
                    <div class="total-item">
                        <span>Discount {{ $purchase->bill->discount_type == 'percent' ? '('.$purchase->bill->discount_value.'%)' : '' }}:</span>
                        <span>-{{ number_format($purchase->bill->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="total-item">
                        <span>Tax/VAT:</span>
                        <span>0.00</span>
                    </div>
                    <div class="total-item grand-total">
                        <span>Grand Total (৳):</span>
                        <span>{{ number_format($purchase->bill->total_amount ?? $purchase->items->sum('total_price'), 2) }}</span>
                    </div>
                </div>
            </div>

            @if($purchase->notes)
            <div style="margin-top: 30px;">
                <h6 style="font-weight: 800; font-size: 11px; text-transform: uppercase;">Purchase Memo:</h6>
                <p style="font-size: 12px; line-height: 1.4;">{{ $purchase->notes }}</p>
            </div>
            @endif

            <div class="signatures">
                <div class="sig-box">Supplier Signature</div>
                <div class="sig-box">Receiver Signature</div>
                <div class="sig-box">Authorized Signature</div>
            </div>

            <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
                Thank you for your business. This is a computer-generated invoice and does not require a physical stamp.
            </div>
        </div>
    </div>
@endsection