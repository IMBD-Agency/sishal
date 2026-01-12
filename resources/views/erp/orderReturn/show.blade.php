@extends('erp.master')

@section('title', 'Order Return Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .info-label { font-size: 0.75rem; text-uppercase: uppercase; letter-spacing: 0.05em; color: #6c757d; font-weight: 700; margin-bottom: 0.25rem; }
            .info-value { font-size: 1rem; color: #212529; font-weight: 600; }
            .status-banner { border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
            .card { border-radius: 16px; border: none; }
            .table thead th { background: #f8f9fa; border-top: none; font-size: 0.75rem; text-uppercase: uppercase; color: #6c757d; padding: 1rem; }
            .table tbody td { padding: 1.25rem 1rem; border-bottom: 1px solid #f1f3f5; }
        </style>

        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('orderReturn.list') }}">Order Returns</a></li>
                            <li class="breadcrumb-item active">#OR-{{ str_pad($orderReturn->id, 5, '0', STR_PAD_LEFT) }}</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Return Details</h2>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('orderReturn.list') }}" class="btn btn-light border"><i class="fas fa-arrow-left me-2"></i>Back</a>
                        <a href="{{ route('orderReturn.edit', $orderReturn->id) }}" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Edit</a>
                        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Print</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @php
                $statusColors = [
                    'pending' => ['bg' => 'rgba(255, 193, 7, 0.1)', 'text' => '#856404', 'icon' => 'fa-clock'],
                    'approved' => ['bg' => 'rgba(40, 167, 69, 0.1)', 'text' => '#155724', 'icon' => 'fa-check-circle'],
                    'rejected' => ['bg' => 'rgba(220, 53, 69, 0.1)', 'text' => '#721c24', 'icon' => 'fa-times-circle'],
                    'processed' => ['bg' => 'rgba(0, 123, 255, 0.1)', 'text' => '#004085', 'icon' => 'fa-sync-alt']
                ];
                $color = $statusColors[$orderReturn->status] ?? ['bg' => '#f8f9fa', 'text' => '#333', 'icon' => 'fa-info-circle'];
            @endphp

            <div class="status-banner shadow-sm" style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['text'] }}20;">
                <div class="fs-2"><i class="fas {{ $color['icon'] }}"></i></div>
                <div>
                    <h5 class="fw-bold mb-1">Return Status: {{ ucfirst($orderReturn->status) }}</h5>
                    <p class="mb-0 opacity-75">Processed by: {{ $orderReturn->employee->user->first_name ?? 'System' }} on {{ $orderReturn->processed_at ? date('d M, Y', strtotime($orderReturn->processed_at)) : 'N/A' }}</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h5 class="fw-bold mb-0">Returned Products</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $total = 0; @endphp
                                        @foreach($orderReturn->items as $item)
                                            @php $subtotal = $item->returned_qty * $item->unit_price; $total += $subtotal; @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $item->product->name ?? 'N/A' }}</div>
                                                    @if($item->variation)
                                                        <small class="text-muted">Variation: {{ $item->variation->name }}</small>
                                                    @endif
                                                    <div class="text-muted small mt-1">Reason: {{ $item->reason ?? 'No reason provided' }}</div>
                                                </td>
                                                <td class="text-center fw-bold">{{ $item->returned_qty }}</td>
                                                <td class="text-end">৳ {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-end fw-bold">৳ {{ number_format($subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="3" class="text-end fw-bold py-3 fs-5">Estimated Refund Total:</td>
                                            <td class="text-end fw-bold py-3 fs-5 text-primary">৳ {{ number_format($total, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($orderReturn->notes)
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3 px-4 border-bottom">
                                <h5 class="fw-bold mb-0">Notes & Comments</h5>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted mb-0">{{ $orderReturn->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h5 class="fw-bold mb-0">Return Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <div class="info-label">Customer</div>
                                <div class="info-value">{{ $orderReturn->customer->name ?? 'Walk-in' }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="info-label">Return Date</div>
                                <div class="info-value">{{ date('d M, Y', strtotime($orderReturn->return_date)) }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="info-label">Refund Type</div>
                                <div class="info-value"><span class="badge bg-secondary px-3 py-2">{{ ucfirst($orderReturn->refund_type) }}</span></div>
                            </div>
                            <div class="mb-0">
                                <div class="info-label">Restocked To</div>
                                <div class="info-value">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    {{ $orderReturn->destination_name }}
                                    <small class="d-block text-muted">({{ ucfirst($orderReturn->return_to_type) }})</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($orderReturn->order)
                        <div class="card shadow-sm bg-primary text-white overflow-hidden">
                            <div class="card-body p-4 position-relative">
                                <i class="fas fa-shopping-bag position-absolute opacity-25" style="font-size: 5rem; right: -10px; bottom: -10px;"></i>
                                <h6 class="text-uppercase fw-bold opacity-75 small">Original Order</h6>
                                <h4 class="fw-bold mb-3">{{ $orderReturn->order->order_number }}</h4>
                                <a href="{{ route('order.show', $orderReturn->order_id) }}" class="btn btn-outline-light btn-sm rounded-pill px-3">View Order</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
