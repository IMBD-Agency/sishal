@extends('erp.master')

@section('title', 'Product Variations')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-4">
            <!-- Header Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold d-flex align-items-center">
                        <i class="fas fa-layer-group me-2 text-dark"></i>
                        Product Variations - <span class="text-muted ms-1">{{ $product->name }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('product.list') }}" class="btn btn-secondary px-4 d-flex align-items-center">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                        <a href="{{ route('erp.products.variations.create', $product->id) }}" class="btn btn-success px-4 d-flex align-items-center">
                            <i class="fas fa-plus me-2"></i>Add Variation
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="variationTable">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="ps-4">Style No</th>
                                    <th>Name</th>
                                    <th>Attributes</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Default</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->variations as $variation)
                                    <tr>
                                        <td class="ps-4">
                                            <span style="color: #e83e8c; font-weight: 500;">{{ $variation->sku }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($variation->image)
                                                    <img src="{{ asset(ltrim($variation->image,'/')) }}" 
                                                         class="rounded border me-2" 
                                                         style="width: 32px; height: 32px; object-fit: cover;">
                                                @endif
                                                <span class="fw-bold">{{ $variation->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($variation->combinations as $combination)
                                                <span class="badge rounded-pill px-3 py-2 me-1" style="background-color: #0dcaf0; color: #fff; font-weight: 500; font-size: 0.75rem;">
                                                    {{ $combination->attribute->name }}: {{ $combination->attributeValue->value }}
                                                </span>
                                            @endforeach
                                        </td>

                                        <td>
                                            @php($stock = $variation->total_stock)
                                            <span class="badge px-3 py-2 rounded {{ $stock > 0 ? 'bg-success' : 'bg-danger' }}" style="font-weight: 500;">
                                                {{ $stock }} in stock
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge px-3 py-2 rounded bg-success" style="font-weight: 500;">
                                                {{ ucfirst($variation->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($variation->is_default)
                                                <i class="fas fa-check-circle text-success fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route('erp.products.variations.show', [$product->id, $variation->id]) }}" 
                                                   class="btn btn-sm p-0 d-flex align-items-center justify-content-center border" 
                                                   style="width: 28px; height: 28px; background: #fff; color: #0dcaf0;" title="View">
                                                    <i class="fas fa-eye fa-xs"></i>
                                                </a>
                                                <a href="{{ route('erp.products.variations.edit', [$product->id, $variation->id]) }}" 
                                                   class="btn btn-sm p-0 d-flex align-items-center justify-content-center border" 
                                                   style="width: 28px; height: 28px; background: #fff; color: #198754;" title="Edit">
                                                    <i class="fas fa-edit fa-xs"></i>
                                                </a>
                                                <a href="{{ route('erp.products.variations.stock', [$product->id, $variation->id]) }}" 
                                                   class="btn btn-sm p-0 d-flex align-items-center justify-content-center border" 
                                                   style="width: 28px; height: 28px; background: #fff; color: #ffc107;" title="Manage Stock">
                                                    <i class="fas fa-boxes fa-xs"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm p-0 d-flex align-items-center justify-content-center border"
                                                        style="width: 28px; height: 28px; background: #fff; color: #6c757d;"
                                                        onclick="toggleStatus({{ $variation->id }})" 
                                                        title="{{ $variation->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $variation->status === 'active' ? 'pause' : 'play' }} fa-xs"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm p-0 d-flex align-items-center justify-content-center border"
                                                        style="width: 28px; height: 28px; background: #fff; color: #dc3545;"
                                                        onclick="deleteVariation({{ $variation->id }})" 
                                                        title="Delete">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted mb-3">
                                                <i class="fas fa-layer-group fa-3x mb-3"></i>
                                                <h5>No variations found</h5>
                                                <p>This product doesn't have any variations yet.</p>
                                            </div>
                                            <a href="{{ route('erp.products.variations.create', $product->id) }}" class="btn btn-primary px-4">
                                                <i class="fas fa-plus me-2"></i>Create First Variation
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this variation? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(variationId) {
    if (confirm('Are you sure you want to toggle the status of this variation?')) {
        fetch(`/erp/products/{{ $product->id }}/variations/${variationId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error toggling status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error toggling status');
        });
    }
}

function deleteVariation(variationId) {
    document.getElementById('deleteForm').action = `/erp/products/{{ $product->id }}/variations/${variationId}`;
    const modalElement = document.getElementById('deleteModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        console.error('Delete modal element not found');
    }
}
</script>
@endpush
