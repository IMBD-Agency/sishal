@extends('erp.master')

@section('title', 'Requisition Details - ' . $requisition->requisition_number)

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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('requisition.index') }}" class="text-decoration-none text-muted">Requisitions</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">{{ $requisition->requisition_number }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-info text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Requisition Details</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
                    <a href="{{ route('requisition.index') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                    @if(in_array($requisition->status, ['pending', 'partially_fulfilled']))
                        @can('process requisitions')
                        <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#fulfillModal">
                            <i class="fas fa-check-double me-2"></i>PROCESS REQUEST
                        </button>
                        @endcan
                    @endif
                    @if($requisition->status === 'pending')
                        <a href="{{ route('requisition.edit', $requisition->id) }}" class="btn btn-warning fw-bold shadow-sm">
                            <i class="fas fa-edit me-2"></i>EDIT
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row g-4">
                <!-- Info Column -->
                <div class="col-lg-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small">Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4 text-center">
                                <div class="badge bg-light text-primary border px-4 py-3 rounded-pill fw-bold mb-2" style="font-size: 1.1rem;">
                                    {{ $requisition->requisition_number }}
                                </div>
                                <div class="small text-muted">Generated on {{ \Carbon\Carbon::parse($requisition->requisition_date)->format('M d, Y') }}</div>
                            </div>

                            <hr class="opacity-10 mb-4">

                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Status:</span>
                                    @php
                                        $statusClass = [
                                            'pending' => 'bg-warning text-dark',
                                            'partially_fulfilled' => 'bg-info text-white',
                                            'fulfilled' => 'bg-success text-white',
                                            'rejected' => 'bg-danger text-white',
                                        ][$requisition->status] ?? 'bg-secondary text-white';
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-1 rounded-pill">{{ strtoupper($requisition->status) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Requesting Branch:</span>
                                    <span class="fw-bold">{{ $requisition->branch->name }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Source Warehouse:</span>
                                    <span class="fw-bold text-info">{{ $requisition->warehouse->name }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Requested By:</span>
                                    <span class="fw-bold">{{ $requisition->creator->name }}</span>
                                </div>
                            </div>

                            @if($requisition->notes)
                                <div class="mt-4 p-3 bg-light rounded-3 border">
                                    <div class="small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-sticky-note me-1"></i> Notes</div>
                                    <div class="small text-dark">{{ $requisition->notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Items Column -->
                <div class="col-lg-8">
                    <div class="premium-card">
                        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small">Requested Items List</h6>
                            <span class="badge bg-primary px-3 py-2">{{ count($requisition->items) }} Products</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table premium-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Media</th>
                                            <th>Product Details</th>
                                            <th>Style No</th>
                                            <th>Variant</th>
                                            <th class="text-center">Requested</th>
                                            <th class="text-center">Fulfilled</th>
                                            <th class="text-center pe-4">Stock Avail.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($requisition->items as $item)
                                            @php
                                                // Check warehouse stock
                                                $stock = 0;
                                                if ($item->variation_id) {
                                                    $vs = \App\Models\ProductVariationStock::where('variation_id', $item->variation_id)
                                                        ->where('warehouse_id', $requisition->warehouse_id)
                                                        ->first();
                                                    $stock = $vs ? $vs->quantity : 0;
                                                } else {
                                                    $ws = \App\Models\WarehouseProductStock::where('product_id', $item->product_id)
                                                        ->where('warehouse_id', $requisition->warehouse_id)
                                                        ->first();
                                                    $stock = $ws ? $ws->quantity : 0;
                                                }

                                                $img = null;
                                                if ($item->variation && $item->variation->image) {
                                                    $img = $item->variation->image;
                                                } elseif ($item->product && $item->product->image) {
                                                    $img = $item->product->image;
                                                }
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    @if($img)
                                                        <img src="/{{ $img }}" class="rounded border" style="width: 35px; height: 35px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;"><i class="fas fa-image text-muted opacity-50"></i></div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ optional($item->product)->name ?? '—' }}</div>
                                                    <div class="extra-small text-muted">{{ optional(optional($item->product)->category)->name ?? 'General' }}</div>
                                                </td>
                                                <td class="text-pink fw-bold">{{ optional($item->product)->style_number ?? optional($item->product)->sku ?? '—' }}</td>
                                                <td>
                                                    @if($item->variation)
                                                        @php
                                                            $comboParts = [];
                                                            if ($item->variation->combinations && $item->variation->combinations->count()) {
                                                                foreach ($item->variation->combinations as $combo) {
                                                                    $val = $combo->attributeValue->value ?? null;
                                                                    if ($val) $comboParts[] = $val;
                                                                }
                                                            }
                                                            // Fall back to variation name if combinations are empty
                                                            $varLabel = implode(' / ', $comboParts) ?: ($item->variation->name ?: '-');
                                                        @endphp
                                                        <span class="badge bg-light text-dark border px-2">{{ $varLabel }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold text-primary">
                                                    {{ number_format($item->quantity, 0) == $item->quantity ? number_format($item->quantity, 0) : $item->quantity }}
                                                </td>
                                                <td class="text-center fw-bold text-success">{{ $item->fulfilled_quantity }}</td>
                                                <td class="text-center pe-4">
                                                    <span class="badge {{ $stock >= ($item->quantity - $item->fulfilled_quantity) ? 'bg-success' : 'bg-danger' }} px-3">
                                                        {{ $stock }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fulfillment Modal -->
    <div class="modal fade" id="fulfillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-tasks me-2"></i>Process Requisition Fulfillment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
                        <i class="fas fa-info-circle fa-2x"></i>
                        <div>
                            <strong>How it works:</strong> If warehouse stock is available → select <strong>Stock Transfer</strong>.
                            If stock is <strong>0</strong>, skip for now and purchase from your supplier manually, then transfer once stock arrives.
                        </div>
                    </div>

                    <form action="{{ route('requisition.fulfill', $requisition->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Pending Qty</th>
                                        <th class="text-center">Warehouse Stock</th>
                                        <th style="width: 150px;">Fulfill Via</th>
                                        <th style="width: 120px;">Qty to Process</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requisition->items as $item)
                                        @if($item->quantity > $item->fulfilled_quantity)
                                            @php
                                                $pending = $item->quantity - $item->fulfilled_quantity;
                                                $stock = 0;
                                                if ($item->variation_id) {
                                                    $vs = \App\Models\ProductVariationStock::where('variation_id', $item->variation_id)->where('warehouse_id', $requisition->warehouse_id)->first();
                                                    $stock = $vs ? $vs->quantity : 0;
                                                } else {
                                                    $ws = \App\Models\WarehouseProductStock::where('product_id', $item->product_id)->where('warehouse_id', $requisition->warehouse_id)->first();
                                                    $stock = $ws ? $ws->quantity : 0;
                                                }
                                                $suggestTransfer = $stock > 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ optional($item->product)->name ?? '—' }}</div>
                                                    <div class="small text-muted">
                                                        @if($item->variation)
                                                            @php
                                                                $comboParts = [];
                                                                if ($item->variation->combinations && $item->variation->combinations->count()) {
                                                                    foreach ($item->variation->combinations as $combo) {
                                                                        $val = $combo->attributeValue->value ?? null;
                                                                        if ($val) $comboParts[] = $val;
                                                                    }
                                                                }
                                                                $varLabel = implode(' / ', $comboParts) ?: ($item->variation->name ?: '—');
                                                            @endphp
                                                            <span class="fw-semibold text-secondary">{{ $varLabel }}</span>
                                                        @else
                                                            <span class="text-muted">Standard</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $pending }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $stock > 0 ? 'bg-success' : 'bg-danger' }}">{{ $stock }}</span>
                                                </td>
                                                <td>
                                                    @if($stock > 0)
                                                        <select name="items[{{ $item->id }}][type]" class="form-select form-select-sm shadow-none">
                                                            <option value="transfer" selected>Stock Transfer</option>
                                                            <option value="skip">Skip</option>
                                                        </select>
                                                    @else
                                                        <select name="items[{{ $item->id }}][type]" class="form-select form-select-sm shadow-none border-danger text-danger">
                                                            <option value="skip" selected>Skip</option>
                                                            <option value="transfer">Stock Transfer</option>
                                                        </select>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($stock > 0)
                                                        <input type="number" name="items[{{ $item->id }}][qty]" class="form-control form-control-sm" value="{{ (int) min($pending, $stock) }}" max="{{ (int) $pending }}" min="1" step="1">
                                                    @else
                                                        <div class="d-flex align-items-center gap-1">
                                                            <input type="number" name="items[{{ $item->id }}][qty]" class="form-control form-control-sm" value="0" max="{{ (int) $pending }}" min="0" step="1" disabled>
                                                            <span class="badge bg-danger text-white" style="font-size:0.65rem; white-space:nowrap;" title="Purchase manually from supplier">
                                                                <i class="fas fa-shopping-cart me-1"></i>Buy Manually
                                                            </span>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">CLOSE</button>
                            <button type="submit" class="btn btn-primary fw-bold px-5 ms-2">INITIALIZE FULFILLMENT</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
