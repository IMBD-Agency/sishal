@extends('erp.master')

@section('title', 'Create Product')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create New Product</h5>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required value="{{ old('name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="slug" name="slug" required value="{{ old('slug') }}" placeholder="Auto-generated from name">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="sku" name="sku" required value="{{ old('sku') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required style="width: 100%">
                                            <option value="">Select Category</option>
                                            {{-- Options will be loaded via AJAX --}}
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="short_desc" class="form-label">Short Description</label>
                                        <div class="ckeditor-wrapper">
                                            <textarea name="short_desc" id="short_desc" class="form-control" rows="10">{{ old('short_desc') }}</textarea>
                                        </div>
                                        <small class="text-muted">You can paste tables from Google Docs or Word - they will be preserved automatically.</small>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="description" class="form-label">Description</label>
                                        <input type="hidden" name="description" id="description_input" value="{{ old('description') }}">
                                        <div id="quill_description_create" style="height: 220px; background: #fff;" class="border"></div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="features" class="form-label">Features</label>
                                        <input type="hidden" name="features" id="features_input" value="{{ old('features') }}">
                                        <div id="quill_features_create" style="height: 220px; background: #fff;" class="border"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="price" name="price" required value="{{ old('price') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="discount" class="form-label">Discount</label>
                                        <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="{{ old('discount') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="cost" name="cost" required value="{{ old('cost') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="image" class="form-label">Main Image</label>
                                        <input class="form-control" type="file" id="image" name="image" accept="image/*">
                                        <small class="form-text text-muted">Supported: jpeg, png, jpg, gif, svg. Max size: 2MB.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="size_chart" class="form-label">Size Chart Image</label>
                                        <div id="size_chart_preview_container" class="mb-2" style="display: none;">
                                            <div class="position-relative" style="display: inline-block;">
                                                <img id="size_chart_preview" src="" alt="Size Chart Preview" style="max-width: 120px; max-height: 120px; border: 1px solid #ddd; border-radius: 4px;">
                                                <button type="button" class="btn btn-sm btn-danger p-1" id="size_chart_delete_btn" style="position: absolute; top: 0; right: 0;" title="Remove image">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <input class="form-control" type="file" id="size_chart" name="size_chart" accept="image/*">
                                        <small class="form-text text-muted">Upload a size chart image that will be displayed on the product details page. Supported: jpeg, png, jpg, gif, svg. Max size: 2MB.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gallery" class="form-label">Gallery Images</label>
                                        <input class="form-control" type="file" id="gallery" name="gallery[]" accept="image/*" multiple>
                                        <small class="form-text text-muted">You can select multiple images.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm p-3 h-100">
                                            <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                                                <div>
                                                    <label class="form-check-label fw-bold mb-0" for="show_in_ecommerce">
                                                        <i class="fas fa-globe me-2 text-primary"></i>Show in Ecommerce
                                                    </label>
                                                    <div class="form-text mt-1 small">Visible on public website. Always visible in POS.</div>
                                                </div>
                                                <input class="form-check-input ms-0" type="checkbox" id="show_in_ecommerce" name="show_in_ecommerce" value="1" style="width: 3rem; height: 1.5rem; cursor: pointer;" {{ old('show_in_ecommerce', 1) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <h3>Meta Information</h3>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{{ old('meta_description') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <div id="keywords-container">
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control keyword-input" name="meta_keywords[]" placeholder="Enter keyword">
                                                <button type="button" class="btn btn-outline-danger remove-keyword" style="display: none;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-keyword">
                                            <i class="fas fa-plus me-1"></i>Add Keyword
                                        </button>
                                    </div>

                                    <!-- Product Attributes Section -->
                                    <div class="col-md-12">
                                        <h3>Product Specifications</h3>
                                        <p class="text-muted">Add technical specifications and product attributes</p>
                                        <div id="attributes-container">
                                            <div class="attribute-row row g-2 mb-2">
                                                <div class="col-md-5">
                                                    <select class="form-select attribute-select" name="attributes[0][attribute_id]">
                                                        <option value="">Select Attribute</option>
                                                        @foreach($attributes as $attribute)
                                                            <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="attributes[0][value]" placeholder="Enter value">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger remove-attribute" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-attribute">
                                            <i class="fas fa-plus me-1"></i>Add Specification
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-4 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>Save Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .select2-selection{
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }
        /* Ensure tables display properly in Quill editor */
        #quill_short_desc_create .ql-editor table,
        #quill_short_desc_edit .ql-editor table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }
        #quill_short_desc_create .ql-editor table td,
        #quill_short_desc_create .ql-editor table th,
        #quill_short_desc_edit .ql-editor table td,
        #quill_short_desc_edit .ql-editor table th {
            border: 1px solid #ddd;
            padding: 8px;
        }
        #quill_short_desc_create .ql-editor table th,
        #quill_short_desc_edit .ql-editor table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        /* CKEditor Wrapper */
        .ckeditor-wrapper {
            width: 100%;
            position: relative;
        }
        
        /* CKEditor Responsive Styles for Mobile */
        .ck-editor {
            max-width: 100%;
        }
        
        .ck-editor__editable {
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* CKEditor will replace the textarea automatically */
        
        /* Mobile Responsive - CKEditor */
        @media (max-width: 768px) {
            /* Wrapper adjustments */
            .ckeditor-wrapper {
                margin: 0 -15px;
                padding: 0 15px;
            }
            
            /* Make editor container responsive */
            .ck.ck-editor {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            
            /* Adjust toolbar for mobile */
            .ck.ck-toolbar {
                flex-wrap: wrap !important;
                padding: 8px 4px !important;
            }
            
            .ck.ck-toolbar .ck-toolbar__separator {
                margin: 4px 2px !important;
            }
            
            .ck.ck-button {
                min-width: 32px !important;
                padding: 4px 6px !important;
                font-size: 12px !important;
            }
            
            /* Make editor content area responsive */
            .ck.ck-editor__editable {
                min-height: 250px !important;
                max-height: 500px !important;
                padding: 12px !important;
                font-size: 16px !important; /* Prevents zoom on iOS */
            }
            
            /* Responsive tables in editor */
            .ck.ck-editor__editable table {
                width: 100% !important;
                display: block !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }
            
            .ck.ck-editor__editable table td,
            .ck.ck-editor__editable table th {
                padding: 6px 8px !important;
                font-size: 14px !important;
                white-space: nowrap !important;
                min-width: 80px !important;
            }
            
            /* Table wrapper for horizontal scroll */
            .ck.ck-editor__editable > table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Adjust form label and help text */
            .form-label {
                font-size: 14px !important;
            }
            
            .text-muted {
                font-size: 12px !important;
            }
            
            /* Table properties dropdown responsive */
            .ck.ck-dropdown__panel {
                max-width: 90vw !important;
                left: 5vw !important;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 576px) {
            .ck.ck-editor__editable {
                min-height: 200px !important;
                padding: 10px !important;
                font-size: 16px !important;
            }
            
            .ck.ck-toolbar {
                padding: 6px 2px !important;
            }
            
            .ck.ck-button {
                min-width: 28px !important;
                padding: 3px 4px !important;
            }
            
            .ck.ck-editor__editable table td,
            .ck.ck-editor__editable table th {
                padding: 4px 6px !important;
                font-size: 12px !important;
                min-width: 60px !important;
            }
        }
        
        /* Ensure tables are scrollable on mobile in the editor */
        .ck.ck-editor__editable {
            overflow-x: auto;
        }
    </style>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<!-- CKEditor 5 - Free, no API key needed, excellent table support -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[\s\W-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
$(document).ready(function() {
    $('#category_id').select2({
        placeholder: 'Search for a category',
        ajax: {
            url: '/erp/categories/search',
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term }; },
            processResults: function(data) { 
                return { 
                    results: data.map(function(cat){ 
                        return { 
                            id: cat.id, 
                            text: cat.display_name || cat.name 
                        }; 
                    }) 
                }; 
            },
            cache: true
        },
        minimumInputLength: 1
    });
    $('#name').on('input', function(){ $('#slug').val(slugify($(this).val())); });

    // CKEditor 5 - Free, no API key, excellent table support
    // Detect mobile device
    var isMobile = window.innerWidth <= 768;
    
    ClassicEditor
        .create(document.querySelector('#short_desc'), {
            toolbar: {
                items: isMobile ? [
                    // Compact toolbar for mobile
                    'heading', '|',
                    'bold', 'italic', '|',
                    'bulletedList', 'numberedList', '|',
                    'insertTable', '|',
                    'undo', 'redo'
                ] : [
                    // Full toolbar for desktop
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'alignment', '|',
                    'insertTable', '|',
                    'link', 'blockQuote', '|',
                    'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            },
            table: {
                contentToolbar: [
                    'tableColumn', 'tableRow', 'mergeTableCells',
                    'tableProperties', 'tableCellProperties'
                ]
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            },
            // CKEditor automatically handles pasting from Google Docs/Word
            // Remove excessive styles but keep structure
            removePlugins: ['Title'],
            // Custom styles for tables
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: true,
                        classes: true,
                        styles: true
                    }
                ]
            }
        })
        .then(editor => {
            // Store editor instance for form submission
            window.shortDescEditor = editor;
            
            // CKEditor 5 automatically handles Google Docs/Word pasting with pasteFromOffice plugin
            // No additional cleanup needed - it's built-in!
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
    
    // Sync editor content with textarea on form submit
    var form = document.querySelector('#mainContent form');
    if (form) {
        form.addEventListener('submit', function() {
            if (window.shortDescEditor) {
                document.getElementById('short_desc').value = window.shortDescEditor.getData();
            }
        });
    }

    // Quill init for Description only
    var quill = new Quill('#quill_description_create', {
        theme: 'snow',
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic','underline','strike'], [{ list:'ordered' }, { list:'bullet' }], [{ align: [] }], ['link','blockquote','code-block','image'], ['clean']] }
    });
    var initial = document.getElementById('description_input').value || '';
    if (initial) { document.querySelector('#quill_description_create .ql-editor').innerHTML = initial; }
    
    // Keep hidden input in sync on every change
    quill.on('text-change', function(){
        document.getElementById('description_input').value = quill.root.innerHTML;
    });
    // Also sync on submit as a final safety
    form.addEventListener('submit', function(){
        document.getElementById('description_input').value = quill.root.innerHTML;
    });

    // Quill init for Features
    var quillFeatures = new Quill('#quill_features_create', {
        theme: 'snow',
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic','underline','strike'], [{ list:'ordered' }, { list:'bullet' }], [{ align: [] }], ['link','blockquote','code-block','image'], ['clean']] }
    });
    var initialFeatures = document.getElementById('features_input').value || '';
    if (initialFeatures) { document.querySelector('#quill_features_create .ql-editor').innerHTML = initialFeatures; }
    
    // Keep hidden input in sync on every change
    quillFeatures.on('text-change', function(){
        document.getElementById('features_input').value = quillFeatures.root.innerHTML;
    });
    // Also sync on submit as a final safety
    form.addEventListener('submit', function(){
        document.getElementById('features_input').value = quillFeatures.root.innerHTML;
    });

    // Keywords add/remove
    let keywordCount = 1;
    $('#add-keyword').on('click', function(){
        $('#keywords-container').append(`
            <div class="input-group mb-2">
                <input type="text" class="form-control keyword-input" name="meta_keywords[]" placeholder="Enter keyword">
                <button type="button" class="btn btn-outline-danger remove-keyword">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`);
        keywordCount++; updateRemoveButtons();
    });
    $(document).on('click', '.remove-keyword', function(){ if (keywordCount>1) { $(this).closest('.input-group').remove(); keywordCount--; updateRemoveButtons(); } });
    function updateRemoveButtons(){ const btns=$('.remove-keyword'); if (keywordCount<=1) btns.hide(); else btns.show(); }
    updateRemoveButtons();

    // Attributes management
    let attributeCount = 1;
    $('#add-attribute').on('click', function(){
        const attributeRow = `
            <div class="attribute-row row g-2 mb-2">
                <div class="col-md-5">
                    <select class="form-select attribute-select" name="attributes[${attributeCount}][attribute_id]">
                        <option value="">Select Attribute</option>
                        @foreach($attributes as $attribute)
                            <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="attributes[${attributeCount}][value]" placeholder="Enter value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger remove-attribute">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#attributes-container').append(attributeRow);
        attributeCount++;
        updateAttributeRemoveButtons();
    });

    $(document).on('click', '.remove-attribute', function(){
        if (attributeCount > 1) {
            $(this).closest('.attribute-row').remove();
            attributeCount--;
            updateAttributeRemoveButtons();
        }
    });

    function updateAttributeRemoveButtons(){
        const btns = $('.remove-attribute');
        if (attributeCount <= 1) btns.hide();
        else btns.show();
    }
    updateAttributeRemoveButtons();

    // Size chart image preview and delete functionality
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
        $('#size_chart_preview').attr('src', '');
        $('#size_chart_preview_container').hide();
    });

    // Filter out empty attribute rows before form submission
    $('form').on('submit', function(e) {
        console.log('Form submitting...');
        
        // Remove only completely empty rows (both attribute_id and value are empty)
        $('.attribute-row').each(function() {
            const attributeId = $(this).find('select[name*="[attribute_id]"]').val();
            const value = $(this).find('input[name*="[value]"]').val();
            
            console.log('Checking row:', {attributeId, value});
            
            // Only remove if both are completely empty
            if ((!attributeId || attributeId === '') && (!value || value.trim() === '')) {
                console.log('Removing completely empty row');
                $(this).remove();
            }
        });
        
        // Log final form data
        const formData = new FormData(this);
        const attributes = [];
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('attributes[')) {
                attributes.push({key, value});
            }
        }
        console.log('Final attributes being submitted:', attributes);
    });
});
</script>
@endpush