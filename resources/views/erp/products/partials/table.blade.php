<div>
    <div class="premium-card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table premium-table reporting-table compact mb-0" id="productTable">
                    <thead>
                        <tr>
                            <th class="text-center">#SN.</th>
                            <th>Entry Date</th>
                            <th class="text-center">Image</th>
                            <th>Product Name</th>
                            <th>Style Number</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Season</th>
                            <th>Gender</th>
                            <th class="text-end">Purchase Price</th>
                            <th class="text-end">MRP</th>
                            <th class="text-end">Whole Sale</th>
                            <th class="text-center">Total Stock</th>
                            <th class="text-center">Option</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                        <tr>
                            <td class="text-center">{{ $products->firstItem() + $index }}</td>
                            <td>{{ $product->created_at->format('d-m-Y') }}</td>
                            <td class="text-center">
                                <div class="thumbnail-box mx-auto product-thumb-container">
                                    @if($product->image)
                                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-thumb-img">
                                    @else
                                        <i class="fas fa-shopping-cart text-muted opacity-50 small"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('product.show', $product->id) }}" class="text-info text-decoration-none fw-bold">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td>{{ $product->style_number ?? $product->sku }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->brand->name ?? '-' }}</td>
                            <td>{{ strtoupper($product->season->name ?? 'ALL') }}</td>
                            <td>{{ strtoupper($product->gender->name ?? 'ALL') }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->cost, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->price, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->wholesale_price ?? 0, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $totalVarStock = $product->total_stock_variation ?? 0;
                                    $totalSimpleStock = ($product->total_stock_branch ?? 0) + ($product->total_stock_warehouse ?? 0);
                                    $displayStock = $product->has_variations ? $totalVarStock : $totalSimpleStock;
                                @endphp
                                <span class="badge {{ $displayStock > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ number_format($displayStock, 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('product.show', $product->id) }}" class="btn btn-info btn-xs text-white" title="View"><i class="fas fa-eye fa-xs"></i></a>
                                    <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success btn-xs" title="Edit"><i class="fas fa-edit fa-xs"></i></a>
                                    <form action="{{ route('product.delete', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash fa-xs"></i></button>
                                    </form>
                                    <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary btn-xs text-white" title="Variations"><i class="fas fa-layer-group fa-xs"></i></a>
                                     <a href="{{ route('barcodes.index') }}?style_no={{ $product->style_number ?? $product->sku }}" class="btn btn-warning btn-xs text-white" title="Barcode"><i class="fas fa-barcode fa-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center py-4">No data found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white border-top-0 py-2 px-3">
            <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted small mb-0">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} entries</p>
                {{ $products->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
