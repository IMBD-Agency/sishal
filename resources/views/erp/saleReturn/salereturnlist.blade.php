@extends('erp.master')

@section('title', 'Sale Return List')

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
                            <li class="breadcrumb-item active text-primary fw-600">Return History</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Full Sale Return Report</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button type="button" class="btn btn-light fw-bold shadow-sm" onclick="exportData('excel')">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                    <a href="{{ route('saleReturn.create') }}" class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-plus me-2"></i>New Return
                    </a>
                </div>
            </div>
        </div>

            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <form action="{{ route('saleReturn.list') }}" method="GET" id="filterForm">
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
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date *</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date *</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 date-group monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month *</label>
                                <select name="month" class="form-select form-select-sm select2">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 date-group monthly-group yearly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year *</label>
                                <select name="year" class="form-select form-select-sm select2">
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
                                <select name="customer_id" class="form-select form-select-sm select2-setup" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Product</label>
                                <select name="product_id" class="form-select form-select-sm select2-setup" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style #</label>
                                <input type="text" name="style_number" class="form-control form-control-sm" placeholder="Style..." value="{{ request('style_number') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select name="category_id" class="form-select form-select-sm select2-setup" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                                <select name="brand_id" class="form-select form-select-sm select2-setup" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                                <select name="season_id" class="form-select form-select-sm select2-setup" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                                <select name="gender_id" class="form-select form-select-sm select2-setup" data-placeholder="All">
                                    <option value=""></option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-create-premium btn-sm w-100" style="height: 31px;">
                                    <i class="fas fa-search small"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <div class="btn-group shadow-sm">
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('csv')">CSV</button>
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('excel')">Excel</button>
                                <button type="button" class="btn btn-dark btn-sm px-3" onclick="exportData('pdf')">PDF</button>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="small text-muted">Records: <strong>{{ $items->total() }}</strong></span>
                                <a href="{{ route('saleReturn.list') }}" class="btn btn-light btn-sm px-3 border shadow-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list me-2 text-primary"></i>Return Data List</h6>
                    <div class="search-wrapper-premium">
                        <input type="text" id="returnSearch" class="form-control rounded-pill search-input-premium" placeholder="Quick find in this registry...">
                        <i class="fas fa-search search-icon-premium"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact-reporting-table mb-0" id="returnTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">SL</th>
                                    <th>Date</th>
                                    <th>R-Inv No</th>
                                    <th>S-Inv No</th>
                                    <th>Customer</th>
                                    <th>Mobile</th>
                                    <th>Outlet</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th style="min-width: 140px;">Product Name</th>
                                    <th>Style #</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">T.Qty</th>
                                    <th class="text-end">T.Amount</th>
                                    <th class="text-end">Charge</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $gQty = 0; $gAmt = 0;
                                @endphp
                                @forelse($items as $index => $item)
                                    @php
                                        $return = $item->saleReturn;
                                        $product = $item->product;
                                        $variation = $item->variation;
                                        if (!$return) continue;

                                        $color = '-'; $size = '-';
                                        if ($variation && $variation->attributeValues) {
                                            foreach($variation->attributeValues as $val) {
                                                $attrName = strtolower($val->attribute->name ?? '');
                                                if (str_contains($attrName, 'color')) $color = $val->value;
                                                elseif (str_contains($attrName, 'size')) $size = $val->value;
                                            }
                                        }

                                        $gQty += $item->returned_qty;
                                        $gAmt += $item->total_price;
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $items->firstItem() + $index }}</td>
                                        <td class="text-center">{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') : '-' }}</td>
                                        <td class="fw-bold text-dark">#SR-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                             <a href="{{ route('pos.show', $return->pos_sale_id) }}" class="text-decoration-none text-primary fw-600">
                                                {{ $return->posSale->sale_number ?? '-' }}
                                             </a>
                                        </td>
                                        <td>{{ $return->customer->name ?? 'Walk-in' }}</td>
                                        <td>{{ $return->customer->phone ?? '-' }}</td>
                                        <td>{{ $return->branch->name ?? '-' }}</td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td>{{ $product->brand->name ?? '-' }}</td>
                                        <td>{{ $product->season->name ?? '-' }}</td>
                                        <td>{{ $product->gender->name ?? '-' }}</td>
                                        <td>{{ $product->name ?? '-' }}</td>
                                        <td>{{ $product->style_number ?? '-' }}</td>
                                        <td>{{ $color }}</td>
                                        <td>{{ $size }}</td>
                                        <td class="text-center">{{ $item->returned_qty }}</td>
                                        <td class="text-center fw-bold">{{ $item->returned_qty }}</td>
                                        <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                                        <td class="text-end">0.00</td>
                                        <td class="text-end">0.00</td>
                                        <td class="text-center">
                                            <a href="{{ route('saleReturn.show', $return->id) }}" class="btn btn-action btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="21" class="text-center py-5 text-muted">No records found</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="fw-bold text-dark text-uppercase">
                                    <td colspan="15" class="text-end">Grand Totals</td>
                                    <td class="text-center">{{ $gQty }}</td>
                                    <td class="text-center">{{ $gQty }}</td>
                                    <td class="text-end">{{ number_format($gAmt, 2) }}</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-end">0.00</td>
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

    <!-- Select2 Configuration -->
    <script>
        $(document).ready(function() {

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

            // Quick Search Table Functionality with Debounce
            let returnSearchTimeout;
            $('#returnSearch').on('input', function() {
                const value = $(this).val().toLowerCase();
                clearTimeout(returnSearchTimeout);
                
                returnSearchTimeout = setTimeout(function() {
                    $('#returnTable tbody tr').filter(function() {
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
                form.action = "{{ route('saleReturn.export.excel') }}";
                form.target = "_blank";
                form.submit();
            } else if (format === 'pdf') {
                form.action = "{{ route('saleReturn.export.pdf') }}";
                form.target = "_blank";
                form.submit();
            } else if (format === 'print') {
                form.action = "{{ route('saleReturn.export.pdf') }}";
                form.target = "_blank";
                let input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", "action");
                input.setAttribute("value", "print");
                form.appendChild(input);
                form.submit();
                form.removeChild(input);
            }

            form.action = originalAction;
            form.target = originalTarget;
        }
    </script>
@endsection