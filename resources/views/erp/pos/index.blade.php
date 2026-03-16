@extends('erp.master')

@section('title', 'Sales History')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')
    
    <style>
        /* Premium Sticky Header & Horizontal Scroll Fix for Sales Report */
        
        /* 1. Maintain card containment to fix layout breakage */
        .premium-card {
            overflow: hidden !important;
            border: 1px solid #edf2f7;
        }

        /* 2. Create an internal scrolling area for the table */
        .table-responsive {
            max-height: 80vh; /* Large height to feel like page scroll */
            overflow: auto !important;
            position: relative;
            background: #fff;
        }

        /* 3. Stick headers to the top of the scrollable box */
        #salesTable {
            border-collapse: separate; /* Required for sticky header compatibility */
            border-spacing: 0;
            width: 100%;
        }

        #salesTable thead th {
            position: sticky;
            top: 0; /* Sticks to the top of .table-responsive */
            background-color: #2d5a4c !important; 
            color: #fff !important;
            z-index: 1000 !important;
            border-bottom: 2px solid #3d6a5c !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Subtle depth shadow */
            padding-top: 12px !important;
            padding-bottom: 12px !important;
        }

        /* 4. Fix for cell backgrounds to ensure they don't overlap shadows */
        #salesTable tbody td {
            background-color: #fff;
        }

        /* Custom Slim Scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        /* Maximize vertical view by making the header static on this page */
        .glass-header {
            position: relative !important;
            top: 0 !important;
            box-shadow: none !important;
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
            margin-bottom: 1rem !important;
        }
    </style>

    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Sales History</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Comprehensive Sales Report</h4>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                 <button class="btn btn-light fw-bold shadow-sm" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
                <a href="{{ route('pos.manual.create') }}" class="btn btn-create-premium text-nowrap">
                    <i class="fas fa-file-invoice me-2"></i>New Sale
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Filter Search</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('pos.list') }}" method="GET" id="filterForm">
                    <div class="mb-4">
                        <div class="d-flex gap-4">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Date Filters -->
                        <div class="col-md-2 daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2 daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2 monthly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                            <select name="month" class="form-select select2-simple">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 monthly-group yearly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                            <select name="year" class="form-select select2-simple">
                                @foreach(range(date('Y'), date('Y') - 10) as $y)
                                    <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                            <select name="branch_id" class="form-select select2-simple">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Customer</label>
                            <select name="customer_id" class="form-select select2-simple" data-placeholder="Select Customer">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                            <select name="category_id" class="form-select select2-simple">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                            <select name="brand_id" class="form-select select2-simple">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                            <select name="season_id" class="form-select select2-simple">
                                <option value="">All Seasons</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                            <select name="gender_id" class="form-select select2-simple">
                                <option value="">All Genders</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style #</label>
                            <input type="text" name="style_number" class="form-control" placeholder="Style Number" value="{{ request('style_number') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Quick Search</label>
                            <input type="text" class="form-control border-primary" placeholder="Sale #, Product Name, SKU..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light border-top p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3" onclick="exportData('excel')">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" onclick="exportData('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('pos.list') }}" class="btn btn-light border px-4 fw-bold text-muted" style="height: 42px; display: flex; align-items: center;">
                            <i class="fas fa-undo me-2"></i>Reset
                        </a>
                        <button type="submit" form="filterForm" class="btn btn-create-premium px-5" style="height: 42px;">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="premium-card shadow-sm">
             <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list me-2 text-primary"></i>Sales Registry</h6>
                </div>
                <div class="search-wrapper-premium">
                    <input type="text" id="salesSearch" class="form-control rounded-pill search-input-premium" placeholder="Quick find in this registry...">
                    <i class="fas fa-search search-icon-premium"></i>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table compact reporting-table table-bordered mb-0" id="salesTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="min-width: 40px;">#</th>
                                <th style="min-width: 100px;">Invoice</th>
                                <th style="min-width: 90px;">Date</th>
                                <th style="min-width: 120px;">Customer</th>
                                <th style="min-width: 100px;">Created By</th>
                                <th class="text-center">Img</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Season</th>
                                <th>Gender</th>
                                <th style="min-width: 150px;">Product Name</th>
                                <th>Style #</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-center bg-soft-primary">Sales Qty</th>
                                <th class="text-end bg-soft-primary">Sales Amt</th>
                                <th class="text-center bg-soft-danger">Ret Qty</th>
                                <th class="text-end bg-soft-danger">Ret Amt</th>
                                <th class="text-center bg-soft-success">Act Qty</th>
                                <th class="text-end bg-soft-success">Act Amt</th>
                                <th class="text-end">Delivery</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Exchange</th>
                                <th class="text-end fw-bold">Grand Total</th>
                                <th class="text-end text-success fw-bold">Paid</th>
                                <th class="text-end text-danger fw-bold">Due</th>
                                <th class="text-center">Action</th>
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
                                    $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);
                                    
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

                                    // Accumulate Totals
                                    $gSellQty += $item->quantity; 
                                    $gSellAmt += $item->total_price;
                                    $gRetQty += $retQty; 
                                    $gRetAmt += $retAmt;
                                    $gActQty += $actualQty; 
                                    $gActAmt += $actualAmt;

                                    if($isFirst) {
                                        $gDelivery += $sale->delivery;
                                        $gDiscount += $sale->discount;
                                        $gExchange += ($sale->exchange_amount ?? 0);
                                        $gFinalTotal += $sale->total_amount;
                                        $gReceived += ($invoice->paid_amount ?? 0);
                                        $gDue += ($invoice->due_amount ?? 0);
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $items->firstItem() + $index }}</td>
                                    <td>
                                        @if($isFirst)
                                            <a href="{{ route('pos.show', $sale->id) }}" class="text-decoration-none fw-bold text-primary hover-opacity-75">
                                                {{ $sale->sale_number ?? '-' }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                    <td>{{ $sale->soldBy ? trim($sale->soldBy->first_name . ' ' . $sale->soldBy->last_name) : '-' }}</td>
                                    <td class="text-center">
                                        <div class="thumbnail-box" style="width: 30px; height: 30px; margin: 0 auto;">
                                             @if($product && $product->image)
                                                <img src="{{ asset($product->image) }}" alt="">
                                             @else
                                                <i class="fas fa-cube text-muted opacity-50 small"></i>
                                             @endif
                                        </div>
                                    </td>
                                    <td>{{ $product->category->name ?? '-' }}</td>
                                    <td>{{ $product->brand->name ?? '-' }}</td>
                                    <td>{{ $product->season->name ?? '-' }}</td>
                                    <td>{{ $product->gender->name ?? '-' }}</td>
                                    <td class="fw-bold text-dark">{{ $product->name ?? '-' }}</td>
                                    <td>{{ $product->style_number ?? $product->sku ?? '-' }}</td>
                                    <td>{{ $color }}</td>
                                    <td>{{ $size }}</td>
                                    
                                    <td class="text-center bg-light">{{ $item->quantity }}</td>
                                    <td class="text-end bg-light">{{ number_format($item->total_price, 2) }}</td>
                                    
                                    <td class="text-center text-danger">{{ $retQty ?: '-' }}</td>
                                    <td class="text-end text-danger">{{ $retQty ? number_format($retAmt, 2) : '-' }}</td>
                                    
                                    <td class="text-center text-success fw-bold">{{ $actualQty }}</td>
                                    <td class="text-end text-success fw-bold">{{ number_format($actualAmt, 2) }}</td>
                                    
                                    <td class="text-end">
                                        @if($isFirst) {{ number_format($sale->delivery, 2) }} @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isFirst) {{ number_format($sale->discount, 2) }} @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isFirst) {{ number_format($sale->exchange_amount ?? 0, 2) }} @endif
                                    </td>
                                    
                                    <td class="text-end fw-bold">
                                         @if($isFirst) {{ number_format($sale->total_amount, 2) }} @endif
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                         @if($isFirst) {{ number_format($invoice->paid_amount ?? 0, 2) }} @endif
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                         @if($isFirst) 
                                            @if(($invoice->due_amount ?? 0) > 0)
                                                {{ number_format($invoice->due_amount, 2) }}
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size: 0.65rem;">Paid</span>
                                            @endif
                                         @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isFirst)
                                            <a href="{{ route('pos.show', $sale->id) }}" class="btn btn-action btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="27" class="text-center py-5 text-muted">No sales records found.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="14" class="text-end text-uppercase">Grand Total</td>
                                <td class="text-center">{{ $gSellQty }}</td>
                                <td class="text-end">{{ number_format($gSellAmt, 2) }}</td>
                                <td class="text-center text-danger">{{ $gRetQty }}</td>
                                <td class="text-end text-danger">{{ number_format($gRetAmt, 2) }}</td>
                                <td class="text-center text-success">{{ $gActQty }}</td>
                                <td class="text-end text-success">{{ number_format($gActAmt, 2) }}</td>
                                <td class="text-end">{{ number_format($gDelivery, 2) }}</td>
                                <td class="text-end">{{ number_format($gDiscount, 2) }}</td>
                                <td class="text-end">{{ number_format($gExchange, 2) }}</td>
                                <td class="text-end text-dark">{{ number_format($gFinalTotal, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($gReceived, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($gDue, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
             @if($items->hasPages())
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $items->firstItem() }} - {{ $items->lastItem() }} of {{ $items->total() }}</small>
                        {{ $items->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-simple').select2({
            width: '100%',
            dropdownParent: $('body')
        });

        const reportRadios = document.querySelectorAll('input[name="report_type"]');
        function toggleDateGroups() {
            const type = document.querySelector('input[name="report_type"]:checked').value;
             $('.daily-group, .monthly-group, .yearly-group').hide();
            
            if (type === 'daily') {
                $('.daily-group').show();
            } else if (type === 'monthly') {
                $('.monthly-group').show();
            } else if (type === 'yearly') {
                $('.yearly-group').show();
            }
        }
        reportRadios.forEach(radio => radio.addEventListener('change', toggleDateGroups));
        toggleDateGroups();

        // Quick Search Table Functionality with Debounce
        let salesSearchTimeout;
        $('#salesSearch').on('input', function() {
            const value = $(this).val().toLowerCase();
            clearTimeout(salesSearchTimeout);
            
            salesSearchTimeout = setTimeout(function() {
                $('#salesTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            }, 300);
        });
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
        } 

        // Restore
        form.action = originalAction;
        form.target = originalTarget;
    }
</script>
@endpush
@endsection