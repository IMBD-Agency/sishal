@extends('erp.master')

@section('title', 'Update Product | ' . $product->name)

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.list') }}" class="text-decoration-none text-muted">Products</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Edit</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <h4 class="fw-bold mb-0 text-dark">{{ $product->name }}</h4>
                    <span class="badge bg-light text-secondary border small">{{ $product->sku }}</span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('product.list') }}" class="btn btn-light border px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Catalog
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="editProductForm">
            @csrf
            @method('PATCH')
            
            <div class="row g-4">
                <!-- Left Column: Core Data -->
                <div class="col-lg-8">
                    <!-- Basic Info -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-edit me-2 text-primary"></i>Core Details</h6>
                        </div>
                        <div class="card-body p-4">
                            @if ($errors->any())
                                <div class="alert alert-danger mb-4 border-0 bg-danger bg-opacity-10 text-danger">
                                    <ul class="mb-0 small fw-bold">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(session('success'))
                                <div class="alert alert-success mb-4 border-0 bg-success bg-opacity-10 text-success fw-bold">{{ session('success') }}</div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="name" class="form-label fw-bold small text-uppercase">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required value="{{ old('name', $product->name) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="sku" class="form-label fw-bold small text-uppercase">Style Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="sku" name="sku" required value="{{ old('sku', $product->sku) }}">
                                </div>
                                <div class="col-md-12">
                                    <label for="slug" class="form-label fw-bold small text-uppercase">URL Slug</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">/product/</span>
                                        <input type="text" class="form-control" id="slug" name="slug" required value="{{ old('slug', $product->slug) }}">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">SEO Friendly URL identifier.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descriptions -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-align-left me-2 text-primary"></i>Content & Description</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="short_desc" class="form-label fw-bold small text-uppercase">Short Highlight</label>
                                <textarea name="short_desc" id="short_desc" class="form-control" rows="4">{{ old('short_desc', $product->short_desc) }}</textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Full Details</label>
                                <input type="hidden" name="description" id="description_input" value="{{ old('description', $product->description) }}">
                                <div id="quill_description_edit" style="height: 250px; background: #fff;"></div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small text-uppercase">Technical Features</label>
                                <input type="hidden" name="features" id="features_input" value="{{ old('features', $product->features) }}">
                                <div id="quill_features_edit" style="height: 200px; background: #fff;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div class="premium-card mb-4">
                         <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-sliders-h me-2 text-primary"></i>Technical Specs</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-attribute">
                                <i class="fas fa-plus me-1"></i>Add Row
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div id="attributes-container">
                                @php
                                    $existingAttributes = $product->productAttributes;
                                @endphp
                                @foreach($existingAttributes as $index => $attr)
                                    <div class="attribute-row row g-2 mb-2 align-items-end">
                                        <div class="col-md-5">
                                            <label class="small text-muted mb-1">Specification Name</label>
                                            <select class="form-select attribute-select" name="attributes[{{ $index }}][attribute_id]">
                                                <option value="">Choose...</option>
                                                @foreach($attributes as $globalAttr)
                                                    <option value="{{ $globalAttr->id }}" {{ $globalAttr->id == $attr->id ? 'selected' : '' }}>{{ $globalAttr->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="small text-muted mb-1">Value</label>
                                            <input type="text" class="form-control" name="attributes[{{ $index }}][value]" value="{{ $attr->pivot->value }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100 remove-attribute">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings -->
                <div class="col-lg-4">
                     <!-- Status & Visibility -->
                    <div class="premium-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label for="status" class="form-label fw-bold small text-uppercase mb-0">Publish Status</label>
                                <select class="form-select form-select-sm w-auto border-0 bg-light fw-bold text-primary" id="status" name="status">
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>
                            <div class="form-check form-switch p-3 bg-light rounded d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold small text-uppercase cursor-pointer" for="show_in_ecommerce">
                                    Show Online
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="show_in_ecommerce" name="show_in_ecommerce" value="1" {{ old('show_in_ecommerce', $product->show_in_ecommerce) ? 'checked' : '' }} style="width: 2.5rem; height: 1.25rem; cursor: pointer;">
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="manage_stock" name="manage_stock" value="1" {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-muted cursor-pointer" for="manage_stock">Track Stock Level</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_variations" name="has_variations" value="1" {{ old('has_variations', $product->has_variations) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-muted cursor-pointer" for="has_variations">Has Variations (Size/Color)</label>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-tag me-2 text-primary"></i>Pricing Strategy</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="price" class="form-label fw-bold small text-uppercase">Selling Price (MRP) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">৳</span>
                                        <input type="number" step="0.01" class="form-control" id="price" name="price" required value="{{ old('price', $product->price) }}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="wholesale_price" class="form-label fw-bold small text-uppercase">Wholesale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">৳</span>
                                        <input type="number" step="0.01" class="form-control" id="wholesale_price" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="cost" class="form-label fw-bold small text-uppercase">Cost Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="cost" name="cost" required value="{{ old('cost', $product->cost) }}">
                                </div>
                                <div class="col-6">
                                    <label for="discount" class="form-label fw-bold small text-uppercase">Discount</label>
                                    <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="{{ old('discount', $product->discount) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Organization -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-sitemap me-2 text-primary"></i>Organization</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Category <span class="text-danger">*</span></label>
                                <select class="form-select select2-init" id="category_id" name="category_id" required style="width: 100%">
                                    @if($product->category)
                                        <option value="{{ $product->category->id }}" selected>{{ $product->category->full_path_name }}</option>
                                    @endif
                                    <!-- AJAX Search -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Brand</label>
                                <select class="form-select select2-simple" id="brand_id" name="brand_id">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold small text-uppercase">Season</label>
                                    <select class="form-select form-select-sm" id="season_id" name="season_id">
                                        <option value="">All</option>
                                        @foreach($seasons as $season)
                                            <option value="{{ $season->id }}" {{ $product->season_id == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small text-uppercase">Gender</label>
                                    <select class="form-select form-select-sm" id="gender_id" name="gender_id">
                                        <option value="">All</option>
                                        @foreach($genders as $gender)
                                            <option value="{{ $gender->id }}" {{ $product->gender_id == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media Assets -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-images me-2 text-primary"></i>Media Assets</h6>
                        </div>
                        <div class="card-body p-4">
                            <!-- Main Image -->
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold small text-uppercase d-block text-start">Main Cover</label>
                                @if($product->image)
                                    <div class="mb-2 p-1 border rounded d-inline-block bg-white shadow-sm">
                                        <img src="{{ asset($product->image) }}" alt="Cover" style="height: 120px; width: 120px; object-fit: cover; border-radius: 4px;">
                                    </div>
                                @endif
                                <input class="form-control" type="file" id="image" name="image" accept="image/*">
                            </div>

                            <!-- Gallery -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase d-block">Gallery Images</label>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @foreach($product->galleries as $gallery)
                                        <div class="position-relative" style="display: inline-block;">
                                            <img src="{{ asset($gallery->image) }}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-danger p-0 rounded-circle position-absolute top-0 start-100 translate-middle gallery-delete-btn" 
                                                    data-action="{{ route('product.gallery.delete', $gallery->id) }}" style="width: 20px; height: 20px; line-height: 1;">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                                <input class="form-control" type="file" id="gallery" name="gallery[]" accept="image/*" multiple>
                            </div>

                            <!-- Size Chart -->
                             <div class="mb-0">
                                <label class="form-label fw-bold small text-uppercase">Size Chart</label>
                                @if($product->size_chart)
                                    <div class="mb-2 position-relative d-inline-block">
                                        <img src="{{ asset($product->size_chart) }}" id="size_chart_existing_preview" style="max-width: 100px; border-radius: 6px; border: 1px solid #eee;">
                                        <button type="button" class="btn btn-sm btn-circle btn-danger position-absolute top-0 start-100 translate-middle" id="size_chart_delete_existing_btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="delete_size_chart" id="delete_size_chart" value="0">
                                @endif
                                <input class="form-control" type="file" id="size_chart" name="size_chart" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="col-12 mt-0">
                    <div class="premium-card">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                             <input type="hidden" name="form_mode" value="edit"> <!-- Optional helper -->
                            <a href="{{ route('product.list') }}" class="btn btn-light fw-bold text-muted">Discard Changes</a>
                            <button type="submit" class="btn btn-create-premium px-5 py-2">
                                <i class="fas fa-sync-alt me-2"></i>Update Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Hidden Form for Gallery Deletion -->
        <form id="galleryDeleteForm" method="POST" style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

<script>
function slugify(text) {
    return text.toString().toLowerCase().trim().replace(/[\s\W-]+/g, '-').replace(/^-+|-+$/g, '');
}

$(document).ready(function() {
    $('.select2-simple, .attribute-select').select2({ width: '100%' });
    
    // Global Focus for Select2
    $(document).on('select2:open', () => {
        const searchField = document.querySelector('.select2-search__field');
        if (searchField) {
            searchField.focus();
        }
    });
    
    $('#category_id').select2({
        placeholder: 'Search Categories...',
        ajax: {
            url: '/erp/categories/search',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term }; },
            processResults: function(data) { 
                return { 
                    results: data.map(function(cat){ 
                        return { id: cat.id, text: cat.display_name || cat.name }; 
                    }) 
                }; 
            },
            cache: true
        },
        minimumInputLength: 1
    });

    $('#name').on('input', function(){
        // Only auto-slug if user hasn't manually edited it (optional logic, kept simple for now)
         // $('#slug').val(slugify($(this).val())); 
    });

    // Editors
    ClassicEditor.create(document.querySelector('#short_desc'), {
        toolbar: { items: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'] }
    }).then(editor => { window.shortDescEditor = editor; }).catch(err => console.error(err));

    var quill = new Quill('#quill_description_edit', {
        theme: 'snow',
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic','underline'], [{ list:'ordered' }, { list:'bullet' }], ['clean']] }
    });
    var initial = document.getElementById('description_input').value || '';
    if (initial) { quill.root.innerHTML = initial; }

    var quillFeatures = new Quill('#quill_features_edit', {
        theme: 'snow',
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic'], [{ list:'ordered' }, { list:'bullet' }], ['clean']] }
    });
    var initialFeatures = document.getElementById('features_input').value || '';
    if (initialFeatures) { quillFeatures.root.innerHTML = initialFeatures; }

    $('#editProductForm').on('submit', function() {
        if (window.shortDescEditor) { document.getElementById('short_desc').value = window.shortDescEditor.getData(); }
        document.getElementById('description_input').value = quill.root.innerHTML;
        document.getElementById('features_input').value = quillFeatures.root.innerHTML;
    });

    // Gallery Delete
    $(document).on('click', '.gallery-delete-btn', function(){
        if(confirm('Delete this image permanently?')) {
            var action = $(this).data('action');
            var form = document.getElementById('galleryDeleteForm');
            form.setAttribute('action', action);
            form.submit();
        }
    });

    // Size Chart Delete Logic
    $('#size_chart_delete_existing_btn').on('click', function() {
        if (confirm('Remove current size chart?')) {
            $('#size_chart_existing_preview').closest('div').hide();
            $('#delete_size_chart').val('1');
            $('#size_chart').val('');
        }
    });

    // Specs Attributes Logic
    let attributeCount = {{ count($product->productAttributes) > 0 ? count($product->productAttributes) : 1 }};
    $('#add-attribute').on('click', function(){
        const row = `
            <div class="attribute-row row g-2 mb-2 align-items-end">
                <div class="col-md-5">
                    <select class="form-select attribute-select" name="attributes[${attributeCount}][attribute_id]">
                        <option value="">Choose...</option>
                        @foreach($attributes as $globalAttr)
                            <option value="{{ $globalAttr->id }}">{{ $globalAttr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="attributes[${attributeCount}][value]" placeholder="Value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 remove-attribute">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;
        $('#attributes-container').append(row);
        // Initialize Select2 on the new row
        $('#attributes-container .attribute-row:last-child .attribute-select').select2({ width: '100%' });
        attributeCount++;
        $('.remove-attribute').show();
    });

    $(document).on('click', '.remove-attribute', function(){
        $(this).closest('.attribute-row').remove();
    });
});
</script>
@endpush
@endsection
