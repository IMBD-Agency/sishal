@extends('erp.master')

@section('title', 'Purchase Return List')

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
                border: 1px solid #d1d5db !important;
                border-radius: 8px !important;
            }
            .form-label { font-size: 0.85rem; font-weight: 700; color: #374151; }
            .table thead th { 
                background: #198754; 
                color: #fff; 
                font-size: 0.75rem; 
                font-weight: 700; 
                text-transform: uppercase; 
                padding: 0.75rem 0.5rem; 
                white-space: nowrap;
            }
            .table tbody td { font-size: 0.85rem; vertical-align: middle; }
        </style>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0 text-dark">Purchase Return List</h2>
                <a href="{{ route('purchaseReturn.create') }}" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Create Return
                </a>
            </div>

            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('purchaseReturn.list') }}" method="GET" id="filterForm">
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
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase">End Date *</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <!-- Monthly Range -->
                            <div class="col-md-2 date-group monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase">Month *</label>
                                <select name="month" class="form-select select2" data-placeholder="Select Month">
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
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Invoice *</label>
                                <input type="text" name="search" class="form-control" placeholder="All Invoice" value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Supplier *</label>
                                <select name="supplier_id" class="form-select select2" data-placeholder="All Supplier">
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
                                <select name="product_id" class="form-select select2" data-placeholder="All Product">
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
                                <select name="category_id" class="form-select select2" data-placeholder="All Category">
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
                                <select name="brand_id" class="form-select select2" data-placeholder="All Brand">
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
                                <select name="season_id" class="form-select select2" data-placeholder="All Season">
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
                                <select name="gender_id" class="form-select select2" data-placeholder="All Gender">
                                    <option value=""></option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>
                                            {{ $gender->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-info text-white w-100 fw-bold border-0 shadow-sm" style="background-color: #17a2b8; height: 38px;">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <!-- Exports can be added here -->
                            </div>
                            <a href="{{ route('purchaseReturn.list') }}" class="btn btn-light btn-sm px-4">Clear All</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Script for Date Toggling -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
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

                    reportRadios.forEach(radio => {
                        radio.addEventListener('change', toggleDateGroups);
                    });
                    
                    // Init
                    toggleDateGroups();
                });
            </script>

            <!-- Table -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th>Serial No</th>
                                    <th>Date</th>
                                    <th>R-Inv. No.</th>
                                    <th>Purchase Invoice No</th>
                                    <th>Outlet</th>
                                    <th>Supplier</th>
                                    <th>Mobile</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th>Product Name</th>
                                    <th>Style Number</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Total Amount</th>
                                    <th>Option</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $pageTotalQty = 0;
                                    $pageTotalAmount = 0;
                                @endphp
                                @forelse($items as $index => $item)
                                    @php
                                        $return = $item->purchaseReturn;
                                        $purchase = $return->purchase;
                                        $product = $item->product;
                                        $variation = $item->purchaseItem ? $item->purchaseItem->variation : null;
                                        
                                        $color = '-';
                                        $size = '-';
                                        
                                        if ($variation && $variation->variationAttributes) {
                                            foreach($variation->variationAttributes as $attr) {
                                                $attrName = strtolower($attr->attribute->name ?? '');
                                                if (str_contains($attrName, 'color') || str_contains($attrName, 'colour') || $attr->attribute->is_color) {
                                                    $color = $attr->value;
                                                } elseif (str_contains($attrName, 'size')) {
                                                    $size = $attr->value;
                                                }
                                            }
                                        }

                                        $amount = $item->returned_qty * $item->unit_price;
                                        $pageTotalQty += $item->returned_qty;
                                        $pageTotalAmount += $amount;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                        <td>{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d M, Y') : '-' }}</td>
                                        <td class="fw-bold text-success">#{{ $return->id }}</td>
                                        <td>
                                            @if($purchase && $purchase->bill && $purchase->bill->bill_number)
                                                {{ $purchase->bill->bill_number }}
                                            @elseif($purchase)
                                                #{{ $purchase->id }}
                                            @elseif($return && $return->purchase_id)
                                                #{{ $return->purchase_id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->return_from_type == 'branch')
                                                <span class="badge bg-info text-dark">Branch: {{ $item->branch->name ?? '-' }}</span>
                                            @elseif($item->return_from_type == 'warehouse')
                                                <span class="badge bg-warning text-dark">WH: {{ $item->warehouse->name ?? '-' }}</span>
                                            @else
                                                {{ ucfirst($item->return_from_type) }}
                                            @endif
                                        </td>
                                        <td>{{ $purchase->supplier->name ?? '-' }}</td>
                                        <td>{{ $purchase->supplier->phone ?? '-' }}</td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td>{{ $product->brand->name ?? '-' }}</td>
                                        <td>{{ $product->season->name ?? '-' }}</td>
                                        <td>{{ $product->gender->name ?? '-' }}</td>
                                        <td class="fw-bold">{{ $product->name ?? '-' }}</td>
                                        <td>{{ $product->style_number ?? '-' }}</td>
                                        <td>{{ $color }}</td>
                                        <td>{{ $size }}</td>
                                        <td class="text-end fw-bold">{{ $item->returned_qty }}</td>
                                        <td class="text-end fw-bold">{{ number_format($amount, 2) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('purchaseReturn.show', $return->id) }}" class="btn btn-sm btn-light border" title="View Return">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="18" class="text-center py-5 text-muted">No data available in table</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="15" class="text-end">Grand Total (This Page):</td>
                                    <td class="text-end">{{ number_format($pageTotalQty) }}</td>
                                    <td class="text-end">{{ number_format($pageTotalAmount, 2) }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="15" class="text-end">Global Total (Filtered):</td>
                                    <td class="text-end">{{ number_format($totalQty) }}</td>
                                    <td class="text-end">{{ number_format($totalPrice, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                @if($items->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $items->links('vendor.pagination.bootstrap-5') }}
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
        });
    </script>
@endsection