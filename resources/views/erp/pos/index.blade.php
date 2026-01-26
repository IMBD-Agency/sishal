@extends('erp.master')

@section('title', 'Sales History')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

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
            <div class="card-header bg-white border-bottom p-4">
                 <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Advanced Search Filter</h6>
                    <!-- Report Period Toggles -->
                    <div class="d-flex gap-3">
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="dailyReport">Custom Range</label>
                        </div>
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthlyReport">Monthly</label>
                        </div>
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearlyReport">Yearly</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('pos.list') }}" method="GET" id="filterForm">
                    <div class="row g-2 align-items-end mb-2">
                        <!-- Date Filters -->
                        <div class="col-md-2 daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2 daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2 monthly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 monthly-group yearly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @foreach(range(date('Y'), date('Y') - 10) as $y)
                                    <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                         <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Invoice No</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Invoice..." name="search" value="{{ request('search') }}">
                        </div>
                         <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Customer</label>
                            <select name="customer_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                <option value="">All</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                             <select name="branch_id" class="form-select form-select-sm select2-simple">
                                <option value="">All</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div class="col-md-2">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                             <select name="category_id" class="form-select form-select-sm select2-simple">
                                <option value="">All</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                             </select>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                             <select name="brand_id" class="form-select form-select-sm select2-simple">
                                <option value="">All</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div class="col-md-2">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                             <select name="season_id" class="form-select form-select-sm select2-simple">
                                <option value="">All</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div class="col-md-2">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                             <select name="gender_id" class="form-select form-select-sm select2-simple">
                                <option value="">All</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div class="col-md-2">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style #</label>
                             <input type="text" name="style_number" class="form-control form-control-sm" placeholder="Style..." value="{{ request('style_number') }}">
                        </div>
                        <div class="col-md-3">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">Specific Product</label>
                             <select name="product_id" class="form-select form-select-sm select2-simple">
                                <option value="">All Tools</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div class="col-md-1 d-flex gap-1 align-items-end">
                             <a href="{{ route('pos.list') }}" class="btn btn-light border btn-sm flex-fill" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                            <button type="submit" class="btn btn-create-premium btn-sm flex-fill" style="height: 31px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="premium-card shadow-sm">
             <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                     <button onclick="exportData('excel')" class="btn btn-outline-dark btn-sm shadow-sm"><i class="fas fa-file-excel me-1"></i> Excel</button>
                     <button onclick="exportData('pdf')" class="btn btn-outline-dark btn-sm shadow-sm"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table compact-reporting-table table-bordered mb-0">
                        <thead class="bg-light">
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
                                    <td>{{ $sale->soldBy->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="thumbnail-box" style="width: 30px; height: 30px; margin: 0 auto;">
                                             @if($product && $product->image)
                                                <img src="{{ asset('storage/'.$product->image) }}" alt="img">
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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