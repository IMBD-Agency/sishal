@extends('erp.master')

@section('title', 'Product Stock Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Inventory Stock Report</h4>
                    <p class="text-muted small mb-0">Detailed breakdown of product stock and valuation</p>
                </div>
                <div>
                     <form method="GET" class="d-flex gap-2">
                        <select name="branch_id" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <select name="warehouse_id" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <select name="category_id" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-dark"><i class="fas fa-filter"></i></button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4">Product Name</th>
                                    <th class="py-3">SKU / Style</th>
                                    <th class="py-3">Category</th>
                                    <th class="py-3 text-center">Current Stock</th>
                                    <th class="py-3 text-end">Unit Cost</th>
                                    <th class="py-3 text-end">Sale Price</th>
                                    <th class="py-3 text-end">Stock Value (Cost)</th>
                                    <th class="py-3 text-end">Potential Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $totalQty = 0; $totalValue = 0; $totalRevenue = 0;
                                @endphp
                                @forelse($products as $product)
                                    @php 
                                        $totalQty += $product->total_stock; 
                                        $totalValue += $product->stock_value;
                                        $totalRevenue += $product->potential_revenue;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $product->name }}</td>
                                        <td class="small text-muted">{{ $product->style_number ?? $product->sku }}</td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td class="text-center fw-bold {{ $product->total_stock < 5 ? 'text-danger' : 'text-dark' }}">
                                            {{ $product->total_stock }}
                                        </td>
                                        <td class="text-end text-muted">{{ number_format($product->cost, 2) }}</td>
                                        <td class="text-end text-muted">{{ number_format($product->price, 2) }}</td>
                                        <td class="text-end">{{ number_format($product->stock_value, 2) }}</td>
                                        <td class="text-end text-success fw-bold">{{ number_format($product->potential_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-5 text-muted">No products found</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold text-dark">
                                <tr>
                                    <td colspan="3" class="text-end text-uppercase">Total Inventory Assets</td>
                                    <td class="text-center">{{ number_format($totalQty) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end">{{ number_format($totalValue, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($totalRevenue, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
