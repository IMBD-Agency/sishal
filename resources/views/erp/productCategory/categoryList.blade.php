@extends('erp.master')

@section('title', 'Category Network')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Categories</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Category Network</h4>
                <p class="text-muted small mb-0">Manage your product grouping and hierarchy</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-3 align-items-md-center">
                <form action="" method="GET" class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="search" name="search" class="form-control" placeholder="Quick find category..." value="{{ request('search') }}">
                </form>
                <button class="btn btn-create-premium" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus-circle me-2"></i>New Category
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="premium-card">
            <div class="card-body p-0">
                <!-- Mobile View -->
                <div class="d-md-none p-3">
                    @forelse ($categories as $idx => $category)
                        <div class="mobile-card shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="thumbnail-box me-3">
                                    @if($category->image)
                                        <img src="{{ asset($category->image) }}" alt="">
                                    @else
                                        <i class="fas fa-layer-group text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0">{{ $category->name }}</h6>
                                    <span class="status-pill {{ $category->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                        <i class="fas {{ $category->status == 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" data-update-url="{{ route('category.update', $category->id) }}" {{ $category->status == 'active' ? 'checked' : '' }} onchange="toggleStatus(this, '{{ route('category.update', $category->id) }}')">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-action flex-grow-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </button>
                                <form action="{{ route('category.delete', $category->id) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('Archive this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-action w-100 text-danger">
                                        <i class="fas fa-trash-alt me-2"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-light mb-3"></i>
                            <p class="text-muted">No categories found in the system</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop Table -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table premium-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">SL</th>
                                <th style="width: 100px;">Visual</th>
                                <th>Category Profile</th>
                                <th>Identifier</th>
                                <th>Live Status</th>
                                <th class="text-end">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $idx => $category)
                                <tr>
                                    <td class="text-muted fw-500">{{ $categories->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="thumbnail-box">
                                            @if($category->image)
                                                <img src="{{ asset($category->image) }}" alt="">
                                            @else
                                                <i class="fas fa-layer-group text-light"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $category->name }}</div>
                                        <div class="text-muted small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $category->description ?: 'No description provided' }}
                                        </div>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded text-primary" style="font-size: 0.8rem;">/{{ $category->slug }}</code>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="status-pill {{ $category->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                                <i class="fas {{ $category->status == 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                {{ ucfirst($category->status) }}
                                            </span>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" data-update-url="{{ route('category.update', $category->id) }}" {{ $category->status == 'active' ? 'checked' : '' }} onchange="toggleStatus(this, '{{ route('category.update', $category->id) }}')">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}" title="Edit Node">
                                                <i class="fas fa-pen-nib"></i>
                                            </button>
                                            <form action="{{ route('category.delete', $category->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Archive this category?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action" title="Remove Node">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                            <h5 class="text-muted">No Categories Registered</h5>
                                            <p class="text-muted small">Start by creating your first category network</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Custom Pagination -->
            @if($categories->hasPages())
                <div class="card-footer bg-white py-3 border-top-0 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing <span class="fw-600 text-dark">{{ $categories->firstItem() }}</span> to <span class="fw-600 text-dark">{{ $categories->lastItem() }}</span> of <span class="fw-600 text-dark">{{ $categories->total() }}</span> categories
                        </div>
                        <div>
                            {{ $categories->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Category Management Modals --}}
@foreach ($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fas fa-edit me-3 opacity-75"></i>Modify Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('category.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_category_name_{{ $category->id }}" name="name" value="{{ $category->name }}" required placeholder="e.g. Mens Fashion">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Public Identifier (Slug) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="font-size: 0.8rem;">shop.com/</span>
                                <input type="text" class="form-control" id="edit_category_slug_{{ $category->id }}" name="slug" value="{{ $category->slug ?? '' }}" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Briefly describe this category...">{{ $category->description }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Visual Identity (Icon/Image)</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="thumbnail-box" style="width: 80px; height: 80px;">
                                    @if($category->image)
                                        <img src="{{ asset($category->image) }}" alt="" id="edit_preview_{{ $category->id }}">
                                    @else
                                        <i class="fas fa-image text-muted fa-2x"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" name="image" accept="image/*" onchange="previewImage(this, 'edit_preview_{{ $category->id }}')">
                                    <small class="text-muted">Recommended: 200x200px PNG/SVG</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Active Status</label>
                            <select class="form-select" name="status">
                                <option value="active" @if($category->status == 'active') selected @endif>Online & Visible</option>
                                <option value="inactive" @if($category->status == 'inactive') selected @endif>Offline / Hidden</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-create-premium px-4">
                            <i class="fas fa-save me-2"></i>Update Node
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Registration Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="fas fa-layer-group me-3 opacity-75"></i>Register Category
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="name" required placeholder="e.g. Summer Collection">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Public Identifier (Slug) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted" style="font-size: 0.8rem;">shop.com/</span>
                            <input type="text" class="form-control" id="category_slug" name="slug" required placeholder="summer-collection">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Explain the focus of this category..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Visual Identity</label>
                        <input class="form-control" type="file" name="image" accept="image/*">
                        <small class="text-muted d-block mt-1">Supports: JPG, PNG, SVG (Max 2MB)</small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Initial Status</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Active & Ready</option>
                            <option value="inactive">Draft / Hidden</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-create-premium px-4">
                        <i class="fas fa-check-circle me-2"></i>Create Node
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function slugify(text) {
        return text.toString().toLowerCase().trim()
            .replace(/[\s\W-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleStatus(checkboxEl, url) {
        const isActive = checkboxEl.checked;
        const status = isActive ? 'active' : 'inactive';
        
        // Update visual pill while waiting
        const pill = checkboxEl.closest('div').querySelector('.status-pill');
        if(pill) {
            pill.className = `status-pill ${status === 'active' ? 'status-active' : 'status-inactive'}`;
            pill.innerHTML = `<i class="fas ${status === 'active' ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${status.charAt(0).toUpperCase() + status.slice(1)}`;
        }

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                checkboxEl.checked = !isActive;
                alert('Connection failure: Status not synchronized');
            }
        })
        .catch(() => {
            checkboxEl.checked = !isActive;
            alert('Cloud sync failed');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('category_name');
        const slugInput = document.getElementById('category_slug');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', () => slugInput.value = slugify(nameInput.value));
        }

        @foreach ($categories as $category)
            (function() {
                const editName = document.getElementById('edit_category_name_{{ $category->id }}');
                const editSlug = document.getElementById('edit_category_slug_{{ $category->id }}');
                if (editName && editSlug) {
                    editName.addEventListener('input', () => editSlug.value = slugify(editName.value));
                }
            })();
        @endforeach
    });
</script>
@endpush