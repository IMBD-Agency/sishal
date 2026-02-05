@extends('erp.master')

@section('title', 'Purchase Return List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <!-- Premium Header Area -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium text-uppercase">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted small">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold small">Return Registry</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Procurement Return Audit</h4>
                        <span class="badge bg-light text-success border border-success small rounded-pill px-3 py-1">{{ $items->total() }} Returns</span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button type="button" class="btn btn-outline-dark shadow-sm px-4 fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Registry
                    </button>
                    <a href="{{ route('purchaseReturn.create') }}" class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-plus-circle me-2"></i>New Return Entry
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Analytics Filters -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Reverse Procurement Filters</h6>
                        <div class="d-flex gap-4">
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="dailyReport">Custom Range</label>
                            </div>
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthlyReport">Monthly View</label>
                            </div>
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearlyReport">Annual View</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('purchaseReturn.list') }}" method="GET" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Return Start Date</label>
                                <input type="date" name="start_date" class="form-control shadow-none" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Return End Date</label>
                                <input type="date" name="end_date" class="form-control shadow-none" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 date-group monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Fiscal Month</label>
                                <select name="month" class="form-select select2-setup shadow-none">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 date-group monthly-group yearly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Fiscal Year</label>
                                <select name="year" class="form-select select2-setup shadow-none">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Invoice / Ref #</label>
                                <input type="text" name="search" class="form-control shadow-none" placeholder="Search return ID..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Assigned Supplier</label>
                                <select name="supplier_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Supplier">
                                    <option value=""></option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Returned Product</label>
                                <select name="product_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Product">
                                    <option value=""></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Style Ref Code</label>
                                <input type="text" name="style_number" class="form-control shadow-none" placeholder="Style SKU..." value="{{ request('style_number') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Product Category</label>
                                <select name="category_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Category">
                                    <option value=""></option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Inventory Brand</label>
                                <select name="brand_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Brand">
                                    <option value=""></option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Inventory Season</label>
                                <select name="season_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Season">
                                    <option value=""></option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Target Gender</label>
                                <select name="gender_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Gender">
                                    <option value=""></option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-none" style="height: 42px;">
                                    <i class="fas fa-search me-2"></i>Audit Returns
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <div class="btn-group shadow-none border rounded overflow-hidden">
                                    <button type="button" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end">CSV</button>
                                    <a href="{{ route('purchaseReturn.export.excel', request()->all()) }}" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end no-loader" target="_blank">EXCEL</a>
                                    <a href="{{ route('purchaseReturn.export.pdf', request()->all()) }}" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end no-loader" target="_blank">PDF</a>
                                    <button type="button" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted" onclick="window.print()">PRINT</button>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="small text-muted fw-bold">Live Return Sync: <span class="text-success">{{ now()->format('H:i:s') }}</span></span>
                                <a href="{{ route('purchaseReturn.list') }}" class="btn btn-light btn-sm px-4 fw-bold border shadow-none">Flush Audit</a>
                            </div>
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

            <!-- Procurement Return Registry Table -->
            <div class="premium-card shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-muted small text-uppercase"><i class="fas fa-undo-alt me-2 text-success"></i>Return Audit Registry</h6>
                    <div class="search-wrapper-premium">
                        <input type="text" id="returnSearch" class="form-control rounded-pill search-input-premium" placeholder="Search by Return ID, Invoice, Supplier...">
                        <i class="fas fa-search search-icon-premium"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact mb-0" id="returnTable">
                            <thead>
                                <tr>
                                    <th class="ps-3">SL</th>
                                    <th>Return Date</th>
                                    <th>R-Inv #</th>
                                    <th>P-Inv #</th>
                                    <th>Outlet / Source</th>
                                    <th>Supplier</th>
                                    <th>Mobile</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th style="min-width: 150px;">Product Name</th>
                                    <th>Style #</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-center">Ret. Qty</th>
                                    <th class="text-end">Ret. Amount</th>
                                    <th class="text-center pe-3">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $pageTotalQty = 0; $pageTotalAmount = 0;
                                @endphp
                                @forelse($items as $index => $item)
                                    @php
                                        $return = $item->purchaseReturn;
                                        if (!$return) continue;
                                        
                                        $purchase = $return->purchase;
                                        $product = $item->product;
                                        $variation = $item->purchaseItem ? $item->purchaseItem->variation : null;
                                        
                                        $color = '-'; $size = '-';
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
                                        <td class="ps-3 text-muted">{{ $items->firstItem() + $index }}</td>
                                        <td>{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') : '-' }}</td>
                                        <td class="fw-bold text-success">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="fw-bold text-dark">
                                            @if($purchase && $purchase->bill && $purchase->bill->bill_number) {{ $purchase->bill->bill_number }}
                                            @elseif($purchase) #PUR-{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}
                                            @else - @endif
                                        </td>
                                        <td>
                                            @if($item->return_from_type == 'branch')
                                                <span class="badge bg-light text-dark border"><i class="fas fa-store-alt me-1 text-info small"></i>{{ $item->branch->name ?? '-' }}</span>
                                            @else
                                                <span class="badge bg-light text-dark border"><i class="fas fa-warehouse me-1 text-warning small"></i>{{ $item->warehouse->name ?? '-' }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $purchase?->supplier?->name ?? '-' }}</td>
                                        <td class="text-muted small">{{ $purchase?->supplier?->phone ?? '-' }}</td>
                                        <td class="text-muted small">{{ $product->category->name ?? '-' }}</td>
                                        <td class="text-muted small">{{ $product->brand->name ?? '-' }}</td>
                                        <td class="text-muted small">{{ $product->season->name ?? '-' }}</td>
                                        <td class="text-muted small">{{ $product->gender->name ?? '-' }}</td>
                                        <td class="fw-bold text-dark">{{ $product->name ?? '-' }}</td>
                                        <td class="text-pink fw-bold">{{ $product->style_number ?? '-' }}</td>
                                        <td class="text-uppercase small fw-bold">{{ $color }}</td>
                                        <td class="small fw-bold">{{ $size }}</td>
                                        <td class="text-center fw-bold">{{ number_format($item->returned_qty, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($amount, 2) }}৳</td>
                                        <td class="pe-3">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('purchaseReturn.show', $return->id) }}" class="action-circle bg-light border-0" title="View Audit Detail">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="18" class="text-center py-5">
                                            <div class="text-muted opacity-50 py-4">
                                                <i class="fas fa-undo-alt fa-3x mb-3"></i>
                                                <h6 class="fw-bold">No Procurement Returns Found</h6>
                                                <p class="small mb-0">Adjust your audit filters or check the registry batch.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light border-top-0">
                                <tr class="fw-bold text-dark text-uppercase" style="font-size: 13px;">
                                    <td colspan="15" class="text-end py-3">Batch Registry Totals</td>
                                    <td class="text-center font-monospace">{{ number_format($pageTotalQty, 2) }}</td>
                                    <td class="text-end font-monospace">{{ number_format($pageTotalAmount, 2) }}৳</td>
                                    <td></td>
                                </tr>
                                <tr class="fw-bold text-primary text-uppercase" style="font-size: 13px;">
                                    <td colspan="15" class="text-end py-3 bg-white">Global Return Auditor Total</td>
                                    <td class="text-center font-monospace bg-white">{{ number_format($totalQty, 2) }}</td>
                                    <td class="text-end font-monospace bg-white">{{ number_format($totalPrice, 2) }}৳</td>
                                    <td class="bg-white"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @if($items->hasPages())
                <div class="card-footer bg-white py-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 0.05em;">Registry Batch: {{ $items->firstItem() }} - {{ $items->lastItem() }} of {{ $items->total() }}</small>
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
    </script>
@endsection