@extends('erp.master')

@section('title', 'Product Variations')

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
                        <i class="fas fa-layer-group me-2"></i>
                        Product Variations - {{ $product->name }}
                    </h4>
                    <div>
                        <a href="{{ route('product.list') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Products
                        </a>
                        <a href="{{ route('erp.products.variations.create', $product->id) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Variation
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($product->variations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SKU</th>
                                        <th>Name</th>
                                        <th>Attributes</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Default</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->variations as $variation)
                                        <tr>
                                            <td>
                                                <code>{{ $variation->sku }}</code>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($variation->image)
                                                        @php($imgPath = ltrim($variation->image,'/'))
                                                        <img src="{{ asset($imgPath) }}" 
                                                             alt="{{ $variation->name }}" 
                                                             class="rounded me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $variation->name }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($variation->combinations as $combination)
                                                    <span class="badge bg-info me-1">
                                                        {{ $combination->attribute->name }}: {{ $combination->attributeValue->value }}
                                                        @if($combination->attribute->is_color && $combination->attributeValue->color_code)
                                                            <span class="color-indicator" 
                                                                  style="background-color: {{ $combination->attributeValue->color_code }}; 
                                                                         width: 12px; height: 12px; 
                                                                         display: inline-block; 
                                                                         border-radius: 50%; 
                                                                         margin-left: 5px;"></span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if($variation->price)
                                                    <span class="text-success fw-bold">${{ number_format($variation->final_price, 2) }}</span>
                                                @else
                                                    <span class="text-muted">${{ number_format($product->price, 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $variation->isInStock() ? 'success' : 'danger' }}">
                                                    {{ $variation->total_stock }} in stock
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $variation->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($variation->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($variation->is_default)
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-star"></i> Default
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('erp.products.variations.show', [$product->id, $variation->id]) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('erp.products.variations.edit', [$product->id, $variation->id]) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('erp.products.variations.stock', [$product->id, $variation->id]) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Manage Stock">
                                                        <i class="fas fa-boxes"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-{{ $variation->status === 'active' ? 'secondary' : 'success' }}"
                                                            onclick="toggleStatus({{ $variation->id }})" 
                                                            title="{{ $variation->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $variation->status === 'active' ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteVariation({{ $variation->id }})" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No variations found</h5>
                            <p class="text-muted">This product doesn't have any variations yet.</p>
                            <a href="{{ route('erp.products.variations.create', $product->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create First Variation
                            </a>
                        </div>
                    @endif
                </div>
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
