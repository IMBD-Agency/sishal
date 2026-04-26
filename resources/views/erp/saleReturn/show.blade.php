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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
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
                    @if($saleReturn->status !== 'processed')
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-primary fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-check-circle me-2"></i>Update Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                @if($saleReturn->status === 'pending')
                                    <li><a class="dropdown-item py-2 fw-semibold text-success" href="javascript:void(0)" onclick="openStatusModal('approved')"><i class="fas fa-check me-2"></i>Approve Return</a></li>
                                    <li><a class="dropdown-item py-2 fw-semibold text-danger" href="javascript:void(0)" onclick="openStatusModal('rejected')"><i class="fas fa-times me-2"></i>Reject Return</a></li>
                                @endif
                                @if(in_array($saleReturn->status, ['pending', 'approved']))
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2 fw-bold text-info" href="javascript:void(0)" onclick="openStatusModal('processed')"><i class="fas fa-box-check me-2"></i>Finalize & Process Stock</a></li>
                                @endif
                            </ul>
                        </div>
                    @endif
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

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="statusForm">
                    @csrf
                    <input type="hidden" name="status" id="modalStatus">
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold" id="modalTitle">Update Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 mb-4" id="modalAlert">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="modalDescription"></span>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Add Notes (Optional)</label>
                            <textarea name="notes" class="form-control border-light-subtle" rows="3" placeholder="Explain the reason for this status change..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light py-3">
                        <button type="button" class="btn btn-light border fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-5" id="modalSubmitBtn">Confirm Action</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openStatusModal(status) {
        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDescription');
        const submitBtn = document.getElementById('modalSubmitBtn');
        const statusInput = document.getElementById('modalStatus');
        
        statusInput.value = status;
        
        if(status === 'approved') {
            title.innerText = 'Approve Sale Return';
            title.className = 'modal-title fw-bold text-success';
            desc.innerText = 'Are you sure you want to approve this return request? No stock changes will happen yet.';
            submitBtn.className = 'btn btn-success fw-bold px-5';
            submitBtn.innerText = 'Approve Now';
        } else if(status === 'rejected') {
            title.innerText = 'Reject Sale Return';
            title.className = 'modal-title fw-bold text-danger';
            desc.innerText = 'Are you sure you want to reject this return request?';
            submitBtn.className = 'btn btn-danger fw-bold px-5';
            submitBtn.innerText = 'Reject Now';
        } else if(status === 'processed') {
            title.innerText = 'Finalize & Process Return';
            title.className = 'modal-title fw-bold text-info';
            desc.innerHTML = '<strong>Important:</strong> This will finalize the return, <strong>increase stock</strong> at the destination, and generate <strong>accounting entries</strong>. This action cannot be undone.';
            submitBtn.className = 'btn btn-info fw-bold px-5 text-white';
            submitBtn.innerText = 'Process & Update Stock';
        }
        
        modal.show();
    }

    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('modalSubmitBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        const formData = new FormData(this);
        const url = "{{ route('saleReturn.updateStatus', $saleReturn->id) }}";

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Something went wrong');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
</script>
@endpush