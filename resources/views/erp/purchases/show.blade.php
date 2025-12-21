@extends('erp.master')

@section('title', 'Purchase Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row mb-4">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('purchase.list') }}" class="text-decoration-none">Purchases</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Purchase #{{ $purchase->id }}</li>
                        </ol>
                    </nav>
                    <h3>Purchase #{{ $purchase->id }}</h3>
                    <div class="mb-2">
                        <span class="me-2">
                            <strong>Date:</strong> {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') : '-' }}
                        </span>
                        <span class="badge 
                            @if($purchase->status == 'pending') bg-warning text-dark
                            @elseif($purchase->status == 'received') bg-success
                            @elseif($purchase->status == 'cancelled') bg-danger
                            @else bg-secondary
                            @endif
                        ">
                            {{ ucfirst($purchase->status ?? '-') }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <a href="{{ route('purchase.list') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                        <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Purchase Info</div>
                        <div class="card-body">
                            <p><strong>Purchase ID:</strong> #{{ $purchase->id }}</p>
                            @if($purchase->supplier)
                                <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
                            @endif
                            <p><strong>Location:</strong> {{ $purchase->location_name }} ({{ ucfirst($purchase->ship_location_type) }})</p>
                            <p><strong>Notes:</strong> {{ $purchase->notes ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Bill Info</div>
                        <div class="card-body">
                            @if($purchase->bill)
                                <p><strong>Bill Number:</strong> {{ $purchase->bill->bill_number ?? 'N/A' }}</p>
                                <p><strong>Bill Date:</strong> {{ $purchase->bill->bill_date ? \Carbon\Carbon::parse($purchase->bill->bill_date)->format('d-m-Y') : 'N/A' }}</p>
                                <p><strong>Total Amount:</strong> {{ number_format($purchase->bill->total_amount, 2) }}৳</p>
                                <p><strong>Paid Amount:</strong> {{ number_format($purchase->bill->paid_amount, 2) }}৳</p>
                                <p><strong>Due Amount:</strong> {{ number_format($purchase->bill->due_amount, 2) }}৳</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-{{ $purchase->bill->status == 'paid' ? 'success' : ($purchase->bill->status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($purchase->bill->status) }}
                                    </span>
                                </p>
                                @if($purchase->bill->description)
                                    <p><strong>Description:</strong> {{ $purchase->bill->description }}</p>
                                @endif
                            @else
                                <p>No bill information available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Items</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchase->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            {{ $item->product->name ?? '-' }}
                                            @if($item->variation_id && $item->variation)
                                                <br><small class="text-muted">Variation: {{ $item->variation->name ?? 'Variation #' . $item->variation_id }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }}৳</td>
                                        <td>{{ number_format($item->discount ?? 0, 2) }}৳</td>
                                        <td>{{ number_format($item->total_price, 2) }}৳</td>
                                        <td>{{ $item->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold">{{ number_format($purchase->items->sum('total_price'), 2) }}৳</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection