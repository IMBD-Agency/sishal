@extends('erp.master')

@section('title', 'Detailed Sale Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Detailed Sale Report</h2>
                    <p class="text-muted mb-0">Comprehensive analysis of POS and Ecommerce sales performance</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('reports.sale') }}" method="GET">
                        <div class="mb-4">
                            <div class="d-flex gap-2 report-type-toggle">
                                <div class="form-check small p-0 m-0">
                                    <input class="btn-check" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }} onclick="this.form.submit()">
                                    <label class="btn btn-sm rounded-pill px-3" for="dailyReport">Daily</label>
                                </div>
                                <div class="form-check small p-0 m-0">
                                    <input class="btn-check" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }} onclick="this.form.submit()">
                                    <label class="btn btn-sm rounded-pill px-3" for="monthlyReport">Monthly</label>
                                </div>
                                <div class="form-check small p-0 m-0">
                                    <input class="btn-check" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }} onclick="this.form.submit()">
                                    <label class="btn btn-sm rounded-pill px-3" for="yearlyReport">Yearly</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Daily Range -->
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase">Start Date *</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate->toDateString() }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase">End Date *</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate->toDateString() }}">
                            </div>

                            <!-- Monthly Range -->
                            <div class="col-md-2 date-group monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase">Month *</label>
                                <select name="month" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly Range (also used for Monthly) -->
                            <div class="col-md-2 date-group monthly-group yearly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase">Year *</label>
                                <select name="year" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Select Customer *</label>
                                <select name="customer_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Select Employee *</label>
                                <select name="employee_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Invoice / Order # *</label>
                                <input type="text" name="invoice_no" class="form-control" placeholder="Search Invoice" value="{{ request('invoice_no') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Product *</label>
                                <select name="product_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Style Number *</label>
                                <input type="text" name="style_number" class="form-control" placeholder="All Style Numbers" value="{{ request('style_number') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Category *</label>
                                <select name="category_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Brand *</label>
                                <select name="brand_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Season *</label>
                                <select name="season_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>
                                            {{ $season->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Gender *</label>
                                <select name="gender_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>
                                            {{ $gender->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Branch</label>
                                <select name="branch_id" class="form-select select2" data-placeholder="Select Branch">
                                    <option value=""></option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Select Account *</label>
                                <select name="account" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-info text-white w-100 fw-bold border-0 shadow-sm" style="background-color: #0dcaf0; height: 38px;">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="export" id="exportInput" value="">
                        
                        <!-- Export Buttons -->
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm export-btn" data-type="excel">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                                <button type="button" class="btn btn-danger btn-sm export-btn" data-type="pdf">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                            </div>
                            <a href="{{ route('reports.sale') }}" class="btn btn-light btn-sm px-4">Clear All</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="saleReportTable">
                            <thead class="bg-success text-white small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-3 py-3">SN</th>
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
                                    <th>Style #</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Sales Qty</th>
                                    <th>Amount</th>
                                    <th>Return Qty</th>
                                    <th>Return Amt</th>
                                    <th>Net Qty</th>
                                    <th>Delivery</th>
                                    <th>Disc.</th>
                                    <th>Total Amount</th>
                                    <th class="pe-3">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allItems as $index => $item)
                                <tr>
                                    <td class="ps-3 small text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-dark small">{{ $item->invoice }}</td>
                                    <td class="small">{{ Carbon\Carbon::parse($item->date)->format('d/m/y') }}</td>
                                    <td class="small">{{ $item->customer_name }}</td>
                                    <td class="small">{{ $item->created_by_name }}</td>
                                    <td>
                                        <img src="{{ $item->product->image ? asset($item->product->image) : asset('static/default-product.png') }}" class="rounded shadow-sm" width="30" height="30" style="object-fit:cover;">
                                    </td>
                                    <td class="small">{{ $item->product->category->name ?? '-' }}</td>
                                    <td class="small">{{ $item->product->brand->name ?? '-' }}</td>
                                    <td class="small">{{ $item->product->season->name ?? '-' }}</td>
                                    <td class="small">{{ $item->product->gender->name ?? '-' }}</td>
                                    <td class="small fw-bold" title="{{ $item->product->name }}">{{ Str::limit($item->product->name, 15) }}</td>
                                    <td class="small">{{ $item->product->style_number ?? '-' }}</td>
                                    <td>
                                        @php $color = $item->variation ? $item->variation->attributeValues->where('attribute.name', 'Color')->first() : null; @endphp
                                        <span class="small">{{ $color ? $color->value : '-' }}</span>
                                    </td>
                                    <td>
                                        @php $size = $item->variation ? $item->variation->attributeValues->where('attribute.name', 'Size')->first() : null; @endphp
                                        <span class="small">{{ $size ? $size->value : '-' }}</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                    <td class="text-center small">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-center small text-muted">0</td>
                                    <td class="text-center small text-muted">0.00</td>
                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                    <td class="text-center small">{{ number_format($item->source == 'POS' ? ($item->pos->delivery ?? 0) : ($item->order->delivery ?? 0), 2) }}</td>
                                    <td class="text-center small text-danger">{{ number_format($item->discount, 2) }}</td>
                                    <td class="fw-bold text-dark">tk {{ number_format($item->total_price, 2) }}</td>
                                    <td class="pe-3 text-end fw-bold {{ $item->profit > 0 ? 'text-success' : 'text-danger' }}">
                                        tk {{ number_format($item->profit, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="22" class="text-center py-5 text-muted">No sales data available for the selected period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($allItems->count() > 0)
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="14" class="text-end py-3">GRAND TOTAL</td>
                                    <td class="text-center">{{ number_format($summary['total_qty']) }}</td>
                                    <td colspan="3"></td>
                                    <td class="text-center">{{ number_format($summary['total_qty']) }}</td>
                                    <td colspan="2"></td>
                                    <td class="text-end">tk {{ number_format($summary['total_amount'], 2) }}</td>
                                    <td class="pe-3 text-end text-success">tk {{ number_format($summary['total_profit'], 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-success { background-color: #2e7d32 !important; }
        thead th { font-size: 0.65rem !important; white-space: nowrap; border: none; }
        tbody td { font-size: 0.75rem !important; padding-top: 0.5rem; padding-bottom: 0.5rem; border-color: #f0f0f0; }
        
        .select2-container--bootstrap-5 .select2-selection { 
            font-size: 0.85rem; 
            min-height: 38px;
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #0dcaf0 !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.25) !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 12px;
            color: #495057;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
        }
        .form-check.custom-radio .form-check-input {
            width: 1.1em;
            height: 1.1em;
            margin-top: 0.2em;
        }
        .form-label {
            margin-bottom: 0.4rem;
            color: #344767;
            font-weight: 600;
        }

        @media print {
            @page { size: landscape; margin: 0.5cm; }
            .sidebar, .header, .btn-group, .card-header, form, .export-btn, a[href*="reports.sale"] { display: none !important; }
            .container-fluid { padding: 0 !important; }
            .main-content { margin: 0 !important; background: white !important; }
            .table-responsive { overflow: visible !important; }
            table { width: 100% !important; border: 1px solid #ddd !important; }
            thead th { background-color: #2e7d32 !important; color: white !important; -webkit-print-color-adjust: exact; }
        }
    </style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        function initSelect2() {
            $('.select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Select an option',
                    allowClear: true
                });
            });
        }

        initSelect2();

        // Fix: Auto-focus search input when opening Select2 dropdown
        $(document).on('select2:open', function(e) {
            window.setTimeout(function () {
                document.querySelector('.select2-search__field').focus();
            }, 0);
        });

        function toggleDateGroups() {
            const type = $('input[name="report_type"]:checked').val();
            $('.date-group').hide();
            
            if (type === 'daily') {
                $('.daily-group').show();
            } else if (type === 'monthly') {
                $('.monthly-group').show();
            } else if (type === 'yearly') {
                $('.yearly-group').show();
            }
        }

        $('input[name="report_type"]').on('change', function() {
            toggleDateGroups();
        });

        // Initialize on load
        toggleDateGroups();

        // Export handling
        $('.export-btn').on('click', function(e) {
            e.preventDefault();
            const type = $(this).data('type');
            $('#exportInput').val(type);
            $(this).closest('form').submit();
            setTimeout(() => {
                $('#exportInput').val('');
            }, 500);
        });
    });
</script>
@endpush
