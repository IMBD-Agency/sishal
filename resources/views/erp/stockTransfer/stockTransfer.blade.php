@extends('erp.master')

@section('title', 'Stock Transfer')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Stock Transfer</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Stock Transfer</h2>
                    <p class="text-muted mb-0">Transfer stock levels across all locations and warehouses</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group me-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#stockTransferModal">
                            <i class="fas fa-adjust me-2"></i>Make Transfer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Please check the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Search Product Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" placeholder="Product name..." name="search" value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">From Location</label>
                                <select class="form-select" name="from_branch_id">
                                    <option value="">All Locations</option>
                                    <optgroup label="Branches">
                                        @foreach ($branches as $branch)
                                            <option value="branch_{{ $branch->id }}" {{ ($filters['from_branch_id'] ?? '') == ('branch_' . $branch->id) || ($filters['from_branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Warehouses">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}" {{ ($filters['from_branch_id'] ?? '') == ('warehouse_' . $warehouse->id) || ($filters['from_warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">To Location</label>
                                <select class="form-select" name="to_branch_id">
                                    <option value="">All Locations</option>
                                    <optgroup label="Branches">
                                        @foreach ($branches as $branch)
                                            <option value="branch_{{ $branch->id }}" {{ ($filters['to_branch_id'] ?? '') == ('branch_' . $branch->id) || ($filters['to_branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Warehouses">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}" {{ ($filters['to_branch_id'] ?? '') == ('warehouse_' . $warehouse->id) || ($filters['to_warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Date From</label>
                                <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary w-100" type="submit" title="Apply Filters">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Date To</label>
                                <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-md-10">
                                <a href="{{ route('stocktransfer.list') }}" class="btn btn-outline-danger" title="Reset All Filters">
                                    <i class="fas fa-times me-2"></i>Reset Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stock Transfer Listing Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Stock Transfers</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="transferTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="border-0">Product</th>
                                    <th class="border-0">From</th>
                                    <th class="border-0">To</th>
                                    <th class="border-0 text-center">Quantity</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Request By</th>
                                    <th class="border-0">Requested At</th>
                                    <th class="border-0">Approved By</th>
                                    <th class="border-0">Approved At</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transfers as $transfer)
                                    <tr>
                                        <td>
                                            {{ $transfer->product->name ?? '-' }}
                                            @if($transfer->variation_id && $transfer->variation)
                                                <br><small class="text-muted">{{ $transfer->variation->name ?? 'Variation #' . $transfer->variation_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transfer->from_type === 'branch')
                                                Branch: {{ $transfer->fromBranch->name ?? '-' }}
                                            @elseif($transfer->from_type === 'warehouse')
                                                Warehouse: {{ $transfer->fromWarehouse->name ?? '-' }}
                                            @else
                                                {{ ucfirst($transfer->from_type) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($transfer->to_type === 'branch')
                                                Branch: {{ $transfer->toBranch->name ?? '-' }}
                                            @elseif($transfer->to_type === 'warehouse')
                                                Warehouse: {{ $transfer->toWarehouse->name ?? '-' }}
                                            @else
                                                {{ ucfirst($transfer->to_type) }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $transfer->quantity }}</td>
                                        <td>
                                            <span class="badge bg-info status-badge" style="cursor:pointer;" data-transfer-id="{{ $transfer->id }}" data-current-status="{{ $transfer->status }}">{{ ucfirst($transfer->status) }}</span>
                                        </td>
                                        <td>{{@$transfer->requestedPerson->first_name}} {{@$transfer->requestedPerson->last_name}}</td>
                                        <td>{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('Y-m-d H:i') : '-' }}</td>
                                        <td>{{@$transfer->approvedPerson->first_name}} {{@$transfer->approvedPerson->last_name}}</td>
                                        <td>{{ $transfer->approved_at ? \Carbon\Carbon::parse($transfer->approved_at)->format('Y-m-d H:i') : '-' }}</td>
                                        <td>
                                            <a href="{{ route('stocktransfer.show',$transfer->id) }}" class="text-info me-2" title="View"><i class="fas fa-eye"></i></a>
                                            @if(in_array($transfer->status, ['pending', 'rejected']))
                                                <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transfer? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @else
                                                <span class="text-muted" title="Cannot delete {{ ucfirst($transfer->status) }} transfer"><i class="fas fa-trash"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing {{ $transfers->firstItem() }} to {{ $transfers->lastItem() }} of {{ $transfers->total() }} transfers
                        </span>
                        {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>

            <!-- Stock Transfer Modal -->
            <div class="modal fade" id="stockTransferModal" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('stocktransfer.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">New Stock Transfer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex align-items-end gap-2 w-100">
                                <div class="mb-3">
                                    <label class="form-label">From</label>
                                    <select class="form-select from-type-select" name="from_type" required>
                                        <option value="branch">Branch</option>
                                        <option value="warehouse">Warehouse</option>
                                    </select>
                                </div>
                                <div class="mb-3 w-100 from-branch-group">
                                    <select name="from_branch_id" class="form-select">
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 w-100 from-warehouse-group" style="display:none;">
                                    <select name="from_warehouse_id" class="form-select">
                                        <option value="">Select Warehouse</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{$warehouse->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex align-items-end gap-2 w-100">
                                <div class="mb-3">
                                    <label class="form-label">To</label>
                                    <select class="form-select to-type-select" name="to_type" required>
                                        <option value="branch">Branch</option>
                                        <option value="warehouse">Warehouse</option>
                                    </select>
                                </div>
                                <div class="mb-3 w-100 to-branch-group">
                                    <select name="to_branch_id" class="form-select">
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 w-100 to-warehouse-group" style="display:none;">
                                    <select name="to_warehouse_id" class="form-select">
                                        <option value="">Select Warehouse</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{$warehouse->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product</label>
                                <select class="form-select" name="product_id" id="productSelect" required style="width: 100%">
                                    <option value="">Select Product...</option>
                                </select>
                            </div>
                            <div class="mb-3" id="variationWrapper" style="display: none;">
                                <label class="form-label">Variation <span class="text-muted">(if applicable)</span></label>
                                <select class="form-select" name="variation_id" id="variationSelect" style="width: 100%">
                                    <option value="">Select Variation...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="quantity" min="0.01" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Transfer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status Update Modal -->
            <div class="modal fade" id="statusUpdateModal" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" id="statusUpdateForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Update Transfer Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="transfer_id" id="modalTransferId">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="modalStatusSelect">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Function to clean up stuck modal backdrop (only called after modal is closed)
                function cleanupModalBackdrop() {
                    // Wait a moment to ensure modal animation is complete
                    setTimeout(function() {
                        // Check if any modal is currently showing
                        var hasVisibleModal = document.querySelector('.modal.show');
                        
                        // Only clean up if no modal is visible
                        if (!hasVisibleModal) {
                            // Remove any stuck backdrops
                            var backdrops = document.querySelectorAll('.modal-backdrop');
                            backdrops.forEach(function(backdrop) {
                                backdrop.remove();
                            });
                            
                            // Clean up body classes
                            if (document.body.classList.contains('modal-open')) {
                                document.body.classList.remove('modal-open');
                            }
                            if (document.body.style.paddingRight) {
                                document.body.style.paddingRight = '';
                            }
                        }
                    }, 150);
                }

                // Initialize modal instance once
                var stockTransferModalElement = document.getElementById('stockTransferModal');
                var stockTransferModal = null;
                if (stockTransferModalElement) {
                    stockTransferModal = new bootstrap.Modal(stockTransferModalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                }

                document.querySelectorAll('[data-bs-target="#stockTransferModal"]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (stockTransferModal) {
                            stockTransferModal.show();
                        }
                    });
                });

                $('#productSelect').select2({
                    placeholder: 'Search or select a product',
                    allowClear: true,
                    ajax: {
                        url: '/erp/products/search',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    return { id: item.id, text: item.name };
                                })
                            };
                        },
                        cache: true
                    },
                    width: 'resolve',
                    dropdownParent: $('#stockTransferModal'),
                });

                // Initialize variation select2
                $('#variationSelect').select2({
                    placeholder: 'Select Variation...',
                    allowClear: true,
                    width: 'resolve',
                    dropdownParent: $('#stockTransferModal'),
                });

                // Load variations when product is selected
                $('#productSelect').on('change', function() {
                    const productId = $(this).val();
                    loadProductVariations(productId);
                });

                // Function to load product variations
                function loadProductVariations(productId) {
                    if (!productId) {
                        $('#variationWrapper').hide();
                        $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                        return;
                    }
                    
                    $.ajax({
                        url: '/erp/products/' + productId + '/variations-list',
                        type: 'GET',
                        dataType: 'json',
                        success: function(variations) {
                            // Clear existing options
                            $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                            
                            // Add variation options
                            if (variations && variations.length > 0) {
                                variations.forEach(function(variation) {
                                    var option = new Option(variation.display_name || variation.name, variation.id, false, false);
                                    $('#variationSelect').append(option);
                                });
                                $('#variationWrapper').show();
                            } else {
                                $('#variationWrapper').hide();
                            }
                            
                            $('#variationSelect').trigger('change');
                        },
                        error: function(xhr) {
                            console.error('Error loading variations:', xhr);
                            $('#variationWrapper').hide();
                            $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                        }
                    });
                }

                // Show/hide branch/warehouse selects for FROM
                $('.from-type-select').on('change', function() {
                    if ($(this).val() === 'branch') {
                        $('.from-branch-group').show();
                        $('.from-warehouse-group').hide();
                    } else if ($(this).val() === 'warehouse') {
                        $('.from-branch-group').hide();
                        $('.from-warehouse-group').show();
                    } else {
                        $('.from-branch-group').hide();
                        $('.from-warehouse-group').hide();
                    }
                }).trigger('change');
                // Show/hide branch/warehouse selects for TO
                $('.to-type-select').on('change', function() {
                    if ($(this).val() === 'branch') {
                        $('.to-branch-group').show();
                        $('.to-warehouse-group').hide();
                    } else if ($(this).val() === 'warehouse') {
                        $('.to-branch-group').hide();
                        $('.to-warehouse-group').show();
                    } else {
                        $('.to-branch-group').hide();
                        $('.to-warehouse-group').hide();
                    }
                }).trigger('change');

                // Reset form when modal is closed
                $('#stockTransferModal').on('hidden.bs.modal', function() {
                    // Reset form fields
                    $('#productSelect').val(null).trigger('change');
                    $('#variationSelect').val(null).trigger('change');
                    $('#variationWrapper').hide();
                    var form = $('#stockTransferModal').find('form')[0];
                    if (form) {
                        form.reset();
                    }
                    
                    // Clean up any stuck backdrops
                    cleanupModalBackdrop();
                });

                $(document).on('click', '.status-badge', function() {
                    var transferId = $(this).data('transfer-id');
                    var currentStatus = $(this).data('current-status');
                    $('#modalTransferId').val(transferId);
                    $('#modalStatusSelect').val(currentStatus);

                    // Enable all options first
                    $('#modalStatusSelect option').prop('disabled', false);

                    // Disable options based on current status
                    if (currentStatus === 'delivered') {
                        $('#modalStatusSelect option').prop('disabled', true);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', false);
                    } else if (currentStatus === 'approved') {
                        $('#modalStatusSelect option[value="pending"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="approved"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="shipped"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="rejected"]').prop('disabled', false);
                    } else if (currentStatus === 'shipped') {
                        $('#modalStatusSelect option[value="pending"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="approved"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="shipped"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="rejected"]').prop('disabled', false);
                    } else if (currentStatus === 'pending') {
                        $('#modalStatusSelect option[value="pending"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="approved"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="shipped"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="rejected"]').prop('disabled', false);
                    }

                    var actionUrl = "{{ route('stocktransfer.status', ['id' => 'TRANSFER_ID']) }}".replace('TRANSFER_ID', transferId);
                    $('#statusUpdateForm').attr('action', actionUrl);
                    var statusModalElement = document.getElementById('statusUpdateModal');
                    if (statusModalElement) {
                        var statusModal = new bootstrap.Modal(statusModalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                        statusModal.show();
                        
                        // Clean up backdrop when status modal is closed
                        $(statusModalElement).on('hidden.bs.modal', function() {
                            cleanupModalBackdrop();
                        });
                    }
                });
            });
        </script>
    </div>
@endsection