@extends('erp.master')

@section('title', 'Sale List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .select2-container--bootstrap-5 .select2-selection { 
                font-size: 0.85rem; 
                min-height: 38px;
                display: flex;
                align-items: center;
                border: 1px solid #dee2e6 !important;
                border-radius: 8px !important;
            }
            .form-label { font-size: 0.85rem; font-weight: 700; color: #374151; }
            .table-report thead th { 
                background: #2d5a4c; 
                color: #fff; 
                font-size: 0.65rem; 
                font-weight: 700; 
                text-transform: uppercase; 
                padding: 10px 5px; 
                white-space: nowrap;
                vertical-align: middle;
                border: 1px solid #3d6a5c;
                text-align: center;
            }
            .table-report tbody td { 
                font-size: 0.75rem; 
                vertical-align: middle; 
                padding: 6px 4px;
                border: 1px solid #dee2e6;
            }
            .table-report tfoot td {
                font-weight: 800;
                background: #f8f9fa;
                font-size: 0.8rem;
            }
            .product-img {
                width: 35px;
                height: 35px;
                object-fit: cover;
                border-radius: 4px;
                border: 1px solid #eee;
            }
            .badge-status {
                font-size: 0.65rem;
                padding: 3px 8px;
                border-radius: 50px;
            }
            /* Custom Scrollbar for wide table */
            .table-responsive::-webkit-scrollbar { height: 8px; }
            .table-responsive::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
        </style>

        <div class="container-fluid px-2 py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Sales List</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm px-3"><i class="fas fa-file-excel me-1"></i> Export Excel</button>
                    <a href="{{ route('pos.add') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                        <i class="fas fa-plus me-1"></i> New Sale
                    </a>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <form action="{{ route('pos.list') }}" method="GET" id="filterForm">
                        <div class="mb-3">
                            <div class="d-flex gap-4">
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="dailyReport">Daily Reports</label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="monthlyReport">Monthly Reports</label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="yearlyReport">Yearly Reports</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small mb-1">Start Date *</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small mb-1">End Date *</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 date-group monthly-group" style="display: none;">
                                <label class="form-label small mb-1">Month *</label>
                                <select name="month" class="form-select form-select-sm select2">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 date-group monthly-group yearly-group" style="display: none;">
                                <label class="form-label small mb-1">Year *</label>
                                <select name="year" class="form-select form-select-sm select2">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Invoice *</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="All Invoice" value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Customer *</label>
                                <select name="customer_id" class="form-select form-select-sm select2" data-placeholder="All Customer">
                                    <option value=""></option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Product *</label>
                                <select name="product_id" class="form-select form-select-sm select2" data-placeholder="All Product">
                                    <option value=""></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Style Number *</label>
                                <input type="text" name="style_number" class="form-control form-control-sm" placeholder="All Style Number" value="{{ request('style_number') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Category *</label>
                                <select name="category_id" class="form-select form-select-sm select2" data-placeholder="All Category">
                                    <option value=""></option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Brand *</label>
                                <select name="brand_id" class="form-select form-select-sm select2" data-placeholder="All Brand">
                                    <option value=""></option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Season *</label>
                                <select name="season_id" class="form-select form-select-sm select2" data-placeholder="All Season">
                                    <option value=""></option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Gender *</label>
                                <select name="gender_id" class="form-select form-select-sm select2" data-placeholder="All Gender">
                                    <option value=""></option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small mb-1">Select Account *</label>
                                <select name="account" class="form-select form-select-sm select2" data-placeholder="All Account">
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-info text-white btn-sm w-100 fw-bold border-0 shadow-sm" style="background-color: #17a2b8; height: 31px;">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <div class="btn-group shadow-sm">
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('csv')">CSV</button>
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('excel')">Excel</button>
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('pdf')">PDF</button>
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('print')">Print</button>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="small text-muted">Total: <strong>{{ $items->total() }}</strong> Records</span>
                                <a href="{{ route('pos.list') }}" class="btn btn-light btn-sm px-3 border shadow-sm">Clear All</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-report table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Serial No</th>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Created</th>
                                    <th>Image</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th>Product Name</th>
                                    <th>Style Number</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Sales Qty</th>
                                    <th>Total S-Qty</th>
                                    <th>Sales Amount</th>
                                    <th>Total Sales Amount</th>
                                    <th>Sales Return Qty</th>
                                    <th>Total SR-Qty</th>
                                    <th>Sales Return Amount</th>
                                    <th>Total Sales Return Amount</th>
                                    <th>Actual Sales Qty</th>
                                    <th>Total AS-Qty</th>
                                    <th>Delivery Charge Amount</th>
                                    <th>Discount Amount</th>
                                    <th>Exchange Amount</th>
                                    <th>Actual Sales Amount</th>
                                    <th>Total</th>
                                    <th>Total Received Amount</th>
                                    <th>Total Due Amount</th>
                                    <th>Option</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $gSellQty = 0; $gSellAmt = 0;
                                    $gRetQty = 0; $gRetAmt = 0;
                                    $gActQty = 0; $gActAmt = 0;
                                    $gDelivery = 0; $gDiscount = 0; $gExchange = 0;
                                    $gFinalTotal = 0; $gReceived = 0; $gDue = 0;
                                @endphp
                                @forelse($items as $index => $item)
                                    @php
                                        $sale = $item->pos;
                                        $invoice = $sale->invoice;
                                        $product = $item->product;
                                        $variation = $item->variation;
                                        
                                        $color = '-'; $size = '-';
                                        if ($variation && $variation->attributeValues) {
                                            foreach($variation->attributeValues as $val) {
                                                $attrName = strtolower($val->attribute->name ?? '');
                                                if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) $color = $val->value;
                                                elseif (str_contains($attrName, 'size')) $size = $val->value;
                                            }
                                        }

                                        $retQty = $item->returnItems->sum('returned_qty');
                                        $retAmt = $item->returnItems->sum('total_price');
                                        
                                        $actualQty = $item->quantity - $retQty;
                                        $actualAmt = $item->total_price - $retAmt;

                                        $gSellQty += $item->quantity; $gSellAmt += $item->total_price;
                                        $gRetQty += $retQty; $gRetAmt += $retAmt;
                                        $gActQty += $actualQty; $gActAmt += $actualAmt;

                                        // Only add invoice-level values once per invoice
                                        $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                        <td class="fw-bold">{{ $sale->sale_number ?? '-' }}</td>
                                        <td class="text-center">{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                        <td>{{ $sale->soldBy->name ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($product && $product->image)
                                                <img src="{{ asset('storage/'.$product->image) }}" class="product-img" alt="IMG">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width:35px;height:35px;"><i class="fas fa-image text-muted x-small"></i></div>
                                            @endif
                                        </td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td>{{ $product->brand->name ?? '-' }}</td>
                                        <td>{{ $product->season->name ?? '-' }}</td>
                                        <td>{{ $product->gender->name ?? '-' }}</td>
                                        <td style="min-width: 120px;">{{ $product->name ?? '-' }}</td>
                                        <td>{{ $product->style_number ?? '-' }}</td>
                                        <td>{{ $color }}</td>
                                        <td>{{ $size }}</td>
                                        
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                                        
                                        <td class="text-center text-danger">{{ $retQty ?: 0 }}</td>
                                        <td class="text-center text-danger fw-bold">{{ $retQty ?: 0 }}</td>
                                        <td class="text-end text-danger">{{ number_format($retAmt, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($retAmt, 2) }}</td>
                                        
                                        <td class="text-center text-success">{{ $actualQty }}</td>
                                        <td class="text-center text-success fw-bold">{{ $actualQty }}</td>
                                        
                                        <!-- Delivery, Discount, Exchange - Per Invoice -->
                                        <td class="text-end">
                                            @if($isFirst)
                                                {{ number_format($sale->delivery, 2) }}
                                                @php $gDelivery += $sale->delivery; @endphp
                                            @else - @endif
                                        </td>
                                        <td class="text-end">
                                            @if($isFirst)
                                                {{ number_format($sale->discount, 2) }}
                                                @php $gDiscount += $sale->discount; @endphp
                                            @else - @endif
                                        </td>
                                        <td class="text-end text-info">
                                            @if($isFirst)
                                                {{ number_format($sale->exchange_amount ?? 0, 2) }}
                                                @php $gExchange += ($sale->exchange_amount ?? 0); @endphp
                                            @else - @endif
                                        </td>

                                        <td class="text-end text-success fw-bold">{{ number_format($actualAmt, 2) }}</td>
                                        
                                        <td class="text-end fw-bold">
                                            @if($isFirst)
                                                {{ number_format($sale->total_amount, 2) }}
                                                @php $gFinalTotal += $sale->total_amount; @endphp
                                            @else - @endif
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            @if($isFirst)
                                                @php $paid = $invoice->paid_amount ?? 0; $gReceived += $paid; @endphp
                                                {{ number_format($paid, 2) }}
                                            @else - @endif
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            @if($isFirst)
                                                @php $due = $invoice->due_amount ?? 0; $gDue += $due; @endphp
                                                {{ number_format($due, 2) }}
                                            @else - @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('pos.show', $sale->id) }}" class="btn btn-sm btn-info text-white px-2 py-1"><i class="fas fa-eye small"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="32" class="text-center py-5 text-muted">No records found</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="14" class="text-end">Grand Total</td>
                                    <td class="text-center">{{ $gSellQty }}</td>
                                    <td class="text-center">{{ $gSellQty }}</td>
                                    <td class="text-end">{{ number_format($gSellAmt, 2) }}</td>
                                    <td class="text-end">{{ number_format($gSellAmt, 2) }}</td>
                                    <td class="text-center">{{ $gRetQty }}</td>
                                    <td class="text-center">{{ $gRetQty }}</td>
                                    <td class="text-end">{{ number_format($gRetAmt, 2) }}</td>
                                    <td class="text-end">{{ number_format($gRetAmt, 2) }}</td>
                                    <td class="text-center">{{ $gActQty }}</td>
                                    <td class="text-center">{{ $gActQty }}</td>
                                    <td class="text-end">{{ number_format($gDelivery, 2) }}</td>
                                    <td class="text-end">{{ number_format($gDiscount, 2) }}</td>
                                    <td class="text-end">{{ number_format($gExchange, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($gActAmt, 2) }}</td>
                                    <td class="text-end">{{ number_format($gFinalTotal, 2) }}</td>
                                    <td class="text-end">{{ number_format($gReceived, 2) }}</td>
                                    <td class="text-end">{{ number_format($gDue, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @if($items->hasPages())
                <div class="card-footer bg-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries</small>
                        {{ $items->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Select2 & jQuery -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });

            const reportRadios = document.querySelectorAll('input[name="report_type"]');
            function toggleDateGroups() {
                const type = document.querySelector('input[name="report_type"]:checked').value;
                document.querySelectorAll('.date-group').forEach(el => el.style.display = 'none');
                
                if (type === 'daily') {
                    document.querySelectorAll('.daily-group').forEach(el => el.style.display = 'block');
                } else if (type === 'monthly') {
                    document.querySelectorAll('.monthly-group').forEach(el => el.style.display = 'block');
                } else if (type === 'yearly') {
                    document.querySelectorAll('.yearly-group').forEach(el => el.style.display = 'block');
                }
            }
            reportRadios.forEach(radio => radio.addEventListener('change', toggleDateGroups));
            toggleDateGroups();
        });

        function exportData(format) {
            const form = document.getElementById('filterForm');
            const originalAction = form.action;
            const originalTarget = form.target;

            if (format === 'excel' || format === 'csv') {
                form.action = "{{ route('pos.export.excel') }}";
                form.target = "_blank";
                form.submit();
            } else if (format === 'pdf') {
                form.action = "{{ route('pos.export.pdf') }}";
                form.target = "_blank";
                form.submit();
            } else if (format === 'print') {
                form.action = "{{ route('pos.export.pdf') }}";
                form.target = "_blank";
                // Add hidden action=print input
                let input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", "action");
                input.setAttribute("value", "print");
                form.appendChild(input);
                form.submit();
                // cleanup
                form.removeChild(input);
            }

            // Restore original form state
            form.action = originalAction;
            form.target = originalTarget;
        }
    </script>
@endsection