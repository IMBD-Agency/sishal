@extends('erp.master')

@section('title', 'Stock Value')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Stock Value Report</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="exportStockValue()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Total Stock Value -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ number_format($totalStockValue, 2) }} TK</h4>
                                            <p class="mb-0">Total Stock Value</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-boxes fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $productStockValues->count() }}</h4>
                                            <p class="mb-0">Products in Stock</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-cube fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $categoryStockValues->count() }}</h4>
                                            <p class="mb-0">Categories</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-tags fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Value by Product -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Stock Value by Product</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="productStockTable">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-end">Stock Quantity</th>
                                                    <th class="text-end">Unit Cost</th>
                                                    <th class="text-end">Total Value</th>
                                                    <th class="text-end">Selling Price</th>
                                                    <th class="text-end">Potential Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($productStockValues as $data)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($data['product']->image)
                                                                <img src="{{ asset($data['product']->image) }}" 
                                                                     alt="{{ $data['product']->name }}" 
                                                                     class="rounded me-2" 
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            @endif
                                                            <div>
                                                                <strong>{{ $data['product']->name }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $data['product']->category->name ?? 'Uncategorized' }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="badge bg-primary">{{ number_format($data['total_stock']) }}</span>
                                                    </td>
                                                    <td class="text-end">{{ number_format($data['unit_cost'], 2) }} TK</td>
                                                    <td class="text-end">
                                                        <strong class="text-success">{{ number_format($data['total_value'], 2) }} TK</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        @php
                                                            $sellingPrice = ($data['product']->discount ?? 0) > 0 
                                                                ? ($data['product']->discount ?? 0)
                                                                : ($data['product']->price ?? 0);
                                                        @endphp
                                                        {{ number_format($sellingPrice, 2) }} TK
                                                    </td>
                                                    <td class="text-end">
                                                        @php
                                                            $potentialProfit = ($sellingPrice - $data['unit_cost']) * $data['total_stock'];
                                                        @endphp
                                                        <span class="badge {{ $potentialProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                            {{ number_format($potentialProfit, 2) }} TK
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No products with stock found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Value by Category -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Stock Value by Category</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th class="text-end">Products</th>
                                                    <th class="text-end">Total Stock</th>
                                                    <th class="text-end">Total Value</th>
                                                    <th class="text-end">Avg. Value per Product</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($categoryStockValues as $data)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $data['category_name'] }}</strong>
                                                    </td>
                                                    <td class="text-end">{{ $data['product_count'] }}</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-primary">{{ number_format($data['total_stock']) }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-success">{{ number_format($data['total_value'], 2) }} TK</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format($data['product_count'] > 0 ? $data['total_value'] / $data['product_count'] : 0, 2) }} TK
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No category data found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportStockValue() {
    // Simple CSV export functionality
    const table = document.getElementById('productStockTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let cellText = cols[j].innerText.replace(/,/g, '');
            row.push(cellText);
        }
        
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'stock_value_report.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
        </div>
    </div>
@endsection
