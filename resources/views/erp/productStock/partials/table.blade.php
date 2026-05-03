<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Total Stock Items</h6>
                        <h2 class="fw-bold mb-0">{{ number_format($totalStockQty) }}</h2>
                    </div>
                    <div class="avatar-md bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-boxes fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Total Stock Value</h6>
                        <h2 class="fw-bold mb-0">৳ {{ number_format($totalStockValue, 2) }}</h2>
                    </div>
                    <div class="avatar-md bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-coins fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-info text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Active Products</h6>
                        <h2 class="fw-bold mb-0">{{ $productStocks->total() }}</h2>
                    </div>
                    <div class="avatar-md bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-tag fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Registry -->
<div class="premium-card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table premium-table reporting-table mb-0" id="stockTable">
                <thead>
                        <tr>
                        <th class="ps-3">SL</th>
                        <th>Item Details</th>
                        <th>Style / SKU</th>
                        <th>Category</th>
                        <th>Size Breakdown</th>
                        <th class="text-center">Total Purchase (YTD)</th>
                        <th class="text-end">Pur. Price</th>
                        <th class="text-end">MRP</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-end">Stock Value</th>
                        <th class="text-center">Locations</th>
                        <th class="text-center pe-3">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productStocks as $index => $stock)
                        @php
                            $totalStock = $stock->has_variations ? ($stock->var_stock ?? 0) : (($stock->simple_branch_stock ?? 0) + ($stock->simple_warehouse_stock ?? 0));

                            $branchStockData = [];
                            $warehouseStockData = [];
                            $sizeSumData = []; 
                            $hasNegativeStock = false;

                            if ($stock->has_variations) {
                                foreach ($stock->variations as $var) {
                                    $sizeName = $var->attributeValues->pluck('value')->implode(', ') ?: 'Default';
                                    foreach ($var->stocks as $s) {
                                        if ($s->branch_id) {
                                            $locName = $s->branch->name ?? 'Unknown';
                                            if(!isset($branchStockData[$locName])) $branchStockData[$locName] = [];
                                            $branchStockData[$locName][] = ['size' => $sizeName, 'qty' => $s->quantity];
                                            
                                            $sizeSumData[$sizeName] = ($sizeSumData[$sizeName] ?? 0) + $s->quantity;
                                            if ($s->quantity < 0) $hasNegativeStock = true;
                                        } else {
                                            $locName = $s->warehouse->name ?? 'Unknown';
                                            if(!isset($warehouseStockData[$locName])) $warehouseStockData[$locName] = [];
                                            $warehouseStockData[$locName][] = ['size' => $sizeName, 'qty' => $s->quantity];
                                            
                                            $sizeSumData[$sizeName] = ($sizeSumData[$sizeName] ?? 0) + $s->quantity;
                                            if ($s->quantity < 0) $hasNegativeStock = true;
                                        }
                                    }
                                }
                            } else {
                                foreach ($stock->branchStock as $s) {
                                    $locName = $s->branch->name ?? 'Unknown';
                                    $branchStockData[$locName] = [['size' => 'N/A', 'qty' => $s->quantity]];
                                    if ($s->quantity < 0) $hasNegativeStock = true;
                                }
                                foreach ($stock->warehouseStock as $s) {
                                    $locName = $s->warehouse->name ?? 'Unknown';
                                    $warehouseStockData[$locName] = [['size' => 'N/A', 'qty' => $s->quantity]];
                                    if ($s->quantity < 0) $hasNegativeStock = true;
                                }
                            }

                            $totalPurchase = 0;
                            $purchaseSumData = [];

                            if ($stock->relationLoaded('purchaseItems')) {
                                foreach ($stock->purchaseItems as $pi) {
                                    $totalPurchase += $pi->quantity;
                                    
                                    if ($pi->variation_id && $stock->has_variations) {
                                        $sizeName = 'Unknown';
                                        $var = collect($stock->variations)->firstWhere('id', $pi->variation_id);
                                        if ($var) {
                                            $sizeName = collect($var->attributeValues)->pluck('value')->implode(', ') ?: 'Default';
                                        }
                                        $purchaseSumData[$sizeName] = ($purchaseSumData[$sizeName] ?? 0) + $pi->quantity;
                                    } else {
                                        $purchaseSumData['N/A'] = ($purchaseSumData['N/A'] ?? 0) + $pi->quantity;
                                    }
                                }
                            }
                        @endphp
                        <tr class="{{ $totalStock <= 5 ? 'bg-danger bg-opacity-10' : '' }}">
                            <td class="ps-3 text-muted">{{ $productStocks->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="thumbnail-box me-3" style="width: 38px; height: 38px;">
                                            @if($stock->image)
                                            <img src="{{ asset($stock->image) }}" alt="img" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                            <i class="fas fa-image text-muted opacity-50 fa-lg"></i>
                                            @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $stock->name }}</div>
                                        <div class="small text-muted">{{ $stock->brand->name ?? '-' }} | {{ $stock->gender->name ?? 'ALL' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="text-primary bg-light px-2 py-1 rounded small">{{ $stock->style_number ?? $stock->sku }}</code>
                            </td>
                            <td>
                                <span class="category-tag">{{ $stock->category->name ?? '-' }}</span>
                            </td>
                            <td>
                                @if($stock->has_variations)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($sizeSumData as $size => $qty)
                                            @php
                                                $purQty = $purchaseSumData[$size] ?? 0;
                                            @endphp
                                            <span class="badge bg-light text-dark border small fw-normal" title="Current Stock: {{ $qty }} | Purchased YTD: {{ $purQty }}">
                                                <span class="text-muted">{{ $size }}:</span> 
                                                <strong class="{{ $qty <= 5 ? 'text-danger' : 'text-primary' }}">{{ $qty }}</strong>
                                                <small class="text-success ms-1"><i class="fas fa-arrow-down" style="font-size: 0.6rem;"></i>{{ $purQty }}</small>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small italic">No variations</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold text-success">{{ $totalPurchase }}</td>
                            <td class="text-end fw-bold">{{ number_format($stock->cost, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($stock->price, 2) }}</td>
                            <td class="text-center">
                                    <span class="badge {{ $totalStock > 5 ? 'bg-success' : 'bg-danger' }} fs-6">
                                    {{ $totalStock }}
                                </span>
                                @if($hasNegativeStock)
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Negative Stock Detected"></i>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                    {{ number_format($totalStock * $stock->cost, 2) }}
                            </td>
                            <td class="text-center">
                                @php $locCount = count($branchStockData) + count($warehouseStockData); @endphp
                                <span class="badge bg-light text-dark border pointer" onclick="$(this).closest('tr').find('.view-breakdown').click()">
                                    {{ $locCount }} Locations
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <button class="btn btn-action view-breakdown" 
                                        data-name="{{ $stock->name }}"
                                        data-branch-stock='@json($branchStockData)'
                                        data-warehouse-stock='@json($warehouseStockData)'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light fw-bold border-top-0">
                    <tr>
                        <td colspan="7" class="text-end ps-3 py-3">GRAND TOTAL (Filtered)</td>
                        <td class="text-center py-3 text-success">-</td>
                        <td class="text-center py-3">
                            <span class="badge bg-dark fs-6 px-3">{{ number_format($totalStockQty) }}</span>
                        </td>
                        <td class="text-end py-3 text-primary">
                            ৳{{ number_format($totalStockValue, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

        <!-- Pagination -->
    <div class="card-footer bg-white border-top-0 py-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="text-muted small mb-0">Displaying {{ $productStocks->firstItem() }} to {{ $productStocks->lastItem() }} of {{ $productStocks->total() }} items</p>
            {{ $productStocks->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
