@extends('erp.master')

@section('title', 'Sale Return Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .detail-card {
                border-radius: 16px;
                border: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            }
            .info-label {
                font-size: 0.75rem;
                font-weight: 700;
                text-uppercase: uppercase;
                letter-spacing: 0.05em;
                color: #94a3b8;
                margin-bottom: 0.25rem;
            }
            .info-value {
                font-weight: 600;
                color: #1e293b;
                font-size: 1rem;
            }
            .status-badge {
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 700;
                font-size: 0.85rem;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .bg-warning-soft { background-color: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
            .bg-success-soft { background-color: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
            .bg-danger-soft { background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
            .bg-info-soft { background-color: #f0f9ff; color: #0284c7; border: 1px solid #e0f2fe; }
            
            .product-box {
                padding: 1.25rem;
                border-radius: 12px;
                background: #fff;
                border: 1px solid #e2e8f0;
                transition: all 0.2s;
            }
            .product-box:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
        </style>

        <!-- Header -->
        <div class="container-fluid px-4 py-4 bg-white border-bottom mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('saleReturn.list') }}" class="text-decoration-none">Sale Returns</a></li>
                            <li class="breadcrumb-item active">Return Details</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="fw-bold mb-0">Return #SR-{{ str_pad($saleReturn->id, 5, '0', STR_PAD_LEFT) }}</h2>
                        @php
                            $statusClass = 'bg-secondary-soft';
                            $statusIcon = 'fa-circle';
                            if($saleReturn->status === 'pending') { $statusClass = 'bg-warning-soft'; $statusIcon = 'fa-clock'; }
                            elseif($saleReturn->status === 'approved') { $statusClass = 'bg-success-soft'; $statusIcon = 'fa-check-circle'; }
                            elseif($saleReturn->status === 'rejected') { $statusClass = 'bg-danger-soft'; $statusIcon = 'fa-times-circle'; }
                            elseif($saleReturn->status === 'processed') { $statusClass = 'bg-info-soft'; $statusIcon = 'fa-sync'; }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            <i class="fas {{ $statusIcon }}"></i> {{ ucfirst($saleReturn->status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group gap-2">
                        <a href="{{ route('saleReturn.edit', $saleReturn->id) }}" class="btn btn-white border px-3 rounded-3">
                            <i class="fas fa-edit me-1 text-primary"></i> Edit
                        </a>
                        <a href="{{ route('saleReturn.list') }}" class="btn btn-primary px-4 rounded-3">
                            <i class="fas fa-list me-1"></i> Return List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4">
            <div class="row g-4">
                <!-- Main Information -->
                <div class="col-lg-12">
                    <div class="card detail-card mb-4">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <div class="info-label">Customer Name</div>
                                    <div class="info-value">{{ $saleReturn->customer->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $saleReturn->customer->phone ?? '' }}</small>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-label">Original POS Sale</div>
                                    <div class="info-value">
                                        @if($saleReturn->posSale)
                                            <a href="{{ route('pos.show', $saleReturn->pos_sale_id) }}" class="text-decoration-none">
                                                #POS-{{ $saleReturn->pos_sale_id }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-label">Return Date</div>
                                    <div class="info-value">{{ date('d M, Y', strtotime($saleReturn->return_date)) }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-label">Refund Type</div>
                                    <div class="info-value"><span class="badge bg-light text-dark fw-bold">{{ ucfirst($saleReturn->refund_type) }}</span></div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="info-label">Grand Total</div>
                                    <div class="info-value text-primary fs-4">৳{{ number_format($saleReturn->items->sum('total_price'), 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product List -->
                <div class="col-lg-8">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="fas fa-box-open me-2 text-primary"></i> Returned Items
                    </h5>
                    <div class="row g-3">
                        @foreach($saleReturn->items as $item)
                        <div class="col-12">
                            <div class="product-box">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-1">{{ $item->product->name ?? 'Deleted Product' }}</h6>
                                        @if($item->variation)
                                            <div class="small text-muted"><i class="fas fa-tags me-1"></i> {{ $item->variation->name }}</div>
                                        @endif
                                        <div class="small mt-1 text-primary fw-semibold">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="info-label">Quantity</div>
                                        <div class="info-value">{{ $item->returned_qty }}</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="info-label">Unit Price</div>
                                        <div class="info-value">৳{{ number_format($item->unit_price, 2) }}</div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="info-label">Row Total</div>
                                        <div class="info-value">৳{{ number_format($item->total_price, 2) }}</div>
                                    </div>
                                    @if($item->reason)
                                    <div class="col-12 mt-3 pt-3 border-top">
                                        <div class="info-label">Item Reason</div>
                                        <div class="text-muted small italic">"{{ $item->reason }}"</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Secondary Info -->
                <div class="col-lg-4">
                    <div class="card detail-card mb-4 overflow-hidden">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0">Location & Staff</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item p-3">
                                <div class="info-label">Restocked At</div>
                                <div class="info-value d-flex align-items-center gap-2">
                                    <i class="fas fa-warehouse text-muted small"></i>
                                    {{ ucfirst($saleReturn->return_to_type) }}:
                                    @if($saleReturn->return_to_type === 'branch')
                                        {{ $saleReturn->branch->name ?? 'N/A' }}
                                    @elseif($saleReturn->return_to_type === 'warehouse')
                                        {{ $saleReturn->warehouse->name ?? 'N/A' }}
                                    @elseif($saleReturn->return_to_type === 'employee')
                                        {{ $saleReturn->employee->user->first_name ?? '' }}
                                    @endif
                                </div>
                            </div>
                            @if($saleReturn->reason)
                            <div class="list-group-item p-3 bg-light">
                                <div class="info-label">Primary Reason</div>
                                <div class="info-value text-dark fw-normal">{{ $saleReturn->reason }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($saleReturn->notes)
                    <div class="card detail-card bg-primary-subtle border-0">
                        <div class="card-body">
                            <div class="info-label text-primary">Internal Notes</div>
                            <div class="info-value text-primary-emphasis fw-normal" style="white-space: pre-line;">{{ $saleReturn->notes }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection