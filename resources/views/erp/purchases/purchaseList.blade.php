@extends('erp.master')

@section('title', 'Purchase List')

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
                            <li class="breadcrumb-item active text-primary fw-bold small">Purchase History</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Purchase Procurement Report</h4>
                        <span class="badge bg-light text-primary border border-primary small rounded-pill px-3 py-1">{{ $items->total() }} Records</span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <button type="button" class="btn btn-outline-dark shadow-sm px-4 fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Registry
                    </button>
                    <a href="{{ route('purchase.create') }}" class="btn btn-create-premium text-nowrap">
                        <i class="fas fa-plus-circle me-2"></i>New Procurement
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Analytics Filters -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Procurement Audit Filters</h6>
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
                    <form action="{{ route('purchase.list') }}" method="GET" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Start Date Registry</label>
                                <input type="date" name="start_date" class="form-control shadow-none" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-group daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">End Date Registry</label>
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
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Branch</label>
                                <select name="branch_id" class="form-select select2-setup shadow-none" data-placeholder="All Branches">
                                    <option value=""></option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Warehouse</label>
                                <select name="warehouse_id" class="form-select select2-setup shadow-none" data-placeholder="All Warehouses">
                                    <option value=""></option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Challan / Inv #</label>
                                <input type="text" name="search" class="form-control shadow-none" placeholder="Search procurement ID..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Vested Supplier</label>
                                <select name="supplier_id" class="form-select select2-setup shadow-none" data-placeholder="Choose Supplier">
                                    <option value=""></option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Allocated Product</label>
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
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Vested Brand</label>
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

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Account Registry</label>
                                <select name="account" class="form-select select2-setup shadow-none" data-placeholder="Select A/C">
                                    <option value=""></option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ request('account') == $account->id ? 'selected' : '' }}>{{ $account->provider_name }} ({{ $account->account_number }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 shadow-none" style="height: 42px;">
                                    <i class="fas fa-search me-2"></i>Apply
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="btn-group shadow-none border rounded overflow-hidden">
                                <button type="button" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end">CSV</button>
                                <a href="{{ route('purchase.export.excel', request()->all()) }}" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end">EXCEL</a>
                                <a href="{{ route('purchase.export.pdf', request()->all()) }}" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end">PDF</a>
                                <button type="button" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted" onclick="window.print()">PRINT</button>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="small text-muted fw-bold">Live Data Sync: <span class="text-primary">{{ now()->format('H:i:s') }}</span></span>
                                <a href="{{ route('purchase.list') }}" class="btn btn-light btn-sm px-4 fw-bold border shadow-none">Flush Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Procurement Audit Registry Table -->
            <div class="premium-card shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-muted small text-uppercase"><i class="fas fa-list me-2 text-primary"></i>Audit Registry</h6>
                    <div class="search-wrapper-premium">
                        <input type="text" id="procurementSearch" class="form-control rounded-pill search-input-premium" placeholder="Search by Invoice, Supplier, Registry...">
                        <i class="fas fa-search search-icon-premium"></i>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact mb-0" id="procurementTable">
                            <thead>
                                <tr>
                                    <th class="ps-3">SL</th>
                                    <th>Invoice #</th>
                                    <th>Registry Date</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Media</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th style="min-width: 160px;">Product Name</th>
                                    <th>Style Ref</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-center">Pur. Qty</th>
                                    <th class="text-center bg-light">T. Pur. Qty</th>
                                    <th class="text-end">Pur. Value</th>
                                    <th class="text-end bg-light">T. Pur. Value</th>
                                    <th class="text-center text-danger">Ret. Qty</th>
                                    <th class="text-center text-danger bg-light">T. Ret. Qty</th>
                                    <th class="text-end text-danger">Ret. Value</th>
                                    <th class="text-end text-danger bg-light">T. Ret. Value</th>
                                    <th class="text-center text-success">Act. Qty</th>
                                    <th class="text-center text-success bg-light">T. Act. Qty</th>
                                    <th class="text-end text-success">Act. Value</th>
                                    <th class="text-end text-success bg-light">T. Act. Value</th>
                                    <th class="text-end text-primary">Paid A/C</th>
                                    <th class="text-end text-danger">Due A/C</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center pe-3">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $grandTotalPurQty = 0; $grandTotalPurAmt = 0;
                                    $grandTotalRetQty = 0; $grandTotalRetAmt = 0;
                                    $grandTotalActQty = 0; $grandTotalActAmt = 0;
                                    $grandTotalPaid = 0; $grandTotalDue = 0;
                                @endphp
                                @forelse($items as $index => $item)
                                    @php
                                        $purchase = $item->purchase;
                                        $bill = $purchase->bill;
                                        $product = $item->product;
                                        $variation = $item->variation;
                                        
                                        $color = '-'; $size = '-';
                                        if ($variation && $variation->attributeValues) {
                                            foreach($variation->attributeValues as $val) {
                                                $attrName = strtolower($val->attribute->name ?? '');
                                                if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) {
                                                    $color = $val->value;
                                                } elseif (str_contains($attrName, 'size')) {
                                                    $size = $val->value;
                                                }
                                            }
                                        }

                                        $retQty = $item->returnItems->sum('returned_qty');
                                        $retAmt = $item->returnItems->sum(function($ri) { return $ri->returned_qty * $ri->unit_price; });
                                        $actualQty = $item->quantity - $retQty;
                                        $actualAmt = $item->total_price - $retAmt;

                                        $grandTotalPurQty += $item->quantity;
                                        $grandTotalPurAmt += $item->total_price;
                                        $grandTotalRetQty += $retQty;
                                        $grandTotalRetAmt += $retAmt;
                                        $grandTotalActQty += $actualQty;
                                        $grandTotalActAmt += $actualAmt;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $items->firstItem() + $index }}</td>
                                        <td class="fw-bold">
                                            <a href="{{ route('purchase.show', $purchase->id) }}" class="text-decoration-none text-primary">
                                                @if($bill && $bill->bill_number) {{ $bill->bill_number }} 
                                                @else #PUR-{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }} @endif
                                            </a>
                                        </td>
                                        <td>{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') : '-' }}</td>
                                        <td class="fw-bold">{{ $purchase->supplier->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="thumbnail-box mx-auto" style="width: 35px; height: 35px;">
                                                <img src="{{ $product && $product->image ? asset('storage/'.$product->image) : asset('static/default-product.png') }}" alt="P">
                                            </div>
                                        </td>
                                        <td class="text-muted">{{ $product->category->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $product->brand->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $product->season->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $product->gender->name ?? '-' }}</td>
                                        <td class="fw-bold text-dark">{{ $product->name ?? '-' }}</td>
                                        <td><code class="text-primary bg-light px-2 py-1 rounded small">{{ $product->sku ?? '-' }}</code></td>
                                        <td class="text-uppercase small fw-bold">{{ $color }}</td>
                                        <td class="small fw-bold">{{ $size }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-center fw-bold bg-light">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->total_price, 2) }}৳</td>
                                        <td class="text-end fw-bold bg-light">{{ number_format($item->total_price, 2) }}৳</td>
                                        
                                        <td class="text-center text-danger">{{ number_format($retQty, 2) }}</td>
                                        <td class="text-center text-danger fw-bold bg-light">{{ number_format($retQty, 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($retAmt, 2) }}৳</td>
                                        <td class="text-end text-danger fw-bold bg-light">{{ number_format($retAmt, 2) }}৳</td>
                                        
                                        <td class="text-center text-success">{{ number_format($actualQty, 2) }}</td>
                                        <td class="text-center text-success fw-bold bg-light">{{ number_format($actualQty, 2) }}</td>
                                        <td class="text-end text-success">{{ number_format($actualAmt, 2) }}৳</td>
                                        <td class="text-end text-success fw-bold bg-light">{{ number_format($actualAmt, 2) }}৳</td>
                                        
                                        <td class="text-end text-primary fw-bold">
                                            @if($index == 0 || $items[$index-1]->purchase_id != $item->purchase_id)
                                                {{ number_format($bill->paid_amount ?? 0, 2) }}৳
                                                @php $grandTotalPaid += ($bill->paid_amount ?? 0); @endphp
                                            @else - @endif
                                        </td>
                                        <td class="text-end text-danger fw-bold">
                                            @if($index == 0 || $items[$index-1]->purchase_id != $item->purchase_id)
                                                {{ number_format($bill->due_amount ?? 0, 2) }}৳
                                                @php $grandTotalDue += ($bill->due_amount ?? 0); @endphp
                                            @else - @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusDot = [
                                                    'pending' => 'bg-warning',
                                                    'received' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                ][$purchase->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="status-pill {{ str_replace('bg-', 'status-', $statusDot) }}">
                                                <i class="fas fa-circle extra-small"></i>{{ ucfirst($purchase->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-3">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('purchase.show', $purchase->id) }}" class="action-circle bg-light border-0" title="View Audit Detail">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="29" class="text-center py-5">
                                            <div class="text-muted opacity-50 py-4">
                                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                                <h6 class="fw-bold">No Procurement Records Found</h6>
                                                <p class="small mb-0">Adjust your filters or try scanning a different batch.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light border-top-0">
                                <tr class="fw-bold text-dark text-uppercase" style="font-size: 13px;">
                                    <td colspan="13" class="text-end py-3">Global Registry Totals</td>
                                    <td class="text-center">{{ number_format($grandTotalPurQty, 2) }}</td>
                                    <td class="text-center bg-white">{{ number_format($grandTotalPurQty, 2) }}</td>
                                    <td class="text-end">{{ number_format($grandTotalPurAmt, 2) }}৳</td>
                                    <td class="text-end bg-white">{{ number_format($grandTotalPurAmt, 2) }}৳</td>
                                    
                                    <td class="text-center text-danger">{{ number_format($grandTotalRetQty, 2) }}</td>
                                    <td class="text-center text-danger bg-white">{{ number_format($grandTotalRetQty, 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($grandTotalRetAmt, 2) }}৳</td>
                                    <td class="text-end text-danger bg-white">{{ number_format($grandTotalRetAmt, 2) }}৳</td>
                                    
                                    <td class="text-center text-success">{{ number_format($grandTotalActQty, 2) }}</td>
                                    <td class="text-center text-success bg-white">{{ number_format($grandTotalActQty, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($grandTotalActAmt, 2) }}৳</td>
                                    <td class="text-end text-success bg-white">{{ number_format($grandTotalActAmt, 2) }}৳</td>
                                    
                                    <td class="text-end text-primary">{{ number_format($grandTotalPaid, 2) }}৳</td>
                                    <td class="text-end text-danger">{{ number_format($grandTotalDue, 2) }}৳</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
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
            let searchTimeout;
            $('#procurementSearch').on('input', function() {
                const value = $(this).val().toLowerCase();
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(function() {
                    $('#procurementTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                }, 300);
            });
        });
    </script>
@endsection