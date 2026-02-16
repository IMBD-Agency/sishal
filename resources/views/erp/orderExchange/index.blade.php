@extends('erp.master')

@section('title', 'Order Exchanges')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-0">Order Exchanges</h2>
                            <p class="text-muted">History of e-commerce product replacements.</p>
                        </div>
                        <div class="btn-group shadow-sm">
                            <a href="{{ route('orderExchange.export', request()->all()) }}" class="btn btn-outline-success rounded-start-pill px-4">
                                <i class="fas fa-file-excel me-1"></i>Export Excel
                            </a>
                            <a href="{{ route('orderExchange.create') }}" class="btn btn-success rounded-end-pill px-4">
                                <i class="fas fa-plus me-1"></i>New Exchange
                            </a>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filters</h6>
                        </div>
                        <div class="card-body pt-0">
                            <form method="GET" action="{{ route('orderExchange.list') }}" class="row g-3">
                                <!-- Row 1 -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Search</label>
                                    <input type="text" name="search" class="form-control bg-light border-0" placeholder="Ref, Customer, Phone..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Customer</label>
                                    <select name="customer_id" class="form-select bg-light border-0 select2">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Date Range</label>
                                    <div class="input-group border-0">
                                        <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ request('start_date') }}">
                                        <span class="input-group-text bg-light border-0 small">to</span>
                                        <input type="date" name="end_date" class="form-control bg-light border-0" value="{{ request('end_date') }}">
                                    </div>
                                </div>

                                <!-- Row 2 -->
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Brand</label>
                                    <select name="brand_id" class="form-select bg-light border-0 select2">
                                        <option value="">All Brands</option>
                                        @foreach($brands as $b)
                                            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Category</label>
                                    <select name="category_id" class="form-select bg-light border-0 select2">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Season</label>
                                    <select name="season_id" class="form-select bg-light border-0 select2">
                                        <option value="">All Seasons</option>
                                        @foreach($seasons as $s)
                                            <option value="{{ $s->id }}" {{ request('season_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 text-end align-self-end">
                                    <a href="{{ route('orderExchange.list') }}" class="btn btn-light border shadow-sm me-1" title="Reset Filters">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                        <i class="fas fa-filter me-1"></i> Apply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase" style="font-size: 0.7rem;">
                                        <tr>
                                            <th class="ps-4">Ref</th>
                                            <th>Product Details</th>
                                            <th>Brand/Category</th>
                                            <th>Season</th>
                                            <th>Size</th>
                                            <th class="text-center">Qty</th>
                                            <th>Credit</th>
                                            <th>Exch. Order</th>
                                            <th>Discount</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                            <th class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($exchanges as $exchange)
                                            @php
                                                $newOrder = $exchange->exchangeOrder;
                                            @endphp
                                            @foreach($exchange->items as $index => $item)
                                            <tr style="font-size: 0.8rem;">
                                                @if($index === 0)
                                                <td class="ps-4" rowspan="{{ $exchange->items->count() }}">
                                                    <a href="{{ route('orderExchange.show', $exchange->id) }}" class="fw-bold text-decoration-none">
                                                        #EXC-{{ str_pad($exchange->id, 5, '0', STR_PAD_LEFT) }}
                                                    </a>
                                                    <div class="small text-muted" style="font-size: 0.65rem;">{{ $exchange->created_at->format('d/m/y') }}</div>
                                                </td>
                                                @endif
                                                
                                                <td>
                                                    <div class="fw-bold text-truncate" style="max-width: 130px;" title="{{ $item->product->name ?? 'N/A' }}">
                                                        {{ $item->product->name ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $exchange->customer->name ?? 'Walk-in' }}</div>
                                                </td>
                                                <td>
                                                    <div class="badge bg-light text-dark border p-1" style="font-size: 0.65rem;">{{ $item->product->brand->name ?? '-' }}</div>
                                                    <div class="small text-muted d-block text-truncate" style="max-width: 80px;">{{ $item->product->category->name ?? '-' }}</div>
                                                </td>
                                                <td class="small">{{ $item->product->season->name ?? '-' }}</td>
                                                <td><span class="badge bg-info-subtle text-info">{{ $item->variation->name ?? 'Std' }}</span></td>
                                                <td class="text-center fw-bold">{{ $item->returned_qty }}</td>
                                                <td class="fw-bold">৳{{ number_format($item->total_price, 2) }}</td>

                                                @if($index === 0)
                                                <td rowspan="{{ $exchange->items->count() }}">
                                                    @if($newOrder)
                                                        <a href="{{ route('order.show', $newOrder->id) }}" class="badge bg-success bg-opacity-10 text-success text-decoration-none border border-success border-opacity-25 py-1">
                                                            {{ $newOrder->order_number }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                                <td rowspan="{{ $exchange->items->count() }}" class="text-muted">
                                                    ৳{{ number_format($newOrder->discount ?? 0, 2) }}
                                                </td>
                                                <td rowspan="{{ $exchange->items->count() }}" class="text-success fw-bold">
                                                    ৳{{ number_format($newOrder->invoice->paid_amount ?? 0, 2) }}
                                                </td>
                                                <td rowspan="{{ $exchange->items->count() }}" class="text-danger fw-bold">
                                                    ৳{{ number_format($newOrder->invoice->due_amount ?? 0, 2) }}
                                                </td>
                                                <td rowspan="{{ $exchange->items->count() }}" class="text-end pe-4">
                                                    <a href="{{ route('orderExchange.show', $exchange->id) }}" class="btn btn-sm btn-outline-primary border shadow-sm px-2">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center py-5 text-muted">No exchanges found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $exchanges->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
