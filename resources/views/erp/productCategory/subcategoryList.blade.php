@extends('erp.master')

@section('title', 'Subcategory Hierarchy')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Subcategory Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-5">
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('category.list') }}" class="text-decoration-none text-muted">Categories</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Sub Categories</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark text-nowrap">Subcategory Hierarchy</h4>
                <p class="text-muted small mb-0">Fine-tune your catalog depth and navigation</p>
            </div>
            <div class="col-md-7 text-md-end mt-3 mt-md-0">
                <form action="" method="GET" class="search-group row g-2 justify-content-md-end">
                    <div class="col-sm-4">
                        <select name="parent_id" class="form-select">
                            <option value="">All Regions</option>
                            @foreach($parentCategories as $pc)
                                <option value="{{ $pc->id }}" @if(request('parent_id')==$pc->id) selected @endif>{{ $pc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="position-relative">
                            <input type="search" name="search" class="form-control ps-5" placeholder="Search..." value="{{ request('search') }}">
                            <i class="fas fa-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-create-premium">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                    <div class="col-sm-auto">
                        <button type="button" class="btn btn-create-premium" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                            <i class="fas fa-plus-circle me-2"></i>New Subnode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="premium-card">
            <div class="card-body p-0">
                <!-- Mobile View -->
                <div class="d-md-none p-3">
                    @forelse ($subcategories as $idx => $subcategory)
                        <div class="mobile-card shadow-sm">
                            <div class="d-flex align-items-center mb-3">
                                <div class="thumbnail-box me-3">
                                    @if($subcategory->image)
                                        <img src="{{ asset($subcategory->image) }}" alt="">
                                    @else
                                        <i class="fas fa-list-ul text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="fw-bold mb-0 text-nowrap">{{ $subcategory->name }}</h6>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" data-update-url="{{ route('subcategory.update', $subcategory->id) }}" {{ $subcategory->status == 'active' ? 'checked' : '' }} onchange="toggleSubStatus(this, '{{ route('subcategory.update', $subcategory->id) }}')">
                                        </div>
                                    </div>
                                    <span class="category-tag small d-inline-block mt-1">{{ $subcategory->parent?->name }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-action flex-grow-1" data-bs-toggle="modal" data-bs-target="#editSubcategoryModal{{ $subcategory->id }}">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </button>
                                <form action="{{ route('subcategory.delete', $subcategory->id) }}" method="POST" class="flex-grow-1" onsubmit="return confirm('Archive this node?')">
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
                            <p class="text-muted">No child categories discovered</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop table -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table premium-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">SL</th>
                                <th style="width: 100px;">Icon</th>
                                <th style="width: 250px;">Parent Domain</th>
                                <th>Subnode Profile</th>
                                <th>Identifier</th>
                                <th>Live Sync</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subcategories as $idx => $subcategory)
                                <tr>
                                    <td class="text-muted">{{ $subcategories->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="thumbnail-box">
                                            @if($subcategory->image)
                                                <img src="{{ asset($subcategory->image) }}" alt="">
                                            @else
                                                <i class="fas fa-list-ul text-light"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-tag">
                                            <i class="fas fa-folder me-2 opacity-50"></i>{{ $subcategory->parent?->name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $subcategory->name }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">
                                            {{ $subcategory->description ?: 'No operational data' }}
                                        </div>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded text-primary" style="font-size: 0.8rem;">/{{ $subcategory->slug }}</code>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="status-pill {{ $subcategory->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                                <i class="fas {{ $subcategory->status == 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                {{ ucfirst($subcategory->status) }}
                                            </span>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" data-update-url="{{ route('subcategory.update', $subcategory->id) }}" {{ $subcategory->status == 'active' ? 'checked' : '' }} onchange="toggleSubStatus(this, '{{ route('subcategory.update', $subcategory->id) }}')">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#editSubcategoryModal{{ $subcategory->id }}">
                                                <i class="fas fa-pen-nib"></i>
                                            </button>
                                            <form action="{{ route('subcategory.delete', $subcategory->id) }}" method="POST" onsubmit="return confirm('Archive this node?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-5 text-muted">
                                            <i class="fas fa-project-diagram fa-3x mb-3 text-light"></i>
                                            <h5>The Hierarchy is Quiet</h5>
                                            <p class="small">Populate your categories with subnodes to structure your catalog</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Pagination -->
            @if($subcategories->hasPages())
                <div class="card-footer bg-white border-top-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-muted small mb-0">Showing {{ $subcategories->firstItem() }} to {{ $subcategories->lastItem() }} of total {{ $subcategories->total() }} results</p>
                        {{ $subcategories->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Subcategory Management Modals --}}
@foreach ($subcategories as $subcategory)
    <div class="modal fade" id="editSubcategoryModal{{ $subcategory->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fas fa-edit me-3 opacity-75"></i>Modify Subnode
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('subcategory.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label">Subnode Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_subcategory_name_{{ $subcategory->id }}" name="name" value="{{ $subcategory->name }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Primary Domain <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_subcategory_parent_{{ $subcategory->id }}" name="parent_id" required>
                                @foreach($parentCategories as $pc)
                                    <option value="{{ $pc->id }}" data-slug="{{ $pc->slug }}" @if($subcategory->parent_id == $pc->id) selected @endif>{{ $pc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Network Identifier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_subcategory_slug_{{ $subcategory->id }}" name="slug" value="{{ $subcategory->slug ?? '' }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Operational Notes</label>
                            <textarea class="form-control" name="description" rows="2">{{ $subcategory->description }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Visual Instance</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="thumbnail-box" style="width: 70px; height: 70px;">
                                    @if($subcategory->image)
                                        <img src="{{ asset($subcategory->image) }}" alt="" id="edit_sub_preview_{{ $subcategory->id }}">
                                    @else
                                        <i class="fas fa-image text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" name="image" accept="image/*" onchange="previewImage(this, 'edit_sub_preview_{{ $subcategory->id }}')">
                                </div>
                            </div>
                        </div>
                        <div class="">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" @if($subcategory->status == 'active') selected @endif>Active Node</option>
                                <option value="inactive" @if($subcategory->status == 'inactive') selected @endif>Sleeping Node</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-create-premium px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Subnode Creation Modal -->
<div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="fas fa-plus-circle me-3 opacity-75"></i>Initialize Subnode
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('subcategory.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Node Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subcategory_name" name="name" required placeholder="e.g. Graphic Tees">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Parent Territory <span class="text-danger">*</span></label>
                        <select class="form-select" id="subcategory_parent_id" name="parent_id" required>
                            @foreach($parentCategories as $pc)
                                <option value="{{ $pc->id }}" data-slug="{{ $pc->slug }}">{{ $pc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Identifier (Slug) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subcategory_slug" name="slug" required placeholder="e.g. graphic-tees">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Describe the node's scope..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Representative Image</label>
                        <input class="form-control" type="file" name="image" accept="image/*">
                    </div>
                    <div class="">
                        <label class="form-label">Initial Status</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Active & Ready</option>
                            <option value="inactive">Draft / Hidden</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-create-premium px-4">Register Node</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function slugify(text) {
        return text.toString().toLowerCase().trim().replace(/[\s\W-]+/g, '-').replace(/^-+|-+$/g, '');
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(previewId);
                if(img) img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleSubStatus(checkboxEl, url) {
        const isActive = checkboxEl.checked;
        const status = isActive ? 'active' : 'inactive';
        
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
                alert('Connection failure: Node status desynchronized');
            }
        })
        .catch(() => {
            checkboxEl.checked = !isActive;
            alert('Cloud sync failed');
        });
    }

    function generateSubcategorySlug(nameInput, parentSelect, slugInput) {
        if (!nameInput || !parentSelect || !slugInput) return;
        const name = nameInput.value.trim();
        const parentOption = parentSelect.options[parentSelect.selectedIndex];
        const parentSlug = parentOption ? parentOption.getAttribute('data-slug') : '';
        if (name) {
            const subcategorySlug = slugify(name);
            slugInput.value = parentSlug ? `${parentSlug}-${subcategorySlug}` : subcategorySlug;
        } else {
            slugInput.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const createName = document.getElementById('subcategory_name');
        const createParent = document.getElementById('subcategory_parent_id');
        const createSlug = document.getElementById('subcategory_slug');
        
        if (createName && createParent && createSlug) {
            createName.addEventListener('input', () => generateSubcategorySlug(createName, createParent, createSlug));
            createParent.addEventListener('change', () => generateSubcategorySlug(createName, createParent, createSlug));
        }

        @foreach ($subcategories as $subcategory)
            (function() {
                const editName = document.getElementById('edit_subcategory_name_{{ $subcategory->id }}');
                const editParent = document.getElementById('edit_subcategory_parent_{{ $subcategory->id }}');
                const editSlug = document.getElementById('edit_subcategory_slug_{{ $subcategory->id }}');
                if (editName && editParent && editSlug) {
                    editName.addEventListener('input', () => generateSubcategorySlug(editName, editParent, editSlug));
                    editParent.addEventListener('change', () => generateSubcategorySlug(editName, editParent, editSlug));
                }
            })();
        @endforeach
    });
</script>
@endpush
