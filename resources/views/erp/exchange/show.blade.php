@extends('erp.master')

@section('title', 'Exchange Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4 position-relative">
            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="fas fa-exchange-alt text-white"></i>
                        </div>
                        <div>
                            <h1 class="h3 fw-bold mb-1 text-dark">Exchange Details</h1>
                            <p class="text-muted mb-0">Exchange #{{ $exchange->exchange_number }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a href="{{ route('exchange.print', $exchange->id) }}" target="_blank"
                           class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </a>
                        <a href="{{ route('exchange.list') }}" class="btn btn-outline-primary px-4 py-2 rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 d-flex flex-column gap-4">
                    <!-- Summary Card -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Summary</h5>
                            <div class="bg-light rounded-3 p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Exchange Date</span>
                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($exchange->exchange_date)->format('d M Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Original Sale</span>
                                    <a href="{{ route('pos.show', $exchange->original_pos_id) }}" class="fw-bold text-decoration-none">{{ $exchange->originalPos->sale_number ?? '-' }}</a>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Customer</span>
                                    <span class="fw-bold">{{ $exchange->customer->name ?? 'Walk-in' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Branch</span>
                                    <span class="fw-bold">{{ $exchange->branch->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Processed By</span>
                                    <span class="fw-bold">{{ $exchange->employee->user->first_name ?? '' }} {{ $exchange->employee->user->last_name ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financials -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3"><i class="fas fa-wallet text-success me-2"></i>Financials</h5>
                            <div class="bg-light rounded-3 p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Returned Value</span>
                                    <span class="text-danger">{{ number_format($exchange->total_return_amount, 2) }}৳</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total New Value</span>
                                    <span class="text-success">{{ number_format($exchange->total_new_amount, 2) }}৳</span>
                                </div>
                                @if($exchange->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Discount</span>
                                    <span class="text-danger">-{{ number_format($exchange->discount_amount, 2) }}৳</span>
                                </div>
                                @endif
                                @if($exchange->delivery_charge > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Delivery</span>
                                    <span class="text-dark">{{ number_format($exchange->delivery_charge, 2) }}৳</span>
                                </div>
                                @endif
                                <hr>
                                @if($exchange->extra_payable > 0)
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="fw-bold text-dark">Extra Paid by Customer</span>
                                    <span class="fw-bold fs-5 text-success">{{ number_format($exchange->extra_payable, 2) }}৳</span>
                                </div>
                                @elseif($exchange->refund_amount > 0)
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="fw-bold text-dark">Refunded to Customer</span>
                                    <span class="fw-bold fs-5 text-danger">{{ number_format($exchange->refund_amount, 2) }}৳</span>
                                </div>
                                @else
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="fw-bold text-dark">Balance</span>
                                    <span class="fw-bold fs-5 text-muted">0.00৳</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Returned Items -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3 text-danger"><i class="fas fa-undo me-2"></i>Returned Items</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($exchange->items->where('type', 'returned') as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $item->product->name ?? '-' }}</div>
                                                @if($item->variation)
                                                    <small class="text-muted">{{ $item->variation->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format($item->unit_price, 2) }}৳</td>
                                            <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}৳</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- New Items -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3 text-success"><i class="fas fa-plus-circle me-2"></i>New Items Given</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($exchange->items->where('type', 'new') as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $item->product->name ?? '-' }}</div>
                                                @if($item->variation)
                                                    <small class="text-muted">{{ $item->variation->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format($item->unit_price, 2) }}৳</td>
                                            <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}৳</td>
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
@endsection
