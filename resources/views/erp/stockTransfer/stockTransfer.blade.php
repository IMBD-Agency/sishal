@extends('erp.master')

@section('title', 'Stock Transfer')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

    <!-- Header Section -->
    <div class="container-fluid px-4 py-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Transfer Lists</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('stocktransfer.export.excel', request()->all()) }}" class="btn btn-success btn-sm px-3">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </a>
                    <a href="{{ route('stocktransfer.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-plus me-1"></i>New Transfer
                    </a>
                </div>
            </div>
        </div>

    <div class="container-fluid px-4 py-4">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
        @endif

        <!-- Filters Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <!-- Report Type Tabs -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="report_type" id="daily" value="daily" checked>
                        <label class="btn btn-outline-primary btn-sm" for="daily">Daily Reports</label>
                        
                        <input type="radio" class="btn-check" name="report_type" id="monthly" value="monthly">
                        <label class="btn btn-outline-primary btn-sm" for="monthly">Monthly Reports</label>
                        
                        <input type="radio" class="btn-check" name="report_type" id="yearly" value="yearly">
                        <label class="btn btn-outline-primary btn-sm" for="yearly">Yearly Reports</label>
                    </div>
                </div>

                <form method="GET" action="" id="filterForm">
                    <div class="row g-3">
                        <!-- Month -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Month</label>
                            <select name="month" class="form-select">
                                <option value="">Select Month</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Year -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Year</label>
                            <select name="year" class="form-select">
                                <option value="">Select Year</option>
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Receiver Outlet -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Receiver Outlet</label>
                            <select name="to_branch_id" class="form-select">
                                <option value="">All Outlet</option>
                                @foreach ($branches as $branch)
                                    <option value="branch_{{ $branch->id }}" {{ request('to_branch_id') == 'branch_'.$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                                @foreach ($warehouses as $warehouse)
                                    <option value="warehouse_{{ $warehouse->id }}" {{ request('to_branch_id') == 'warehouse_'.$warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Invoice -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Invoice</label>
                            <select name="invoice_id" class="form-select">
                                <option value="">All Invoice</option>
                                @foreach($transfers as $transfer)
                                    <option value="{{ $transfer->id }}">#{{ $transfer->id }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Product</label>
                            <select name="product_id" class="form-select">
                                <option value="">All Product</option>
                                @foreach($transfers->pluck('product')->unique('id') as $product)
                                    @if($product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Style Number -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Style Number</label>
                            <select name="style_number" class="form-select">
                                <option value="">All Style Number</option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">All Category</option>
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Brand</label>
                            <select name="brand_id" class="form-select">
                                <option value="">All Brand</option>
                            </select>
                        </div>

                        <!-- Season -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Season</label>
                            <select name="season_id" class="form-select">
                                <option value="">All Season</option>
                            </select>
                        </div>

                        <!-- Gender -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender_id" class="form-select">
                                <option value="">All Gender</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="mb-3">
            <div class="btn-group" role="group">
                <button class="btn btn-dark btn-sm px-3">CSV</button>
                <a href="{{ route('stocktransfer.export.excel', request()->all()) }}" class="btn btn-dark btn-sm px-3">Excel</a>
                <a href="{{ route('stocktransfer.export.pdf', request()->all()) }}" class="btn btn-dark btn-sm px-3">PDF</a>
                <a href="{{ route('stocktransfer.export.pdf', array_merge(request()->all(), ['action' => 'print'])) }}" target="_blank" class="btn btn-dark btn-sm px-3">Print</a>
            </div>
            <input type="text" id="tableSearch" class="form-control form-control-sm d-inline-block ms-2" style="width: 200px;" placeholder="Search...">
        </div>

        <!-- Stock Transfer Listing Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="transferTable">
                        <thead>
                            <tr style="background-color: #2A5341; color: white; text-transform: uppercase; font-size: 0.8rem; font-weight: 600;">
                                <th class="ps-3 py-3 border-0 rounded-start">Serial No</th>
                                <th class="py-3 border-0">Invoice</th>
                                <th class="py-3 border-0">Date</th>
                                <th class="py-3 border-0">Outlet (From)</th>
                                <th class="py-3 border-0">Outlet (To)</th>
                                <th class="py-3 border-0">Transferred by</th>
                                <th class="py-3 border-0">Category</th>
                                <th class="py-3 border-0">Brand</th>
                                <th class="py-3 border-0">Season</th>
                                <th class="py-3 border-0">Gender</th>
                                <th class="py-3 border-0">Product Name</th>
                                <th class="py-3 border-0">Style Number</th>
                                <th class="py-3 border-0">Color</th>
                                <th class="py-3 border-0">Size</th>
                                <th class="py-3 border-0">Qty</th>
                                <th class="py-3 border-0">Total Qty</th>
                                <th class="py-3 border-0">Amount</th>
                                <th class="py-3 border-0">Paid</th>
                                <th class="py-3 border-0">Due</th>
                                <th class="text-center pe-3 py-3 border-0 rounded-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transfers as $index => $transfer)
                                <tr>
                                    <td class="ps-3 small">{{ $transfers->firstItem() + $index }}</td>
                                    <td class="small fw-bold" style="color: #e83e8c;">ST-{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td class="small">{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d/m/Y') : '-' }}</td>
                                    <td class="small">
                                        @if($transfer->from_type === 'branch')
                                            {{ $transfer->fromBranch->name ?? '-' }}
                                        @else
                                            {{ $transfer->fromWarehouse->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($transfer->to_type === 'branch')
                                            {{ $transfer->toBranch->name ?? '-' }}
                                        @else
                                            {{ $transfer->toWarehouse->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="small">{{ @$transfer->requestedPerson->name ?? '-' }}</td>
                                    <td class="small">{{ $transfer->product->category->name ?? '-' }}</td>
                                    <td class="small">{{ $transfer->product->brand->name ?? '-' }}</td>
                                    <td class="small">{{ $transfer->product->season->name ?? '-' }}</td>
                                    <td class="small">{{ $transfer->product->gender->name ?? '-' }}</td>
                                    <td class="small fw-bold">{{ $transfer->product->name ?? '-' }}</td>
                                    <td class="small" style="color: #e83e8c;">{{ $transfer->product->style_number ?? '-' }}</td>
                                    <td class="small">
                                        @if($transfer->variation && $transfer->variation->combinations)
                                            @php
                                                $color = null;
                                                foreach($transfer->variation->combinations as $combo) {
                                                    $attrName = strtolower($combo->attribute->name ?? '');
                                                    if(in_array($attrName, ['color', 'colour', 'colors'])) {
                                                        $color = $combo->attributeValue->value ?? null;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            {{ $color ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($transfer->variation && $transfer->variation->combinations)
                                            @php
                                                $size = null;
                                                foreach($transfer->variation->combinations as $combo) {
                                                    $attrName = strtolower($combo->attribute->name ?? '');
                                                    if(in_array($attrName, ['size', 'sizes'])) {
                                                        $size = $combo->attributeValue->value ?? null;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            {{ $size ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="small fw-bold">{{ $transfer->quantity }}</td>
                                    <td class="small">{{ $transfer->quantity }}</td>
                                    <td class="small">0.00</td>
                                    <td class="small">0.00</td>
                                    <td class="small">0.00</td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('stocktransfer.show',$transfer->id) }}" 
                                               class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                               style="width: 26px; height: 26px; color: #0dcaf0;" title="View">
                                                <i class="fas fa-eye fa-xs"></i>
                                            </a>
                                            @if(in_array($transfer->status, ['pending', 'rejected']))
                                                <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                                            style="width: 26px; height: 26px; color: #dc3545;" 
                                                            onclick="return confirm('Are you sure?')" title="Delete">
                                                        <i class="fas fa-trash fa-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p>No data available in table</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transfers->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
                    <div class="small text-muted">
                        Showing {{ $transfers->firstItem() ?: 0 }} to {{ $transfers->lastItem() ?: 0 }} of {{ $transfers->total() }} entries
                    </div>
                    <div>
                        {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Total Summary -->
        <div class="mt-3 text-end">
            <div class="d-inline-block bg-white border rounded px-4 py-2 shadow-sm">
                <span class="fw-bold">Total Quantity:</span>
                <span class="text-success fw-bold ms-2">{{ $transfers->sum('quantity') }}</span>
                <span class="mx-3">|</span>
                <span class="fw-bold">Total Amount:</span>
                <span class="text-success fw-bold ms-2">0.00</span>
            </div>
        </div>

        <!-- Stock Transfer Modal -->
        <div class="modal fade" id="stockTransferModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content border-0 shadow" method="POST" action="{{ route('stocktransfer.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Create Stock Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- FROM -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-3">Transfer From</label>
                                    <div class="mb-3">
                                        <select class="form-select from-type-select mb-2" name="from_type" required>
                                            <option value="branch">Branch</option>
                                            <option value="warehouse">Warehouse</option>
                                        </select>
                                    </div>
                                    <div class="from-branch-group">
                                        <select name="from_branch_id" class="form-select">
                                            <option value="">Select Branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="from-warehouse-group" style="display:none;">
                                        <select name="from_warehouse_id" class="form-select">
                                            <option value="">Select Warehouse</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TO -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-3">Transfer To</label>
                                    <div class="mb-3">
                                        <select class="form-select to-type-select mb-2" name="to_type" required>
                                            <option value="branch">Branch</option>
                                            <option value="warehouse">Warehouse</option>
                                        </select>
                                    </div>
                                    <div class="to-branch-group">
                                        <select name="to_branch_id" class="form-select">
                                            <option value="">Select Branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="to-warehouse-group" style="display:none;">
                                        <select name="to_warehouse_id" class="form-select">
                                            <option value="">Select Warehouse</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Product -->
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold">Select Product</label>
                                <select class="form-select" name="product_id" id="productSelect" required style="width: 100%">
                                    <option value="">Search by name or code...</option>
                                </select>
                            </div>

                            <div class="col-md-8" id="variationWrapper" style="display: none;">
                                <label class="form-label fw-bold">Select Variation</label>
                                <select class="form-select" name="variation_id" id="variationSelect" style="width: 100%">
                                    <option value="">Select Variation...</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" class="form-control" name="quantity" min="0.01" step="0.01" required placeholder="0.00">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Internal Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Reason for transfer, special instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">Process Transfer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div class="modal fade" id="statusUpdateModal" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content border-0 shadow" id="statusUpdateForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Update Transfer Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="transfer_id" id="modalTransferId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Status</label>
                            <select class="form-select rounded-3" name="status" id="modalStatusSelect">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <p class="text-muted small">Changing status may affect inventory levels at both source and destination.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <script>
            function applyQuickFilter(value) {
                document.getElementsByName('date_from')[0].value = '';
                document.getElementsByName('date_to')[0].value = '';
                document.getElementById('filterForm').submit();
            }

            // Table search functionality
            $('#tableSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#transferTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

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