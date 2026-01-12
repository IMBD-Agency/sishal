@extends('erp.master')

@section('title', 'Purchase Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .bg-primary-soft { background-color: rgba(13, 110, 253, 0.05); }
            .bg-success-soft { background-color: rgba(25, 135, 84, 0.05); }
            .bg-warning-soft { background-color: rgba(255, 193, 7, 0.05); }
            .bg-danger-soft { background-color: rgba(220, 53, 69, 0.05); }
            
            .info-card { border-radius: 1rem; border: none; overflow: hidden; height: 100%; transition: transform 0.2s; }
            .info-card:hover { transform: translateY(-2px); }
            .info-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #adb5bd; margin-bottom: 0.25rem; }
            .info-value { font-size: 1rem; font-weight: 600; color: #212529; }
            
            .item-table th { background-color: #f8f9fa; font-weight: 600; color: #6c757d; font-size: 0.85rem; padding: 1rem; border: none; }
            .item-table td { padding: 1rem; vertical-align: middle; border-color: #f1f3f5; }
        </style>

        <!-- Header -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom mb-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('purchase.list') }}" class="text-decoration-none">Purchase</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Details</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="fw-bold mb-0">Purchase #{{ $purchase->id }}</h2>
                        @php
                            $statusMap = [
                                'pending' => ['class' => 'bg-warning-soft text-warning border-warning', 'icon' => 'fa-clock'],
                                'received' => ['class' => 'bg-success-soft text-success border-success', 'icon' => 'fa-check-circle'],
                                'cancelled' => ['class' => 'bg-danger-soft text-danger border-danger', 'icon' => 'fa-times-circle'],
                            ];
                            $s = $statusMap[$purchase->status] ?? ['class' => 'bg-secondary-soft text-secondary border-secondary', 'icon' => 'fa-question-circle'];
                        @endphp
                        <span class="badge border {{ $s['class'] }} px-3 py-2 rounded-pill fw-medium">
                            <i class="fas {{ $s['icon'] }} me-1"></i> {{ ucfirst($purchase->status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <button onclick="window.print()" class="btn btn-light border px-4 rounded-pill">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="fas fa-edit me-2"></i>Edit Purchase
                        </a>
                        <a href="{{ route('purchase.list') }}" class="btn btn-light border px-3 rounded-pill">
                            <i class="fas fa-list me-2"></i>List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 pb-5">
            <div class="row g-4 mb-4">
                <!-- Summary Column -->
                <div class="col-md-4">
                    <div class="card info-card shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 border-bottom pb-3"><i class="fas fa-info-circle me-2 text-primary"></i>General Information</h6>
                            <div class="mb-3">
                                <div class="info-label">Purchase ID</div>
                                <div class="info-value">#{{ $purchase->id }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Purchase Date</div>
                                <div class="info-value text-primary">
                                    <i class="far fa-calendar-alt me-1"></i> 
                                    {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') : '-' }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Created By</div>
                                <div class="info-value">Admin User</div> {{-- Ideally fetch from $purchase->user->name --}}
                            </div>
                            <div>
                                <div class="info-label">Last Updated</div>
                                <div class="info-value x-small text-muted">{{ $purchase->updated_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Source/Destination Column -->
                <div class="col-md-4">
                    <div class="card info-card shadow-sm border-start border-4 border-primary">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 border-bottom pb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Destination Information</h6>
                            <div class="mb-3">
                                <div class="info-label">Location Name</div>
                                <div class="info-value fs-5">{{ $purchase->location_name }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Location Type</div>
                                <div class="info-value">
                                    <span class="badge bg-light text-dark px-2">{{ ucfirst($purchase->ship_location_type) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="info-label">Delivery Notes</div>
                                <div class="info-value small text-muted fst-italic">{{ $purchase->notes ?? 'No special notes provided.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="col-md-4">
                    <div class="card info-card shadow-sm bg-primary-soft overflow-hidden">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 border-bottom border-white pb-3"><i class="fas fa-calculator me-2 text-primary"></i>Inventory Value</h6>
                            <div class="d-flex justify-content-between mb-3 border-bottom border-white pb-3">
                                <div>
                                    <div class="info-label">Total Quantity</div>
                                    <div class="info-value fs-4">{{ $purchase->items->sum('quantity') }} Items</div>
                                </div>
                                <div class="text-end">
                                    <div class="info-label">Grand Total</div>
                                    <div class="info-value text-primary fs-3">৳{{ number_format($purchase->items->sum('total_price'), 2) }}</div>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-coins me-1"></i> Re-calculated based on current line items.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-list-ul me-2 text-muted"></i>Purchase Manifest</h5>
                    <span class="badge bg-light text-dark">{{ count($purchase->items) }} Line Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 item-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product Name & Specifications</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th class="text-end pe-4">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $item->product->name ?? 'Unknown Product' }}</div>
                                            @if($item->variation_id && $item->variation)
                                                <div class="x-small text-muted">
                                                    <i class="fas fa-tag me-1"></i> {{ $item->variation->name ?? 'Var #'.$item->variation_id }}
                                                </div>
                                            @endif
                                            @if($item->description)
                                                <div class="x-small text-secondary mt-1 opacity-75">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $item->quantity }}</td>
                                        <td class="text-muted">৳{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold text-primary">৳{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end py-3 fw-bold text-muted text-uppercase small">Manifest Total</td>
                                    <td class="text-end pe-4 py-3 fw-bold fs-5 text-dark">৳{{ number_format($purchase->items->sum('total_price'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection