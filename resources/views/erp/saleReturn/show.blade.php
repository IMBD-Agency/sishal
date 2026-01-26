@extends('erp.master')

@section('title', 'Sale Return Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('saleReturn.list') }}" class="text-decoration-none text-muted">Returns</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Details</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <h4 class="fw-bold mb-0 text-dark text-nowrap">Return #SR-{{ str_pad($saleReturn->id, 5, '0', STR_PAD_LEFT) }}</h4>
                        @php
                            $statusClass = 'badge bg-secondary';
                            if($saleReturn->status === 'pending') $statusClass = 'badge bg-warning';
                            elseif($saleReturn->status === 'approved') $statusClass = 'badge bg-success';
                            elseif($saleReturn->status === 'rejected') $statusClass = 'badge bg-danger';
                            elseif($saleReturn->status === 'processed') $statusClass = 'badge bg-info';
                        @endphp
                        <span class="{{ $statusClass }} py-2 px-3">{{ ucfirst($saleReturn->status) }}</span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('saleReturn.edit', $saleReturn->id) }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <a href="{{ route('saleReturn.list') }}" class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-list me-2"></i>Return List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4">
            <div class="row g-4">
                <!-- Main Information -->
                <div class="col-lg-12">
                    <div class="premium-card mb-4">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <div class="form-label small fw-bold text-muted text-uppercase">Customer Name</div>
                                    <div class="fw-bold text-dark fs-5">{{ $saleReturn->customer->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $saleReturn->customer->phone ?? '' }}</small>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-label small fw-bold text-muted text-uppercase">Original POS Sale</div>
                                    <div class="fw-bold fs-5">
                                        @if($saleReturn->posSale)
                                            <a href="{{ route('pos.show', $saleReturn->pos_sale_id) }}" class="text-decoration-none text-primary">
                                                #{{ $saleReturn->posSale->sale_number }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-label small fw-bold text-muted text-uppercase">Return Date</div>
                                    <div class="fw-bold text-dark fs-5">{{ date('d M, Y', strtotime($saleReturn->return_date)) }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-label small fw-bold text-muted text-uppercase">Refund Type</div>
                                    <div><span class="badge bg-light text-dark fw-bold">{{ ucfirst($saleReturn->refund_type) }}</span></div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="form-label small fw-bold text-muted text-uppercase text-end">Grand Total</div>
                                    <div class="fw-bold text-success fs-3">৳{{ number_format($saleReturn->items->sum('total_price'), 2) }}</div>
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
                                        <div class="small mt-1 text-primary fw-semibold">Style No: {{ $item->product->sku ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-2 text-center text-md-start">
                                        <div class="form-label small fw-bold text-muted text-uppercase">Quantity</div>
                                        <div class="fw-bold text-dark fs-5">{{ $item->returned_qty }}</div>
                                    </div>
                                    <div class="col-md-2 text-center text-md-start">
                                        <div class="form-label small fw-bold text-muted text-uppercase">Unit Price</div>
                                        <div class="fw-bold text-dark fs-5">৳{{ number_format($item->unit_price, 2) }}</div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="form-label small fw-bold text-muted text-uppercase text-end">Row Total</div>
                                        <div class="fw-bold text-primary fs-5">৳{{ number_format($item->total_price, 2) }}</div>
                                    </div>
                                    @if($item->reason)
                                    <div class="col-12 mt-3 pt-3 border-top">
                                        <div class="form-label small fw-bold text-muted text-uppercase">Item Reason</div>
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
                    <div class="premium-card mb-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-uppercase text-muted small">Location & Staff</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item p-3">
                                <div class="form-label small fw-bold text-muted text-uppercase">Restocked At</div>
                                <div class="fw-bold text-dark fs-5 d-flex align-items-center gap-2">
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
                                <div class="form-label small fw-bold text-muted text-uppercase text-end">Primary Reason</div>
                                <div class="text-dark fw-normal">{{ $saleReturn->reason }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($saleReturn->notes)
                    <div class="premium-card bg-primary-subtle border-0">
                        <div class="card-body">
                            <div class="form-label small fw-bold text-primary text-uppercase">Internal Notes</div>
                            <div class="text-primary-emphasis fw-normal" style="white-space: pre-line;">{{ $saleReturn->notes }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection