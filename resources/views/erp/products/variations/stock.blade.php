@extends('erp.master')

@section('title', 'Manage Variation Stock')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-4">
            <!-- Header Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold d-flex align-items-center">
                        <i class="fas fa-boxes me-2 text-dark"></i>
                        Manage Stock - <span class="text-muted ms-1">{{ $product->name }} ({{ $variation->name }})</span>
                    </h5>
                    <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary px-4 d-flex align-items-center">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Add to Branches -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark">Add to Branches</h6>
                        </div>
                        <div class="card-body">
                            <form id="branchStockForm">
                                <div class="table-responsive" style="max-height: 250px;">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th class="small fw-bold border-0">Branch Name</th>
                                                <th class="small fw-bold border-0 text-end" style="width: 100px;">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($branches as $branch)
                                                <tr>
                                                    <td class="small">{{ $branch->name }}</td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm branch-qty" 
                                                               data-id="{{ $branch->id }}" placeholder="0" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-cyan text-white w-100 mt-3 fw-bold" onclick="submitBranchStock()">
                                    <i class="fas fa-plus me-2"></i>Add To Branches
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Stock Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">Current Stock</h6>
                    <button class="btn btn-sm btn-light border text-muted px-3" onclick="loadStockLevels()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="stockLevels">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-cyan mb-3"></i>
                            <p class="text-muted">Loading stock information...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-cyan { background-color: #198754; border-color: #198754; } /* Syncing with the green theme in image */
    .btn-cyan:hover { background-color: #157347; border-color: #157347; }
    .stock-card {
        border-radius: 12px;
        transition: transform 0.2s;
        border: none;
    }
    .stock-card:hover { transform: translateY(-3px); }
    .bg-premium-green {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
    }
    .table-responsive::-webkit-scrollbar { width: 4px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
</style>

@endsection

@push('scripts')
<script>
async function submitBranchStock(){
    const branchItems = [];
    $('.branch-qty').each(function() {
        const val = parseInt($(this).val());
        if (val > 0) {
            branchItems.push({
                id: $(this).data('id'),
                qty: val
            });
        }
    });

    if(branchItems.length === 0){
        showToast('Please enter quantity for at least one branch.', 'warning');
        return;
    }

    const ids = branchItems.map(i => i.id);
    const qty = branchItems.map(i => i.qty);

    const url = `{{ route('erp.products.variations.stock.branches', [$product->id, $variation->id]) }}`;
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                'Accept': 'application/json', 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({ branches: ids, quantities: qty })
        });
        const data = await res.json();
        showToast(data.message || 'Stock updated successfully', 'success');
        $('.branch-qty').val(''); // Clear inputs
        loadStockLevels();
    } catch (e) {
        showToast('Failed to update stock', 'error');
    }
}

async function submitWarehouseStock(){
    // Warehouse stock management disabled for simplified workflow
    showToast('Warehouse management is currently disabled.', 'info');
}

async function loadStockLevels(){
    const url = `{{ route('erp.products.variations.stock.levels', [$product->id, $variation->id]) }}`;
    const res = await fetch(url);
    const data = await res.json();
    
    const stockLevelsDiv = document.getElementById('stockLevels');
    
    let html = `
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card stock-card bg-premium-green text-white shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="small opacity-75 text-uppercase fw-bold mb-1">Total Stock</div>
                        <h2 class="display-4 fw-bold mb-0">${data.total_stock || 0}</h2>
                        <div class="small opacity-75 mt-1">units</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stock-card bg-premium-green text-white shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="small opacity-75 text-uppercase fw-bold mb-1">Available Stock</div>
                        <h2 class="display-4 fw-bold mb-0">${data.available_stock || 0}</h2>
                        <div class="small opacity-75 mt-1">units</div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Branch stocks section
    html += `<div class="row"><div class="col-md-12 mb-4">`;
    html += `<h6 class="fw-bold d-flex align-items-center mb-3"><i class="fas fa-store me-2 text-primary"></i> Current Stock Levels (By Branch)</h6>`;
    if (data.branch_stocks && data.branch_stocks.length > 0) {
        html += `<div class="table-responsive border rounded"><table class="table table-sm table-hover mb-0">
            <thead class="bg-light"><tr><th>Branch</th><th class="text-center">Total</th><th class="text-center">Available</th></tr></thead>
            <tbody>`;
        data.branch_stocks.forEach(stock => {
            html += `<tr>
                <td class="small fw-bold ps-3">${stock.branch_name}</td>
                <td class="text-center"><span class="badge bg-light text-dark border">${stock.quantity}</span></td>
                <td class="text-center"><span class="badge bg-success">${stock.available_quantity || stock.quantity}</span></td>
            </tr>`;
        });
        html += `</tbody></table></div>`;
    } else {
        html += `<div class="alert alert-light border small text-muted"><i class="fas fa-info-circle me-2"></i>No branch stock records.</div>`;
    }
    html += `</div></div>`;
    
    stockLevelsDiv.innerHTML = html;
}

// showToast fallback
window.showToast = function(message, type = 'info') {
    const bg = type === 'success' ? '#198754' : (type === 'error' ? '#dc3545' : '#17a2b8');
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
    }).showToast();
}

document.addEventListener('DOMContentLoaded', loadStockLevels);
</script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
@endpush


