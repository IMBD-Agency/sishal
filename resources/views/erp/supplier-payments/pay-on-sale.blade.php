@extends('erp.master')

@section('title', 'Pay-on-Sale (Consignment) Settlement')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}" class="text-decoration-none text-muted">Supplier Payments</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Pay-on-Sale Settlement</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">Pay-on-Sale (Consignment) Settlement</h4>
                            <p class="text-muted small mb-0">Pay suppliers exclusively for goods that have been sold (Sold Qty × Unit Cost)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
                    <a href="{{ route('supplier-payments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fas fa-history me-1"></i>All Payments
                    </a>
                    <a href="{{ route('supplier-payments.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-plus-circle me-1"></i>Bill Payment
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold rounded-3">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 fw-bold rounded-3">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- Filter & Supplier Selection Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4 bg-light bg-opacity-50">
                    <form action="javascript:void(0);" id="payOnSaleFilterForm" onsubmit="event.preventDefault(); return false;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Supplier <span class="text-danger">*</span></label>
                                <select class="form-select select2-supplier" name="supplier_id" id="supplierSelect">
                                    <option value="">-- Choose Supplier --</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>
                                            {{ $sup->name }} {{ $sup->phone ? '('.$sup->phone.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Product / SKU / Style</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control border-start-0" name="search" id="productSearchInput" placeholder="Name, SKU, Style Number..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">From Date</label>
                                <input type="date" class="form-control" name="start_date" id="startDateInput" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">To Date</label>
                                <input type="date" class="form-control" name="end_date" id="endDateInput" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="button" class="btn btn-primary flex-grow-1 rounded-3 fw-bold no-loader" id="filterSubmitBtn">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <button type="button" class="btn btn-outline-secondary rounded-3 no-loader {{ !request()->hasAny(['search', 'start_date', 'end_date']) && !$supplierId ? 'd-none' : '' }}" id="resetFiltersBtn" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Data Container (AJAX Target) -->
            <div id="dataContainer" class="{{ !$supplierId ? 'd-none' : '' }}">
                <!-- Metric Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100">
                            <div class="small text-muted fw-bold text-uppercase">Purchased Qty</div>
                            <div class="fs-4 fw-bold text-primary mt-1" id="sumPurchasedQty">{{ number_format($summary['totals']['purchased_qty'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100">
                            <div class="small text-muted fw-bold text-uppercase">Sold Qty</div>
                            <div class="fs-4 fw-bold text-success mt-1" id="sumSoldQty">{{ number_format($summary['totals']['sold_qty'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100">
                            <div class="small text-muted fw-bold text-uppercase">In-Stock Qty</div>
                            <div class="fs-4 fw-bold text-warning mt-1" id="sumInStockQty">{{ number_format($summary['totals']['in_stock_qty'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100">
                            <div class="small text-muted fw-bold text-uppercase">Total Sold Cost</div>
                            <div class="fs-4 fw-bold text-dark mt-1">৳<span id="sumSoldCost">{{ number_format($summary['totals']['sold_cost_payable'] ?? 0, 2) }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100">
                            <div class="small text-muted fw-bold text-uppercase">Already Paid</div>
                            <div class="fs-4 fw-bold text-info mt-1">৳<span id="sumTotalPaid">{{ number_format($summary['totals']['total_paid'] ?? 0, 2) }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-success bg-opacity-10 border border-success border-opacity-25 h-100">
                            <div class="small text-success fw-bold text-uppercase">Net Due Payable</div>
                            <div class="fs-4 fw-bold text-success mt-1">৳<span id="sumNetDue">{{ number_format($summary['totals']['net_due_payable'] ?? 0, 2) }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Products Breakdown Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 position-relative">
                    <!-- Loading Overlay -->
                    <div id="tableLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none d-flex align-items-center justify-content-center" style="z-index: 10;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="small fw-bold text-muted mt-2">Loading settlement data...</div>
                        </div>
                    </div>

                    <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list me-2 text-primary"></i>Supplied Products & Sales Settlement Status</h6>
                            <div class="extra-small text-muted mt-1">
                                Showing <span id="visibleItemCount" class="fw-bold text-dark">{{ count($summary['items'] ?? []) }}</span> of <span id="totalSupplierItemsCount" class="fw-bold text-dark">{{ count($summary['items'] ?? []) }}</span> products
                            </div>
                        </div>

                        <!-- Table Controls -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select class="form-select form-select-sm rounded-pill" id="pageSizeSelect" style="width: 105px;">
                                <option value="25" selected>25 / page</option>
                                <option value="50">50 / page</option>
                                <option value="100">100 / page</option>
                                <option value="-1">Show All</option>
                            </select>

                            @canany(['pay on sale', 'create payments'])
                                <button type="button" class="btn btn-success btn-sm px-4 rounded-pill shadow-sm fw-bold no-loader" id="settleModalBtn" data-bs-toggle="modal" data-bs-target="#payOnSaleModal" {{ ($summary['totals']['net_due_payable'] ?? 0) <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-check-double me-2"></i>Settle Selected (<span id="settleBtnLabel">৳{{ number_format($summary['totals']['net_due_payable'] ?? 0, 2) }}</span>)
                                </button>
                            @endcanany
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="payOnSaleTable">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="ps-3 py-3" width="4%">
                                            <input type="checkbox" class="form-check-input" id="selectAllItems" checked title="Select/Deselect All Visible Items">
                                        </th>
                                        <th class="small text-muted text-uppercase py-3" width="4%">#</th>
                                        <th class="small text-muted text-uppercase py-3">Product Name</th>
                                        <th class="small text-muted text-uppercase py-3">SKU / Style</th>
                                        <th class="text-center small text-muted text-uppercase py-3">Purchased</th>
                                        <th class="text-center small text-muted text-uppercase py-3">Sold Qty</th>
                                        <th class="text-center small text-muted text-uppercase py-3">In-Stock</th>
                                        <th class="text-end small text-muted text-uppercase py-3">Unit Cost (৳)</th>
                                        <th class="text-end small text-muted text-uppercase py-3">Sold Cost (৳)</th>
                                        <th class="text-end pe-3 small text-muted text-uppercase py-3">Net Due (৳)</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    @forelse($summary['items'] ?? [] as $index => $item)
                                        <tr class="product-row {{ ($item['net_due'] ?? $item['sold_cost_payable']) <= 0 ? 'bg-light bg-opacity-25 opacity-75' : '' }}"
                                            data-name="{{ strtolower($item['product_name']) }}"
                                            data-sku="{{ strtolower($item['sku']) }}"
                                            data-style="{{ strtolower($item['style_number']) }}">
                                            <td class="ps-3">
                                                <input type="checkbox" class="form-check-input item-checkbox" data-name="{{ $item['product_name'] }}" data-payable="{{ $item['net_due'] ?? $item['sold_cost_payable'] }}" {{ ($item['net_due'] ?? $item['sold_cost_payable']) > 0 ? 'checked' : 'disabled' }}>
                                            </td>
                                            <td class="row-index"><span class="badge bg-light text-dark border">{{ $loop->iteration }}</span></td>
                                            <td>
                                                <div class="fw-bold text-dark item-title">{{ $item['product_name'] }}</div>
                                                @if(($item['net_due'] ?? 0) <= 0 && $item['sold_cost_payable'] > 0)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 extra-small mt-1"><i class="fas fa-check-circle me-1"></i>Fully Settled</span>
                                                @elseif($item['sold_cost_payable'] <= 0)
                                                    <span class="badge bg-light text-muted border extra-small mt-1">No Sales Yet</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small text-muted item-sku">SKU: {{ $item['sku'] }}</div>
                                                <div class="extra-small text-muted item-style">Style: {{ $item['style_number'] }}</div>
                                            </td>
                                            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">{{ number_format($item['purchased_qty']) }}</span></td>
                                            <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">{{ number_format($item['sold_qty']) }}</span></td>
                                            <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3">{{ number_format($item['in_stock_qty']) }}</span></td>
                                            <td class="text-end fw-bold">৳{{ number_format($item['unit_cost'], 2) }}</td>
                                            <td class="text-end fw-bold text-dark">৳{{ number_format($item['sold_cost_payable'], 2) }}</td>
                                            <td class="text-end pe-3 fw-bold {{ ($item['net_due'] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">৳{{ number_format($item['net_due'] ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr id="emptyRow">
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <i class="fas fa-info-circle fa-2x mb-2 text-muted opacity-50 d-block"></i>
                                                No purchase or sales data found for the selected supplier.
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr id="noMatchRow" class="d-none">
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="fas fa-search fa-2x mb-2 text-muted opacity-50 d-block"></i>
                                            No products match your search query.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Footer -->
                        <div class="p-3 bg-light bg-opacity-50 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 {{ count($summary['items'] ?? []) === 0 ? 'd-none' : '' }}" id="paginationContainer">
                            <div class="small text-muted" id="paginationInfo">
                                Showing <span id="pageRangeText">1-25</span> of <span id="totalMatchedText">{{ count($summary['items'] ?? []) }}</span> items
                            </div>
                            <ul class="pagination pagination-sm mb-0 justify-content-end" id="paginationNav">
                                <!-- Dynamic pagination items generated by JS -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Supplier Selection State -->
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 {{ $supplierId ? 'd-none' : '' }}" id="emptySupplierContainer">
                <div class="card-body py-5">
                    <i class="fas fa-truck-loading fa-3x text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark mb-1">Select a Supplier to Begin Pay-on-Sale Settlement</h5>
                    <p class="text-muted mb-0">Choose a supplier above to view total purchased quantity, sold quantity, and calculate sold item payable amounts.</p>
                </div>
            </div>

            <!-- Settlement Payment Modal -->
            @canany(['pay on sale', 'create payments'])
                <div class="modal fade" id="payOnSaleModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header bg-primary text-white border-0 py-3">
                                <h6 class="modal-title fw-bold"><i class="fas fa-money-check-alt me-2"></i>Process Pay-on-Sale Settlement</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('supplier-payments.store-pay-on-sale') }}" id="settlePaymentForm">
                                @csrf
                                <input type="hidden" name="supplier_id" id="settleModalSupplierId" value="{{ $supplierId }}">
                                
                                <div class="modal-body p-4">
                                    <div class="alert alert-info border-0 rounded-3 mb-3">
                                        <div class="small fw-bold"><i class="fas fa-calculator me-1"></i>Pay-on-Sale Summary</div>
                                        <div class="d-flex justify-content-between mt-1 extra-small">
                                            <span>Selected Items Sold Cost: <strong id="modalSelectedCostText">৳{{ number_format($summary['totals']['sold_cost_payable'] ?? 0, 2) }}</strong></span>
                                            <span>Already Paid: <strong id="modalTotalPaidText">৳{{ number_format($summary['totals']['total_paid'] ?? 0, 2) }}</strong></span>
                                        </div>
                                        <div class="fw-bold text-success mt-1">
                                            Net Due for Selected Items: <span id="modalNetDueText">৳{{ number_format($summary['totals']['net_due_payable'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Settlement Payment Amount (৳) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">৳</span>
                                            <input type="number" step="0.01" class="form-control fw-bold fs-5 text-success" name="amount" id="settleModalAmountInput" value="{{ $summary['totals']['net_due_payable'] ?? 0 }}" max="{{ $summary['totals']['net_due_payable'] ?? 0 }}" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Payment Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Payment Account <span class="text-danger">*</span></label>
                                        <select class="form-select" name="account_id" required>
                                            <option value="">-- Choose Account --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->account_name ?? $acc->provider_name }} (Bal: ৳{{ number_format($acc->current_balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select" name="payment_method" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="check">Check</option>
                                            <option value="mobile_banking">Mobile Banking (bKash/Nagad)</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Reference / Transaction ID</label>
                                        <input type="text" class="form-control" name="reference" placeholder="Check no, Txn ID, etc.">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Note / Remarks</label>
                                        <textarea class="form-control" name="note" rows="2" placeholder="Pay-on-sale settlement for sold goods..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0 py-3">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-check-circle me-1"></i>Confirm Settlement Payment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select2-supplier').select2({
                    width: '100%',
                    dropdownParent: $('body')
                });

                let currentSummaryTotals = {
                    purchased_qty: {{ $summary['totals']['purchased_qty'] ?? 0 }},
                    sold_qty: {{ $summary['totals']['sold_qty'] ?? 0 }},
                    in_stock_qty: {{ $summary['totals']['in_stock_qty'] ?? 0 }},
                    sold_cost_payable: {{ $summary['totals']['sold_cost_payable'] ?? 0 }},
                    total_paid: {{ $summary['totals']['total_paid'] ?? 0 }},
                    net_due_payable: {{ $summary['totals']['net_due_payable'] ?? 0 }}
                };

                function updateSelectedPayableSummary() {
                    let totalSelectedCost = 0;
                    let selectedCount = 0;

                    $('.item-checkbox:checked').each(function() {
                        totalSelectedCost += parseFloat($(this).data('payable')) || 0;
                        selectedCount++;
                    });

                    const overallNetDue = parseFloat(currentSummaryTotals.net_due_payable) || 0;
                    let netDueForSelected = totalSelectedCost;
                    if (overallNetDue > 0 && totalSelectedCost > 0) {
                        netDueForSelected = Math.min(totalSelectedCost, overallNetDue);
                    }
                    netDueForSelected = Math.round(netDueForSelected * 100) / 100;

                    $('#settleBtnLabel').text('৳' + netDueForSelected.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#settleModalAmountInput').val(netDueForSelected);
                    $('#modalSelectedCostText').text('৳' + totalSelectedCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalNetDueText').text('৳' + netDueForSelected.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                    if (netDueForSelected <= 0 || selectedCount === 0) {
                        $('#settleModalBtn').prop('disabled', true);
                    } else {
                        $('#settleModalBtn').prop('disabled', false);
                    }
                }

                // -------------------------------------------------------------
                // 100% Pure AJAX Filtering & Data Loading
                // -------------------------------------------------------------
                function loadPayOnSaleData() {
                    const supplierId = $('#supplierSelect').val();
                    const search = $('#productSearchInput').val();
                    const startDate = $('#startDateInput').val();
                    const endDate = $('#endDateInput').val();

                    if (!supplierId) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Select Supplier',
                                text: 'Please select a supplier to view and filter Pay-on-Sale settlement data.',
                                confirmButtonColor: 'var(--primary-color)'
                            });
                        }
                        $('#dataContainer').addClass('d-none');
                        $('#emptySupplierContainer').removeClass('d-none');
                        $('#resetFiltersBtn').addClass('d-none');
                        return;
                    }

                    // Show reset button if any filter is active
                    if (search || startDate || endDate || supplierId) {
                        $('#resetFiltersBtn').removeClass('d-none');
                    } else {
                        $('#resetFiltersBtn').addClass('d-none');
                    }

                    // Show loading UI
                    $('#filterSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Filtering...');
                    $('#tableLoadingOverlay').removeClass('d-none');
                    $('#dataContainer').removeClass('d-none');
                    $('#emptySupplierContainer').addClass('d-none');

                    // Update browser URL without reloading page
                    const params = new URLSearchParams();
                    if (supplierId) params.set('supplier_id', supplierId);
                    if (search) params.set('search', search);
                    if (startDate) params.set('start_date', startDate);
                    if (endDate) params.set('end_date', endDate);
                    const newUrl = window.location.pathname + '?' + params.toString();
                    window.history.pushState(null, '', newUrl);

                    $.ajax({
                        url: "{{ route('supplier-payments.pay-on-sale-summary') }}",
                        type: "GET",
                        data: {
                            supplier_id: supplierId,
                            search: search,
                            start_date: startDate,
                            end_date: endDate
                        },
                        success: function(response) {
                            currentSummaryTotals = response.totals || {};

                            // 1. Update Metric Cards
                            $('#sumPurchasedQty').text(Number(currentSummaryTotals.purchased_qty || 0).toLocaleString());
                            $('#sumSoldQty').text(Number(currentSummaryTotals.sold_qty || 0).toLocaleString());
                            $('#sumInStockQty').text(Number(currentSummaryTotals.in_stock_qty || 0).toLocaleString());
                            $('#sumSoldCost').text(Number(currentSummaryTotals.sold_cost_payable || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#sumTotalPaid').text(Number(currentSummaryTotals.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#sumNetDue').text(Number(currentSummaryTotals.net_due_payable || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#modalTotalPaidText').text('৳' + Number(currentSummaryTotals.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                            // 2. Update Modal Hidden Inputs
                            $('#settleModalSupplierId').val(supplierId);

                            // 3. Render Table Rows
                            const items = response.items || [];
                            $('#totalSupplierItemsCount').text(items.length);
                            let rowsHtml = '';

                            if (items.length === 0) {
                                rowsHtml = `<tr id="emptyRow">
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2 text-muted opacity-50 d-block"></i>
                                        No purchase or sales data found matching your filter criteria.
                                    </td>
                                </tr>`;
                            } else {
                                items.forEach(function(item, idx) {
                                    const netDue = parseFloat(item.net_due) || 0;
                                    const soldCost = parseFloat(item.sold_cost_payable) || 0;
                                    const payable = netDue > 0 ? netDue : soldCost;
                                    const isMuted = payable <= 0;
                                    const isChecked = payable > 0;
                                    const isDisabled = payable <= 0;

                                    let badgeStatus = '';
                                    if (netDue <= 0 && soldCost > 0) {
                                        badgeStatus = `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 extra-small mt-1"><i class="fas fa-check-circle me-1"></i>Fully Settled</span>`;
                                    } else if (soldCost <= 0) {
                                        badgeStatus = `<span class="badge bg-light text-muted border extra-small mt-1">No Sales Yet</span>`;
                                    }

                                    rowsHtml += `<tr class="product-row ${isMuted ? 'bg-light bg-opacity-25 opacity-75' : ''}"
                                        data-name="${(item.product_name || '').toLowerCase()}"
                                        data-sku="${(item.sku || '').toLowerCase()}"
                                        data-style="${(item.style_number || '').toLowerCase()}">
                                        <td class="ps-3">
                                            <input type="checkbox" class="form-check-input item-checkbox" data-name="${item.product_name}" data-payable="${payable}" ${isChecked ? 'checked' : ''} ${isDisabled ? 'disabled' : ''}>
                                        </td>
                                        <td class="row-index"><span class="badge bg-light text-dark border">${idx + 1}</span></td>
                                        <td>
                                            <div class="fw-bold text-dark item-title">${item.product_name}</div>
                                            ${badgeStatus}
                                        </td>
                                        <td>
                                            <div class="small text-muted item-sku">SKU: ${item.sku || 'N/A'}</div>
                                            <div class="extra-small text-muted item-style">Style: ${item.style_number || 'N/A'}</div>
                                        </td>
                                        <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">${Number(item.purchased_qty || 0).toLocaleString()}</span></td>
                                        <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">${Number(item.sold_qty || 0).toLocaleString()}</span></td>
                                        <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3">${Number(item.in_stock_qty || 0).toLocaleString()}</span></td>
                                        <td class="text-end fw-bold">৳${Number(item.unit_cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td class="text-end fw-bold text-dark">৳${Number(item.sold_cost_payable || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td class="text-end pe-3 fw-bold ${netDue > 0 ? 'text-success' : 'text-muted'}">৳${netDue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>`;
                                });
                            }

                            rowsHtml += `<tr id="noMatchRow" class="d-none">
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fas fa-search fa-2x mb-2 text-muted opacity-50 d-block"></i>
                                    No products match your search query.
                                </td>
                            </tr>`;

                            $('#tableBody').html(rowsHtml);

                            // Re-filter table rows and reset pagination
                            filterTableRows();
                            updateSelectedPayableSummary();
                        },
                        error: function(xhr) {
                            alert('Error loading supplier settlement data: ' + (xhr.responseJSON?.message || xhr.statusText));
                        },
                        complete: function() {
                            $('#filterSubmitBtn').prop('disabled', false).html('<i class="fas fa-filter me-1"></i>Filter');
                            $('#tableLoadingOverlay').addClass('d-none');
                        }
                    });
                }

                // Explicit Filter Button Click (Zero reload)
                $('#filterSubmitBtn').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    loadPayOnSaleData();
                });

                // Handle Filter form submission via AJAX (Zero reload)
                $('#payOnSaleFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    loadPayOnSaleData();
                    return false;
                });

                // Enter key in filter inputs triggers AJAX filter
                $('#productSearchInput, #startDateInput, #endDateInput').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        e.stopPropagation();
                        loadPayOnSaleData();
                    }
                });

                // Auto load on supplier change (Zero reload)
                $('#supplierSelect').on('change select2:select', function() {
                    loadPayOnSaleData();
                });

                // Reset filters via AJAX (Zero reload)
                $('#resetFiltersBtn').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#productSearchInput').val('');
                    $('#startDateInput').val('');
                    $('#endDateInput').val('');
                    loadPayOnSaleData();
                });

                // Checkbox controls
                $('#selectAllItems').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('.product-row:not(.d-none) .item-checkbox:not(:disabled)').prop('checked', isChecked);
                    updateSelectedPayableSummary();
                });

                $(document).on('change', '.item-checkbox', function() {
                    const activeCount = $('.product-row:not(.d-none) .item-checkbox:not(:disabled)').length;
                    const checkedCount = $('.product-row:not(.d-none) .item-checkbox:checked').length;
                    $('#selectAllItems').prop('checked', activeCount > 0 && activeCount === checkedCount);
                    updateSelectedPayableSummary();
                });

                // -------------------------------------------------------------
                // Client-Side Table Pagination System
                // -------------------------------------------------------------
                let currentPage = 1;
                let pageSize = parseInt($('#pageSizeSelect').val()) || 25;
                let matchedRows = $('.product-row');

                function renderTablePagination() {
                    matchedRows = $('.product-row:not(#emptyRow):not(#noMatchRow)');
                    const totalMatched = matchedRows.length;
                    $('#visibleItemCount').text(totalMatched);
                    $('#totalMatchedText').text(totalMatched);

                    if (totalMatched === 0) {
                        $('#paginationContainer').addClass('d-none');
                        return;
                    }

                    $('#paginationContainer').removeClass('d-none');

                    if (pageSize === -1) {
                        // Show all
                        matchedRows.removeClass('d-none');
                        $('#pageRangeText').text(`1-${totalMatched}`);
                        $('#paginationNav').empty();
                        return;
                    }

                    const totalPages = Math.ceil(totalMatched / pageSize);
                    if (currentPage > totalPages) currentPage = totalPages;
                    if (currentPage < 1) currentPage = 1;

                    const startIndex = (currentPage - 1) * pageSize;
                    const endIndex = Math.min(startIndex + pageSize, totalMatched);

                    matchedRows.addClass('d-none');
                    matchedRows.slice(startIndex, endIndex).removeClass('d-none');

                    $('#pageRangeText').text(`${startIndex + 1}-${endIndex}`);

                    // Build pagination buttons
                    let navHtml = '';
                    navHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link page-prev" href="javascript:void(0)">&laquo;</a>
                    </li>`;

                    const maxButtons = 5;
                    let startPage = Math.max(1, currentPage - 2);
                    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                    if (endPage - startPage < maxButtons - 1) {
                        startPage = Math.max(1, endPage - maxButtons + 1);
                    }

                    for (let p = startPage; p <= endPage; p++) {
                        navHtml += `<li class="page-item ${p === currentPage ? 'active' : ''}">
                            <a class="page-link page-num" href="javascript:void(0)" data-page="${p}">${p}</a>
                        </li>`;
                    }

                    navHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link page-next" href="javascript:void(0)">&raquo;</a>
                    </li>`;

                    $('#paginationNav').html(navHtml);
                }

                function filterTableRows() {
                    currentPage = 1;
                    renderTablePagination();
                    
                    const activeCount = $('.product-row:not(.d-none) .item-checkbox:not(:disabled)').length;
                    const checkedCount = $('.product-row:not(.d-none) .item-checkbox:checked').length;
                    $('#selectAllItems').prop('checked', activeCount > 0 && activeCount === checkedCount);
                }

                $('#pageSizeSelect').on('change', function() {
                    pageSize = parseInt($(this).val()) || 25;
                    currentPage = 1;
                    renderTablePagination();
                });

                $(document).on('click', '.page-num', function() {
                    currentPage = parseInt($(this).data('page'));
                    renderTablePagination();
                });

                $(document).on('click', '.page-prev', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        renderTablePagination();
                    }
                });

                $(document).on('click', '.page-next', function() {
                    const totalPages = Math.ceil(matchedRows.length / pageSize);
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTablePagination();
                    }
                });

                // Initial run
                renderTablePagination();
                updateSelectedPayableSummary();
            });
        </script>
    @endpush
@endsection
