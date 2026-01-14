@extends('erp.master')

@section('title', 'Stock Transfer Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h4 class="mb-0">Stock Transfer Details</h4>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Product</dt>
                                <dd class="col-sm-8">
                                    {{ $transfer->product->name ?? '-' }}
                                    @if($transfer->variation_id && $transfer->variation)
                                        <br><small class="text-muted">Variation: {{ $transfer->variation->name ?? 'Variation #' . $transfer->variation_id }}</small>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Style No</dt>
                                <dd class="col-sm-8">
                                    @if($transfer->variation_id && $transfer->variation && $transfer->variation->sku)
                                        {{ $transfer->variation->sku }}
                                    @else
                                        {{ $transfer->product->sku ?? '-' }}
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Category</dt>
                                <dd class="col-sm-8">{{ $transfer->product->category->name ?? '-' }}</dd>

                                <dt class="col-sm-4">From</dt>
                                <dd class="col-sm-8">
                                    @if($transfer->from_type === 'branch')
                                        Branch: {{ $transfer->fromBranch->name ?? '-' }}
                                    @elseif($transfer->from_type === 'warehouse')
                                        Warehouse: {{ $transfer->fromWarehouse->name ?? '-' }}
                                    @else
                                        {{ ucfirst($transfer->from_type) }}
                                    @endif
                                </dd>

                                <dt class="col-sm-4">To</dt>
                                <dd class="col-sm-8">
                                    @if($transfer->to_type === 'branch')
                                        Branch: {{ $transfer->toBranch->name ?? '-' }}
                                    @elseif($transfer->to_type === 'warehouse')
                                        Warehouse: {{ $transfer->toWarehouse->name ?? '-' }}
                                    @else
                                        {{ ucfirst($transfer->to_type) }}
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Quantity</dt>
                                <dd class="col-sm-8">{{ $transfer->quantity }}</dd>

                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8"><span class="badge bg-info">{{ ucfirst($transfer->status) }}</span></dd>

                                <dt class="col-sm-4">Requested By</dt>
                                <dd class="col-sm-8">{{ optional($transfer->requestedPerson)->first_name }} {{ optional($transfer->requestedPerson)->last_name }}</dd>

                                <dt class="col-sm-4">Approved By</dt>
                                <dd class="col-sm-8">{{ optional($transfer->approvedPerson)->first_name }} {{ optional($transfer->approvedPerson)->last_name }}</dd>

                                <dt class="col-sm-4">Requested At</dt>
                                <dd class="col-sm-8">{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('Y-m-d H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Approved At</dt>
                                <dd class="col-sm-8">{{ $transfer->approved_at ? \Carbon\Carbon::parse($transfer->approved_at)->format('Y-m-d H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Shipped At</dt>
                                <dd class="col-sm-8">{{ $transfer->shipped_at ? \Carbon\Carbon::parse($transfer->shipped_at)->format('Y-m-d H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Delivered At</dt>
                                <dd class="col-sm-8">{{ $transfer->delivered_at ? \Carbon\Carbon::parse($transfer->delivered_at)->format('Y-m-d H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Notes</dt>
                                <dd class="col-sm-8">{{ $transfer->notes ?? '-' }}</dd>
                            </dl>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('stocktransfer.list') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                @if(in_array($transfer->status, ['pending', 'rejected']))
                                    <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transfer? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash me-2"></i>Delete Transfer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection