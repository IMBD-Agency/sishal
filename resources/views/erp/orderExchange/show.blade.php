@extends('erp.master')

@section('title', 'Exchange Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-0">Exchange Details #EXC-{{ str_pad($orderReturn->id, 5, '0', STR_PAD_LEFT) }}</h2>
                            <span class="badge bg-success">Completed</span>
                        </div>
                        <a href="{{ route('orderExchange.list') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>

                    <div class="row">
                        <!-- Left: Items -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h5 class="fw-bold mb-0 text-danger"><i class="fas fa-undo me-2"></i>Returned Products</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="bg-light text-muted small text-uppercase">
                                                <tr>
                                                    <th class="ps-4">Product Name</th>
                                                    <th>Brand</th>
                                                    <th>Category</th>
                                                    <th>Season</th>
                                                    <th>Size/Var</th>
                                                    <th>Unit Price</th>
                                                    <th>Qty</th>
                                                    <th class="pe-4 text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $returnTotal = 0; @endphp
                                                @foreach($orderReturn->items as $item)
                                                    @php $returnTotal += $item->total_price; @endphp
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="fw-bold">{{ $item->product->name ?? 'N/A' }}</div>
                                                            <small class="text-muted small">ID: #{{ $item->product->id ?? '-' }}</small>
                                                        </td>
                                                        <td><span class="badge bg-light text-dark border">{{ $item->product->brand->name ?? '-' }}</span></td>
                                                        <td class="small">{{ $item->product->category->name ?? '-' }}</td>
                                                        <td class="small">{{ $item->product->season->name ?? '-' }}</td>
                                                        <td><span class="badge bg-info-subtle text-info border-info">{{ $item->variation->name ?? 'Standard' }}</span></td>
                                                        <td>৳ {{ number_format($item->unit_price, 2) }}</td>
                                                        <td class="fw-bold">{{ $item->returned_qty }}</td>
                                                        <td class="pe-4 text-end fw-bold">৳ {{ number_format($item->total_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light border-top fw-bold">
                                                <tr>
                                                    <td colspan="7" class="ps-4 py-3">Total Return Credit:</td>
                                                    <td class="pe-4 py-3 text-end text-danger fs-5">৳ {{ number_format($returnTotal, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if($exchangeOrder)
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom p-4">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="fw-bold mb-0 text-success"><i class="fas fa-shopping-cart me-2"></i>New Products (Order #{{ $exchangeOrder->order_number }})</h5>
                                        <a href="{{ route('order.show', $exchangeOrder->id) }}" class="btn btn-sm btn-outline-primary">View Order</a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="bg-light text-muted small text-uppercase">
                                                <tr>
                                                    <th class="ps-4">Product Name</th>
                                                    <th>Brand</th>
                                                    <th>Category</th>
                                                    <th>Season</th>
                                                    <th>Size/Var</th>
                                                    <th>Unit Price</th>
                                                    <th>Qty</th>
                                                    <th class="pe-4 text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $newTotal = 0; @endphp
                                                @foreach($exchangeOrder->items as $item)
                                                    @php $newTotal += $item->total_price; @endphp
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="fw-bold">{{ $item->product->name ?? 'N/A' }}</div>
                                                            <small class="text-muted small">ID: #{{ $item->product->id ?? '-' }}</small>
                                                        </td>
                                                        <td><span class="badge bg-light text-dark border">{{ $item->product->brand->name ?? '-' }}</span></td>
                                                        <td class="small">{{ $item->product->category->name ?? '-' }}</td>
                                                        <td class="small">{{ $item->product->season->name ?? '-' }}</td>
                                                        <td><span class="badge bg-success-subtle text-success border-success">{{ $item->variation->name ?? 'Standard' }}</span></td>
                                                        <td>৳ {{ number_format($item->unit_price, 2) }}</td>
                                                        <td class="fw-bold">{{ $item->quantity }}</td>
                                                        <td class="pe-4 text-end fw-bold">৳ {{ number_format($item->total_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light border-top fw-bold">
                                                <tr>
                                                    <td colspan="7" class="ps-4 py-3">New Order Total:</td>
                                                    <td class="pe-4 py-3 text-end fs-5">৳ {{ number_format($newTotal, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning border-warning border-opacity-25 bg-warning bg-opacity-10 text-warning d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                <div>Linked Exchange Order not found or yet to be created.</div>
                            </div>
                            @endif
                        </div>

                        <!-- Right: Summary -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom p-4">
                                    <h5 class="fw-bold mb-0">Exchange Summary</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-4 text-center p-4 bg-light rounded-3 border">
                                        <div class="text-muted small text-uppercase mb-1">Financial Adjustment</div>
                                        @php $net = ($newTotal ?? 0) - ($returnTotal ?? 0); @endphp
                                        <h2 class="fw-bold {{ $net > 0 ? 'text-primary' : 'text-success' }}">
                                            ৳ {{ number_format(abs($net), 2) }}
                                        </h2>
                                        <div class="small fw-bold">
                                            @if($net > 0)
                                                <span class="text-danger">Customer needs to pay</span>
                                            @elseif($net < 0)
                                                <span class="text-success">Refund/Credit to Customer</span>
                                            @else
                                                <span class="text-muted">Even Exchange</span>
                                            @endif
                                        </div>
                                    </div>

                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <span class="text-muted">Customer</span>
                                            <span class="fw-bold text-end">{{ $orderReturn->customer->name ?? 'N/A' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <span class="text-muted">Return Credit</span>
                                            <span class="fw-bold text-danger text-end">৳ {{ number_format($returnTotal, 2) }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <span class="text-muted">New Purchase</span>
                                            <span class="fw-bold text-success text-end">৳ {{ number_format($newTotal ?? 0, 2) }}</span>
                                        </li>
                                        @if($exchangeOrder)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <span class="text-muted">Discount on Exch.</span>
                                            <span class="fw-bold text-end">৳ {{ number_format($exchangeOrder->discount, 2) }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-top mt-1 pt-2">
                                            <span class="text-muted">Paid Amount</span>
                                            <span class="fw-bold text-success text-end">৳ {{ number_format($exchangeOrder->invoice->paid_amount ?? 0, 2) }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <span class="fw-bold">Due Balance</span>
                                            <span class="fw-bold text-danger text-end fs-6">৳ {{ number_format($exchangeOrder->invoice->due_amount ?? 0, 2) }}</span>
                                        </li>
                                        @endif
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-top mt-2">
                                            <span class="text-muted">Exch. Date</span>
                                            <span class="fw-bold text-end">{{ $orderReturn->return_date }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <span class="text-muted">Restocked To</span>
                                            <span class="fw-bold text-end text-capitalize">{{ $orderReturn->return_to_type }} ({{ $orderReturn->return_to_id }})</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
