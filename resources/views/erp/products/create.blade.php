@extends('erp.master')

@section('title', 'New Product Entry')

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
                        <li class="breadcrumb-item active text-primary fw-600">Create New</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Add New Product</h4>
                <p class="text-muted small mb-0">Create a new inventory item with full specifications</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('product.list') }}" class="btn btn-light border px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Catalog
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" id="createProductForm">
            @csrf
            
            <div class="row g-4">
                <!-- Left Column: Basic Info & Specs -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-info-circle me-2 text-primary"></i>Core Details</h6>
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
                                    <input type="text" class="form-control" id="name" name="name" required value="{{ old('name') }}" placeholder="e.g. Premium Cotton T-Shirt">
                                </div>
                                <div class="col-md-4">
                                    <label for="sku" class="form-label fw-bold small text-uppercase">Style Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="sku" name="sku" required value="{{ old('sku') }}" placeholder="e.g. SN-2024-001">
                                </div>
                                <div class="col-md-12">
                                    <label for="slug" class="form-label fw-bold small text-uppercase">URL Slug</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">/product/</span>
                                        <input type="text" class="form-control" id="slug" name="slug" required value="{{ old('slug') }}" placeholder="auto-generated-slug">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Unique URL identifier for SEO.</small>
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
                                <textarea name="short_desc" id="short_desc" class="form-control" rows="4">{{ old('short_desc') }}</textarea>
                                <small class="text-muted">Brief summary for listing pages.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Full Details</label>
                                <input type="hidden" name="description" id="description_input" value="{{ old('description') }}">
                                <div id="quill_description_create" style="height: 250px; background: #fff;"></div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small text-uppercase">Technical Features</label>
                                <input type="hidden" name="features" id="features_input" value="{{ old('features') }}">
                                <div id="quill_features_create" style="height: 200px; background: #fff;"></div>
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
                                <div class="attribute-row row g-2 mb-2 align-items-end">
                                    <div class="col-md-5">
                                        <label class="small text-muted mb-1">Specification Name</label>
                                        <select class="form-select attribute-select" name="attributes[0][attribute_id]">
                                            <option value="">Choose...</option>
                                            @foreach($attributes as $attribute)
                                                <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="small text-muted mb-1">Value</label>
                                        <input type="text" class="form-control" name="attributes[0][value]" placeholder="e.g. 100% Cotton">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-attribute" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Taxonomy, Pricing, Media -->
                <div class="col-lg-4">
                    <!-- Status & Visibility -->
                    <div class="premium-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label for="status" class="form-label fw-bold small text-uppercase mb-0">Publish Status</label>
                                <select class="form-select form-select-sm w-auto border-0 bg-light fw-bold text-primary" id="status" name="status">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>
                            <div class="form-check form-switch p-3 bg-light rounded d-flex align-items-center justify-content-between">
                                <label class="form-check-label fw-bold small text-uppercase cursor-pointer" for="show_in_ecommerce">
                                    Show Online
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="show_in_ecommerce" name="show_in_ecommerce" value="1" {{ old('show_in_ecommerce', 1) ? 'checked' : '' }} style="width: 2.5rem; height: 1.25rem; cursor: pointer;">
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="manage_stock" name="manage_stock" value="1" checked>
                                <label class="form-check-label small fw-bold text-muted cursor-pointer" for="manage_stock">Track Stock Level</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_variations" name="has_variations" value="1">
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
                                        <input type="number" step="0.01" class="form-control" id="price" name="price" required value="{{ old('price') }}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="wholesale_price" class="form-label fw-bold small text-uppercase">Wholesale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">৳</span>
                                        <input type="number" step="0.01" class="form-control" id="wholesale_price" name="wholesale_price" value="{{ old('wholesale_price') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="cost" class="form-label fw-bold small text-uppercase">Cost Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="cost" name="cost" required value="{{ old('cost') }}">
                                </div>
                                <div class="col-6">
                                    <label for="discount" class="form-label fw-bold small text-uppercase">Discount</label>
                                    <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="{{ old('discount') }}">
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
                                    <option value="">Select Category</option>
                                    <!-- Options loaded via AJAX -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Brand</label>
                                <select class="form-select select2-simple" id="brand_id" name="brand_id">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold small text-uppercase">Season</label>
                                    <select class="form-select form-select-sm" id="season_id" name="season_id">
                                        <option value="">All</option>
                                        @foreach($seasons as $season)
                                            <option value="{{ $season->id }}">{{ $season->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small text-uppercase">Gender</label>
                                    <select class="form-select form-select-sm" id="gender_id" name="gender_id">
                                        <option value="">All</option>
                                        @foreach($genders as $gender)
                                            <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media -->
                    <div class="premium-card mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-images me-2 text-primary"></i>Media Assets</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Main Cover Image</label>
                                <input class="form-control" type="file" id="image" name="image" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Gallery (Multiple)</label>
                                <input class="form-control" type="file" id="gallery" name="gallery[]" accept="image/*" multiple>
                            </div>
                             <div class="mb-0">
                                <label class="form-label fw-bold small text-uppercase">Size Chart</label>
                                <div id="size_chart_preview_container" class="mb-2" style="display: none;">
                                    <div class="position-relative d-inline-block">
                                        <img id="size_chart_preview" src="" style="max-width: 100px; border-radius: 6px; border: 1px solid #eee;">
                                        <button type="button" class="btn btn-sm btn-circle btn-danger position-absolute top-0 start-100 translate-middle" id="size_chart_delete_btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <input class="form-control" type="file" id="size_chart" name="size_chart" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Sticky Footer for Action -->
                <div class="col-12 mt-0">
                    <div class="premium-card">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('product.list') }}" class="btn btn-light fw-bold text-muted">Discard</a>
                            <button type="submit" class="btn btn-create-premium px-5 py-2">
                                <i class="fas fa-save me-2"></i>Save & Publish
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
    // Initialize Select2
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

    $('#name').on('input', function(){ $('#slug').val(slugify($(this).val())); });

    // CKEditor for Short Description
    ClassicEditor.create(document.querySelector('#short_desc'), {
        toolbar: {
            items: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            shouldNotGroupWhenFull: true
        }
    }).then(editor => { window.shortDescEditor = editor; }).catch(err => console.error(err));

    // Quill for Main Description
    var quill = new Quill('#quill_description_create', {
        theme: 'snow',
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic','underline'], [{ list:'ordered' }, { list:'bullet' }], ['clean']] }
    });
    var initial = document.getElementById('description_input').value || '';
    if (initial) { quill.root.innerHTML = initial; }
    
    // Quill for Features
    var quillFeatures = new Quill('#quill_features_create', {
        theme: 'snow',
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic'], [{ list:'ordered' }, { list:'bullet' }], ['clean']] }
    });
    var initialFeatures = document.getElementById('features_input').value || '';
    if (initialFeatures) { quillFeatures.root.innerHTML = initialFeatures; }

    // Sync Editors
    $('#createProductForm').on('submit', function() {
        if (window.shortDescEditor) { document.getElementById('short_desc').value = window.shortDescEditor.getData(); }
        document.getElementById('description_input').value = quill.root.innerHTML;
        document.getElementById('features_input').value = quillFeatures.root.innerHTML;
    });

    // Attribute Management logic
    let attributeCount = 1;
    $('#add-attribute').on('click', function(){
        const row = `
            <div class="attribute-row row g-2 mb-2 align-items-end">
                <div class="col-md-5">
                    <select class="form-select attribute-select" name="attributes[${attributeCount}][attribute_id]">
                        <option value="">Choose...</option>
                        @foreach($attributes as $attribute)
                            <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
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
        if ($('.attribute-row').length <= 1) $('.remove-attribute').hide();
    });

    // Image Previews
    $('#size_chart').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#size_chart_preview').attr('src', e.target.result);
                $('#size_chart_preview_container').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#size_chart_delete_btn').on('click', function() {
        $('#size_chart').val('');
        $('#size_chart_preview_container').hide();
    });
});
</script>
@endpush
@endsection