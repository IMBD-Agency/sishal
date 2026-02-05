@extends('erp.master')

@section('title', 'Itemized Purchase Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Detailed Purchase Report</h2>
                    <p class="text-muted mb-0">Deep insight into your procurement and inventory items</p>
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
                    <form action="{{ route('reports.purchase') }}" method="GET" id="reportForm">
                        <div class="mb-4">
                            <div class="d-flex gap-4">
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="dailyReport">
                                        <i class="fas fa-calendar-day me-1 text-primary"></i> Daily Reports
                                    </label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="monthlyReport">
                                        <i class="fas fa-calendar-alt me-1 text-success"></i> Monthly Reports
                                    </label>
                                </div>
                                <div class="form-check custom-radio">
                                    <input class="form-check-input" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="yearlyReport">
                                        <i class="fas fa-calendar me-1 text-info"></i> Yearly Reports
                                    </label>
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
                                <select name="month" class="form-select select2" data-placeholder="Select Month">
                                    <option value="">Select Month</option>
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
                                <select name="year" class="form-select select2" data-placeholder="Select Year">
                                    <option value="">Select Year</option>
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Challan *</label>
                                <select name="challan_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($challans as $challan)
                                        <option value="{{ $challan->id }}" {{ request('challan_id') == $challan->id ? 'selected' : '' }}>
                                            #{{ $challan->id }} ({{ $challan->bill->bill_number ?? 'No Bill' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Supplier *</label>
                                <select name="supplier_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                <input type="text" name="style_number" class="form-control" placeholder="All Style Number" value="{{ request('style_number') }}">
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
                                <label class="form-label small fw-bold text-muted text-uppercase">Warehouse</label>
                                <select name="warehouse_id" class="form-select select2" data-placeholder="Select Warehouse">
                                    <option value=""></option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
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
                                <label class="form-label small fw-bold text-muted text-uppercase">Select Account *</label>
                                <select name="account_id" class="form-select select2" data-placeholder="Select an option">
                                    <option value=""></option>
                                    {{-- Accounts functionality is currently disabled --}}
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-info text-white w-100 fw-bold border-0 shadow-sm" style="background-color: #0dcaf0; height: 38px;">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
</div>

                        <input type="hidden" name="export" id="exportInput" value="">
                        
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm export-btn" data-type="excel">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                                <button type="button" class="btn btn-danger btn-sm export-btn" data-type="pdf">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                            </div>
                            <a href="{{ route('reports.purchase') }}" class="btn btn-light btn-sm px-4">Clear All</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="small fw-bold text-uppercase text-muted mb-1">Total Quantity</div>
                        <div class="h3 fw-bold mb-0 text-primary">{{ number_format($summary['total_qty']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="small fw-bold text-uppercase text-muted mb-1">Total Amount</div>
                        <div class="h3 fw-bold mb-0 text-success">tk {{ number_format($summary['total_amount'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="small fw-bold text-uppercase text-muted mb-1">Products</div>
                        <div class="h3 fw-bold mb-0 text-info">{{ $summary['unique_products'] }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="small fw-bold text-uppercase text-muted mb-1">Orders Count</div>
                        <div class="h3 fw-bold mb-0 text-dark">{{ $summary['total_orders'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-dark text-white small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4 py-3">SN</th>
                                    <th>Ref / Bill</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Style #</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th>Variation</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end pe-4">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                <tr>
                                    <td class="ps-4 small text-muted">{{ $items->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('purchase.show', $item->purchase_id) }}" class="fw-bold text-primary text-decoration-none">
                                            #{{ $item->purchase_id }}
                                        </a>
                                        @if($item->purchase->bill)
                                            <br><small class="text-muted d-block">{{ $item->purchase->bill->bill_number }}</small>
                                        @endif
                                    </td>
                                    <td class="small">{{ $item->purchase->purchase_date ? Carbon\Carbon::parse($item->purchase->purchase_date)->format('d/m/y') : $item->purchase->created_at->format('d/m/y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ $item->purchase->supplier->name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <img src="{{ $item->product->image ? asset($item->product->image) : asset('static/default-product.png') }}" class="rounded-2 shadow-sm" width="35" height="35" style="object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small" title="{{ $item->product->name ?? 'Deleted' }}">
                                            {{ Str::limit($item->product->name ?? 'Deleted', 20) }}
                                        </div>
                                    </td>
                                    <td class="small">{{ $item->product->style_number ?? '-' }}</td>
                                    <td><span class="badge bg-light text-dark border small">{{ $item->product->category->name ?? '-' }}</span></td>
                                    <td class="small">{{ $item->product->brand->name ?? '-' }}</td>
                                    <td class="small">{{ $item->product->season->name ?? '-' }}</td>
                                    <td class="small">{{ $item->product->gender->name ?? '-' }}</td>
                                    <td>
                                        @if($item->variation)
                                            @foreach($item->variation->attributeValues as $val)
                                                <span class="badge bg-info-subtle text-info x-small">{{ $val->value }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">Standard</span>
                                        @endif
                                    </td>
                                    <td class="text-center small">tk {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                    <td class="text-end pe-4 fw-bold text-dark">tk {{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="text-center py-5 text-muted">No procurement data matches your advanced filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($items->count() > 0)
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="13" class="text-end py-3">GRAND TOTAL</td>
                                    <td class="text-center">{{ number_format($summary['total_qty']) }}</td>
                                    <td class="text-end pe-4">tk {{ number_format($summary['total_amount'], 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                @if($items->hasPages())
                <div class="card-footer bg-transparent border-0 p-4">
                    {{ $items->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .x-small { font-size: 0.7rem; }
        thead th { font-size: 0.75rem !important; }
        tbody td { font-size: 0.85rem !important; }
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
        .btn-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
        }
        .btn-info:hover {
            background-color: #0baccc;
            border-color: #0baccc;
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
            
            // Re-initialize select2 for newly shown elements if necessary
            // or ensure they have correct layout
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
            $('#reportForm').submit();
            // Reset after a small delay to ensure submit handles it
            setTimeout(() => {
                $('#exportInput').val('');
            }, 500);
        });
    });
</script>
@endpush
