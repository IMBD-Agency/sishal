@extends('erp.master')

@section('title', 'Purchase Return Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <!-- Premium Header Area -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium text-uppercase">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted small">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('purchaseReturn.list') }}" class="text-decoration-none text-muted small">Return Registry</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold small">Return #{{ $purchaseReturn->id }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Purchase Return #{{ $purchaseReturn->id }}</h4>
                        <span class="badge bg-light text-primary border border-primary small rounded-pill px-3 py-1">Audit View</span>
                    </div>
                    <p class="text-muted mb-0 small mt-1">Detailed forensic view of reverse procurement transaction.</p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('purchaseReturn.list') }}" class="btn btn-outline-dark shadow-sm px-4 fw-bold">
                        <i class="fas fa-arrow-left me-2"></i>Return Registry
                    </a>
                    <button type="button" class="btn btn-outline-primary shadow-sm px-4 fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Registry
                    </button>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row">
                <!-- Left Column - Return Details -->
                <div class="col-lg-8">
                    <!-- Return Information Card -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-info-circle me-2 text-primary"></i>Transaction Audit trail</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Return Identifier</label>
                                        <p class="mb-0 fw-bold fs-5 text-dark">#RET-{{ str_pad($purchaseReturn->id, 5, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Procurement Reference</label>
                                        <p class="mb-0 fw-bold text-primary">#PUR-{{ str_pad($purchaseReturn->purchase->id ?? 0, 5, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Executing Supplier</label>
                                        <p class="mb-0 fw-bold fs-6">{{ $purchaseReturn->supplier->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Audit Registry Date</label>
                                        <p class="mb-0 fw-bold">{{ $purchaseReturn->return_date }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Settlement Type</label>
                                        <p class="mb-0 fw-bold text-dark">{{ ucfirst(str_replace('_', ' ', $purchaseReturn->return_type)) }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Verification Status</label>
                                        <div>
                                            @php
                                                $statusDot = [
                                                    'pending' => 'bg-warning',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'processed' => 'bg-info',
                                                ][$purchaseReturn->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="status-pill {{ str_replace('bg-', 'status-', $statusDot) }} status-badge cursor-pointer" 
                                                  data-return-id="{{ $purchaseReturn->id }}" 
                                                  data-current-status="{{ $purchaseReturn->status }}">
                                                <i class="fas fa-circle extra-small"></i>{{ ucfirst($purchaseReturn->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Originator Asset</label>
                                        <p class="mb-0 fw-bold text-muted small">{{ $purchaseReturn->createdBy->first_name ?? 'N/A' }} {{ $purchaseReturn->createdBy->last_name ?? '' }}</p>
                                    </div>
                                    @if($purchaseReturn->approved_by)
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Authorized Decision</label>
                                        <p class="mb-0 fw-bold text-success small">{{ $purchaseReturn->approvedBy->first_name ?? 'N/A' }} {{ $purchaseReturn->approvedBy->last_name ?? '' }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($purchaseReturn->reason)
                            <div class="mt-4 pt-4 border-top">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Audit Deficiency Reason</label>
                                <div class="alert alert-light border-0 py-2 px-3 mb-0 fw-bold text-dark small" style="background-color: #f8fafc;">
                                    {{ $purchaseReturn->reason }}
                                </div>
                            </div>
                            @endif
                            
                            @if($purchaseReturn->notes)
                            <div class="mt-4 pt-4 border-top">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Technical Audit Notes</label>
                                <div class="bg-light p-3 rounded" style="background: rgba(0,0,0,0.02) !important;">
                                    <pre class="mb-0 text-muted small" style="white-space: pre-wrap; font-family: inherit;">{{ $purchaseReturn->notes }}</pre>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Return Items Registry -->
                    <div class="premium-card">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-boxes me-2 text-primary"></i>Returned Assests registry</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table premium-table compact mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Product Component</th>
                                            <th class="text-center">Origin Node</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Unit Value</th>
                                            <th class="text-end">Line Total</th>
                                            <th class="text-center pe-4">Deficiency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseReturn->items as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center py-1">
                                                    <div class="thumbnail-box me-3" style="width: 35px; height: 35px;">
                                                        <img src="{{ $item->product && $item->product->image ? asset('storage/'.$item->product->image) : asset('static/default-product.png') }}" alt="P">
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark small">{{ $item->product->name ?? 'N/A' }}</h6>
                                                        <code class="text-primary extra-small">{{ $item->product->sku ?? 'N/A' }}</code>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border extra-small">
                                                    <i class="fas fa-network-wired me-1 text-muted"></i>{{ ucfirst($item->return_from_type) }}: 
                                                    @if($item->return_from_type === 'branch' && $item->branch)
                                                        {{ $item->branch->name }}
                                                    @elseif($item->return_from_type === 'warehouse' && $item->warehouse)
                                                        {{ $item->warehouse->name }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="text-center fw-bold">{{ number_format($item->returned_qty, 2) }}</td>
                                            <td class="text-end fw-bold">{{ number_format($item->unit_price, 2) }}৳</td>
                                            <td class="text-end fw-bold text-primary">{{ number_format($item->total_price, 2) }}৳</td>
                                            <td class="text-center pe-4 small text-muted fst-italic">{{ Str::limit($item->reason, 20) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted opacity-50 py-4">
                                                    <i class="fas fa-undo-alt fa-3x mb-3"></i>
                                                    <h6 class="fw-bold">No return items documented in this batch.</h6>
                                                    <p class="small mb-0">Adjust your audit filters or check the registry batch.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary & Auditor Actions -->
                <div class="col-lg-4">
                    <!-- High-Level Financial Summary -->
                    <div class="premium-card mb-4 border-primary-fade">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-chart-pie me-2 text-primary"></i>Financial Consolidation</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3 align-items-center">
                                <span class="small fw-bold text-muted text-uppercase">Asset Count:</span>
                                <span class="fw-bold h6 mb-0">{{ $purchaseReturn->items->count() }} Items</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 align-items-center">
                                <span class="small fw-bold text-muted text-uppercase">Gross Quantity:</span>
                                <span class="fw-bold h6 mb-0 text-dark">{{ number_format($purchaseReturn->items->sum('returned_qty'), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-0 align-items-center">
                                <span class="small fw-bold text-muted text-uppercase">Net Return Value:</span>
                                <span class="fw-bold h5 mb-0 text-primary">{{ number_format($purchaseReturn->items->sum('total_price'), 2) }}৳</span>
                            </div>
                            
                            <div class="mt-4 pt-4 border-top">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="extra-small fw-bold text-muted text-uppercase">Batched:</span>
                                    <span class="extra-small fw-bold text-dark">{{ $purchaseReturn->created_at ? $purchaseReturn->created_at->format('M d, Y | H:i') : 'N/A' }}</span>
                                </div>
                                @if($purchaseReturn->approved_at)
                                <div class="d-flex justify-content-between">
                                    <span class="extra-small fw-bold text-muted text-uppercase">Verified:</span>
                                    <span class="extra-small fw-bold text-success">{{ $purchaseReturn->approved_at ? $purchaseReturn->approved_at->format('M d, Y | H:i') : 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Strategic Auditor Actions -->
                    <div class="premium-card sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-bolt me-2 text-warning"></i>Strategic decisions</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-grid gap-3">
                                @if($purchaseReturn->status === 'pending')
                                    <a href="{{ route('purchaseReturn.edit', $purchaseReturn->id) }}" class="btn btn-outline-warning fw-bold py-2 shadow-sm">
                                        <i class="fas fa-edit me-2"></i>Modify Registry
                                    </a>
                                    <button type="button" class="btn btn-success fw-bold py-2 shadow-sm update-status-btn" 
                                            data-return-id="{{ $purchaseReturn->id }}" data-status="approved">
                                        <i class="fas fa-check-double me-2"></i>Verify & Approve
                                    </button>
                                    <button type="button" class="btn btn-outline-danger fw-bold py-2 shadow-sm update-status-btn" 
                                            data-return-id="{{ $purchaseReturn->id }}" data-status="rejected">
                                        <i class="fas fa-ban me-2"></i>Reject Batch
                                    </button>
                                @elseif($purchaseReturn->status === 'approved')
                                    <button type="button" class="btn btn-primary fw-bold py-2 shadow-lg update-status-btn" 
                                            data-return-id="{{ $purchaseReturn->id }}" data-status="processed">
                                        <i class="fas fa-microchip me-2"></i>Execute Reverse Logistics
                                    </button>
                                @endif
                                <a href="{{ route('purchaseReturn.list') }}" class="btn btn-light fw-bold py-2 border shadow-none">
                                    <i class="fas fa-list-ul me-2 text-muted"></i>Return to Registry
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusUpdateModalLabel">Update Purchase Return Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="statusUpdateForm">
                        <input type="hidden" id="returnId" name="return_id">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label">New Status</label>
                            <select class="form-select" id="newStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="processed">Processed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="statusNotes" name="notes" rows="3" placeholder="Add any notes about this status change..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateStatusBtn">
                        <span class="btn-text">Update Status</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle status badge click to open modal
            $(document).on('click', '.status-badge', function() {
                const returnId = $(this).data('return-id');
                const currentStatus = $(this).data('current-status');
                
                // Set modal data
                $('#returnId').val(returnId);
                $('#newStatus').val(currentStatus);
                
                // Show modal
                $('#statusUpdateModal').modal('show');
            });

            // Handle status update form submission
            $('#updateStatusBtn').on('click', function() {
                const returnId = $('#returnId').val();
                const newStatus = $('#newStatus').val();
                const notes = $('#statusNotes').val();
                const button = $(this);
                
                if (!newStatus) {
                    alert('Please select a new status.');
                    return;
                }

                // Show confirmation dialog
                let confirmMessage = '';
                switch(newStatus) {
                    case 'approved':
                        confirmMessage = 'Are you sure you want to approve this purchase return?';
                        break;
                    case 'rejected':
                        confirmMessage = 'Are you sure you want to reject this purchase return?';
                        break;
                    case 'processed':
                        confirmMessage = 'Are you sure you want to process this purchase return? This will adjust stock levels.';
                        break;
                }

                if (!confirm(confirmMessage)) {
                    return;
                }

                // Disable button and show loading
                button.prop('disabled', true);
                $('.btn-text').hide();
                $('.btn-loading').show();

                // Send AJAX request
                $.ajax({
                    url: `/erp/purchase-return/${returnId}/update-status`,
                    method: 'POST',
                    data: {
                        status: newStatus,
                        notes: notes,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert(response.message);
                            
                            // Close modal
                            $('#statusUpdateModal').modal('hide');
                            
                            // Reload page to reflect changes
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                            button.prop('disabled', false);
                            $('.btn-text').show();
                            $('.btn-loading').hide();
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred while updating the status.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert('Error: ' + errorMessage);
                        button.prop('disabled', false);
                        $('.btn-text').show();
                        $('.btn-loading').hide();
                    }
                });
            });

            // Reset modal when closed
            $('#statusUpdateModal').on('hidden.bs.modal', function() {
                $('#statusUpdateForm')[0].reset();
                $('#returnId').val('');
                $('#newStatus').val('pending');
                $('#statusNotes').val('');
                $('#updateStatusBtn').prop('disabled', false);
                $('.btn-text').show();
                $('.btn-loading').hide();
            });
        });
    </script>
@endsection