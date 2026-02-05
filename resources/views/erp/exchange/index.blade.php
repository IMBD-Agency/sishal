@extends('erp.master')

@section('title', 'Exchange List')

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
                            <li class="breadcrumb-item active text-primary fw-600">Exchange</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Exchange List</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('exchange.create') }}" class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-plus me-2"></i>New Exchange
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <form action="{{ route('exchange.list') }}" method="GET" id="filterForm">
                        <div class="mb-3">
                            <div class="d-flex gap-4">
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="dailyReport">Daily Reports</label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="monthlyReport">Monthly Reports</label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="yearlyReport">Yearly Reports</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                             <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 date-group monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select form-select-sm select2-simple">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 date-group monthly-group yearly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select form-select-sm select2-simple">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Anything</label>
                                <input type="text" name="search" class="form-control form-control-sm border-primary" placeholder="Sale #, Customer, Product..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Customer</label>
                                <select name="customer_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                                <select name="branch_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select name="category_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                                <select name="brand_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                                <select name="season_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                                <select name="gender_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
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
                                <select name="product_id" class="form-select form-select-sm select2-simple" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end gap-1">
                                <a href="{{ route('exchange.list') }}" class="btn btn-light border btn-sm flex-fill" title="Reset">
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

            <!-- Table -->
            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-exchange-alt me-2 text-primary"></i>Product Exchange List</h6>
                    <div class="d-flex align-items-center gap-2 ms-auto" style="max-width: 400px; width: 100%;">
                <div class="search-wrapper-premium">
                    <input type="text" id="exchangeSearch" class="form-control rounded-pill search-input-premium" placeholder="Quick find in this registry...">
                    <i class="fas fa-search search-icon-premium"></i>
                </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact-reporting-table mb-0" id="exchangeTable">
                            <thead style="background-color: #4a7c59 !important; color: white !important;">
                                <tr>
                                    <th class="text-center">Serial No</th>
                                    <th>Exchange Invoice No</th>
                                    <th>Sale Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th class="text-center">Image</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th style="min-width: 140px;">Product Name</th>
                                    <th>Style Number</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Exchange</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $tExchange = 0; $tDiscount = 0; $tPaid = 0; $tDue = 0;
                                @endphp
                                @forelse($items as $index => $item)
                                    @php
                                        $sale = $item->pos;
                                        $product = $item->product;
                                        $variation = $item->variation;
                                        $invoice = $sale->invoice;

                                        $color = '-'; $size = '-';
                                        if ($variation && $variation->attributeValues) {
                                            foreach($variation->attributeValues as $val) {
                                                $attrName = strtolower($val->attribute->name ?? '');
                                                if (str_contains($attrName, 'color')) $color = $val->value;
                                                elseif (str_contains($attrName, 'size')) $size = $val->value;
                                            }
                                        }

                                        $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);
                                        if($isFirst) {
                                            $tExchange += $sale->exchange_amount;
                                            $tDiscount += $sale->discount;
                                            $tPaid += ($invoice->paid_amount ?? 0);
                                            $tDue += ($invoice->due_amount ?? 0);
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $items->firstItem() + $index }}</td>
                                        <td class="fw-bold text-dark">{{ $sale->sale_number }}</td>
                                        <td>{{ $sale->originalPos->sale_number ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                        <td class="text-center">
                                            @if($product->image)
                                                <img src="{{ asset('storage/'.$product->image) }}" width="30" height="30" class="rounded shadow-sm">
                                            @endif
                                        </td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td>{{ $product->brand->name ?? '-' }}</td>
                                        <td>{{ $product->season->name ?? '-' }}</td>
                                        <td>{{ $product->gender->name ?? '-' }}</td>
                                        <td class="fw-bold">{{ $product->name }}</td>
                                        <td>{{ $product->style_number }}</td>
                                        <td>{{ $color }}</td>
                                        <td>{{ $size }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ $isFirst ? number_format($sale->exchange_amount, 2) : '' }}</td>
                                        <td class="text-end">{{ $isFirst ? number_format($sale->discount, 2) : '' }}</td>
                                        <td class="text-end">{{ $isFirst ? number_format($invoice->paid_amount ?? 0, 2) : '' }}</td>
                                        <td class="text-end">{{ $isFirst ? number_format($invoice->due_amount ?? 0, 2) : '' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('pos.show', $sale->id) }}" class="btn btn-action btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="20" class="text-center py-5 text-muted">No records found</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="fw-bold text-dark">
                                    <td colspan="15" class="text-end">Total Amount</td>
                                    <td class="text-end">{{ number_format($tExchange, 2) }}</td>
                                    <td class="text-end">{{ number_format($tDiscount, 2) }}</td>
                                    <td class="text-end">{{ number_format($tPaid, 2) }}</td>
                                    <td class="text-end">{{ number_format($tDue, 2) }}</td>
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

@push('scripts')
<script>
    $(document).ready(function() {
        const reportRadios = document.querySelectorAll('input[name="report_type"]');
        function toggleDateGroups() {
            const type = document.querySelector('input[name="report_type"]:checked').value;
             $('.date-group').hide();
            
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
        let exchangeTimeout;
        $('#exchangeSearch').on('input', function() {
            const value = $(this).val().toLowerCase();
            clearTimeout(exchangeTimeout);
            
            exchangeTimeout = setTimeout(function() {
                $('#exchangeTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            }, 300);
        });
    });
</script>
@endpush
@endsection
