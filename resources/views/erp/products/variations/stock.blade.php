@extends('erp.master')

@section('title', 'Manage Variation Stock')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-boxes me-2"></i>
                                Manage Stock - {{ $product->name }} ({{ $variation->name }})
                            </h4>
                            <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h5 class="mb-0">Add to Branches</h5></div>
                                        <div class="card-body">
                                            <form id="branchStockForm">
                                                @csrf
                                                <input type="hidden" id="branch_product_id" value="{{ $product->id }}">
                                                <input type="hidden" id="branch_variation_id" value="{{ $variation->id }}">
                                                <div class="mb-2">
                                                    <label class="form-label">Branches</label>
                                                    <select class="form-select" id="branches" multiple>
                                                        @foreach($branches as $branch)
                                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Quantities (comma separated matching order)</label>
                                                    <input class="form-control" id="branch_quantities" placeholder="e.g. 5,10"/>
                                                </div>
                                                <button type="button" class="btn btn-warning" onclick="submitBranchStock()">
                                                    <i class="fas fa-plus me-1"></i> Add To Branches
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h5 class="mb-0">Add to Warehouses</h5></div>
                                        <div class="card-body">
                                            <form id="warehouseStockForm">
                                                @csrf
                                                <input type="hidden" id="warehouse_product_id" value="{{ $product->id }}">
                                                <input type="hidden" id="warehouse_variation_id" value="{{ $variation->id }}">
                                                <div class="mb-2">
                                                    <label class="form-label">Warehouses</label>
                                                    <select class="form-select" id="warehouses" multiple>
                                                        @foreach($warehouses as $warehouse)
                                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Quantities (comma separated matching order)</label>
                                                    <input class="form-control" id="warehouse_quantities" placeholder="e.g. 5,10"/>
                                                </div>
                                                <button type="button" class="btn btn-warning" onclick="submitWarehouseStock()">
                                                    <i class="fas fa-plus me-1"></i> Add To Warehouses
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0">Current Stock</h5>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="loadStockLevels()">
                                                <i class="fas fa-sync-alt me-1"></i> Refresh
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <pre id="stockLevels" class="bg-light p-3 rounded" style="min-height:120px"></pre>
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
@endsection

@push('scripts')
<script>
function parseIds(selector){
    return Array.from(document.querySelector(selector).selectedOptions).map(o => o.value);
}
function parseQuantities(selector){
    const raw = document.querySelector(selector).value.trim();
    if(!raw) return [];
    return raw.split(',').map(s => parseInt(s.trim(), 10)).filter(n => !isNaN(n) && n > 0);
}
async function submitBranchStock(){
    const ids = parseIds('#branches');
    const qty = parseQuantities('#branch_quantities');
    if(ids.length === 0 || qty.length !== ids.length){
        alert('Please select branches and provide matching quantities.');
        return;
    }
    const url = `{{ route('erp.products.variations.stock.branches', [$product->id, $variation->id]) }}`;
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ branches: ids, quantities: qty })
    });
    const data = await res.json();
    alert(data.message || 'Done');
    loadStockLevels();
}
async function submitWarehouseStock(){
    const ids = parseIds('#warehouses');
    const qty = parseQuantities('#warehouse_quantities');
    if(ids.length === 0 || qty.length !== ids.length){
        alert('Please select warehouses and provide matching quantities.');
        return;
    }
    const url = `{{ route('erp.products.variations.stock.warehouses', [$product->id, $variation->id]) }}`;
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ warehouses: ids, quantities: qty })
    });
    const data = await res.json();
    alert(data.message || 'Done');
    loadStockLevels();
}
async function loadStockLevels(){
    const url = `{{ route('erp.products.variations.stock.levels', [$product->id, $variation->id]) }}`;
    const res = await fetch(url);
    const data = await res.json();
    document.getElementById('stockLevels').textContent = JSON.stringify(data, null, 2);
}
document.addEventListener('DOMContentLoaded', loadStockLevels);
</script>
@endpush


