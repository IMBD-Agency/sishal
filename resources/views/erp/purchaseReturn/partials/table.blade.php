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
            <table class="table premium-table compact reporting-table mb-0" id="returnTable">
                <thead>
                    <tr>
                        <th class="ps-3">SL</th>
                        <th>Return Date</th>
                        <th>R-Inv #</th>
                        <th>P-Inv #</th>
                        <th class="text-center">Image</th>
                        <th>Outlet / Source</th>
                        <th>Supplier</th>
                        <th>Mobile</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Season</th>
                        <th>Gender</th>
                        <th style="min-width: 150px;">Product Name</th>
                        <th>Style #</th>
                        <th width="120">Color</th>
                        <th width="100">Size</th>
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
                            $variation = $item->variation;
                            
                            $color = '-'; $size = '-';
                            if ($variation) {
                                if ($variation->attributeValues && $variation->attributeValues->count() > 0) {
                                    foreach($variation->attributeValues as $val) {
                                        $attr = $val->attribute;
                                        if (!$attr) continue;
                                        
                                        $attrName = strtolower($attr->name);
                                        if (str_contains($attrName, 'color') || $attr->is_color) {
                                            $color = $val->value;
                                        } elseif (str_contains($attrName, 'size') || str_contains($attrName, 'fit')) {
                                            $size = $val->value;
                                        }
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
                                @if($purchase && $purchase->bill && $purchase->bill->bill_number) 
                                    <a href="{{ route('purchase.show', $purchase->id) }}" class="text-decoration-none text-primary">{{ $purchase->bill->bill_number }}</a>
                                @elseif($purchase) 
                                    <a href="{{ route('purchase.show', $purchase->id) }}" class="text-decoration-none text-primary">#PUR-{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</a>
                                @else 
                                    <span class="text-muted small">GLOBAL</span> 
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="thumbnail-box mx-auto" style="width: 35px; height: 35px;">
                                    <img src="{{ $product && $product->image ? asset($product->image) : asset('static/default-product.jpg') }}" alt="">
                                </div>
                            </td>
                            <td>
                                @if($item->return_from_type == 'branch')
                                    <span class="badge bg-light text-dark border"><i class="fas fa-store-alt me-1 text-info"></i>{{ $item->branch->name ?? '-' }}</span>
                                @else
                                    <span class="badge bg-light text-dark border"><i class="fas fa-warehouse me-1 text-warning"></i>{{ $item->warehouse->name ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                {{ $purchase->supplier->name ?? ($return->supplier->name ?? '-') }}
                            </td>
                            <td class="small">
                                {{ $purchase->supplier->phone ?? ($return->supplier->phone ?? '-') }}
                            </td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->brand->name ?? '-' }}</td>
                            <td>{{ $product->season->name ?? '-' }}</td>
                            <td>{{ $product->gender->name ?? '-' }}</td>
                            <td class="fw-bold text-dark">
                                {{ $product->name ?? '-' }}
                                @if($variation && $variation->name && $variation->name !== $product->name)
                                    <span class="text-muted small d-block">({{ $variation->name }})</span>
                                @endif
                            </td>
                            <td><code class="text-primary bg-light px-2 py-1 rounded">{{ $product->sku ?? ($product->style_number ?? '-') }}</code></td>
                            <td class="text-uppercase fw-bold">{{ $color }}</td>
                            <td class="fw-bold">{{ $size }}</td>
                            <td class="text-center fw-bold">{{ number_format($item->returned_qty, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($amount, 2) }}৳</td>
                            <td class="pe-3">
                                <div class="d-flex gap-2 justify-content-center">
                                    @if(auth()->user()->hasPermissionTo('view purchase returns'))
                                        <a href="{{ route('purchaseReturn.show', $return->id) }}" class="action-circle bg-light border-0" title="View Audit Detail">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                    @endif

                                    @if($return->status === 'pending')
                                        @if(auth()->user()->hasPermissionTo('edit purchase returns'))
                                            <a href="{{ route('purchaseReturn.edit', $return->id) }}" class="action-circle bg-light border-0" title="Edit Return">
                                                <i class="fas fa-edit text-warning"></i>
                                            </a>
                                        @endif
                                        
                                        <button type="button" class="action-circle bg-light border-0 approve-return" 
                                            data-url="{{ route('purchaseReturn.updateStatus', $return->id) }}" 
                                            title="Approve & Complete">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </button>
                                    @endif

                                    @if(auth()->user()->hasPermissionTo('delete purchase returns'))
                                        <button type="button" class="action-circle bg-light border-0 delete-return" 
                                            data-url="{{ route('purchaseReturn.delete', $return->id) }}"
                                            title="Delete Return">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    @endif
                                </div>
                                
                                <!-- Hidden Form for Approval -->
                                <form id="approve-form-{{ $return->id }}" action="{{ route('purchaseReturn.updateStatus', $return->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="status" value="processed">
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="20" class="text-center py-5">
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
                        <td colspan="16" class="text-end py-3">Batch Registry Totals</td>
                        <td class="text-center font-monospace">{{ number_format($pageTotalQty, 2) }}</td>
                        <td class="text-end font-monospace">{{ number_format($pageTotalAmount, 2) }}৳</td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold text-primary text-uppercase" style="font-size: 13px;">
                        <td colspan="16" class="text-end py-3 bg-white">Global Return Auditor Total</td>
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
