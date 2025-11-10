@extends('erp.master')

@section('title', 'Create Product Variation')

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
                        <i class="fas fa-plus me-2"></i>
                        Create Product Variation - {{ $product->name }}
                    </h4>
                    <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Variations
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h6>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('erp.products.variations.store', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="name" class="form-label">Variation Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="price" class="form-label">Price Override</label>
                                                    <input type="number" class="form-control" id="price" name="price" 
                                                           value="{{ old('price') }}" step="0.01" min="0">
                                                    <small class="form-text text-muted">Leave empty to use product price</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="cost" class="form-label">Cost Override</label>
                                                    <input type="number" class="form-control" id="cost" name="cost" 
                                                           value="{{ old('cost') }}" step="0.01" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="discount" class="form-label">Discount Override</label>
                                                    <input type="number" class="form-control" id="discount" name="discount" 
                                                           value="{{ old('discount') }}" step="0.01" min="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="image" class="form-label">Variation Image</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_default">
                                                    Set as default variation
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Attribute Combinations</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($attributes->count() > 0)
                                            <div id="attribute-combinations">
                                                @foreach($attributes as $index => $attribute)
                                                    <div class="mb-3 attribute-row" data-attribute-id="{{ $attribute->id }}" data-is-required="{{ $attribute->is_required ? '1' : '0' }}">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label mb-0">{{ $attribute->name }} @if($attribute->is_required)<span class="text-danger">*</span>@endif</label>
                                                            <button type="button" class="btn btn-sm btn-outline-primary select-all-btn" data-attribute-id="{{ $attribute->id }}">
                                                                <i class="fas fa-check-square me-1"></i> Select All
                                                            </button>
                                                        </div>
                                                        <select class="form-select attribute-select" name="attribute_values[{{ $attribute->id }}][]" multiple{{ $attribute->is_required ? ' required' : '' }}>
                                                            @foreach($attribute->activeValues as $value)
                                                                <option value="{{ $value->id }}" 
                                                                        data-attribute-id="{{ $attribute->id }}"
                                                                        data-value-id="{{ $value->id }}"
                                                                        data-color-code="{{ $value->color_code }}"
                                                                        {{ collect(old('attribute_values.' . $attribute->id, []))->contains($value->id) ? 'selected' : '' }}>
                                                                    {{ $value->value }}
                                                                    @if($attribute->is_color && $value->color_code)
                                                                        <span class="color-indicator" 
                                                                              style="background-color: {{ $value->color_code }}; 
                                                                                     width: 12px; height: 12px; 
                                                                                     display: inline-block; 
                                                                                     border-radius: 50%; 
                                                                                     margin-left: 5px;"></span>
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted">Tip: Hold Ctrl/Cmd to select multiple {{ strtolower($attribute->name) }} values to auto-generate combinations.</small>
                                                        <input type="hidden" name="attributes[]" value="{{ $attribute->id }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <!-- Preview Section -->
                                            <div id="combinations-preview" class="mt-3" style="display: none;">
                                                <h6>Preview of combinations to be created:</h6>
                                                <div id="preview-list" class="alert alert-info">
                                                    <!-- Preview will be populated by JavaScript -->
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                No attributes found. Please create attributes first.
                                                <a href="{{ route('erp.variation-attributes.create') }}" class="btn btn-sm btn-primary ms-2">
                                                    Create Attributes
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5>Gallery Images</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="gallery" class="form-label">Additional Images</label>
                                            <input type="file" class="form-control" id="gallery" name="gallery[]" accept="image/*" multiple>
                                            <small class="form-text text-muted">You can select multiple images</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary me-2">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Create Variation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
$(document).ready(function() {
    // Handle "Select All" button click
    $('.select-all-btn').on('click', function() {
        const attributeId = $(this).data('attribute-id');
        const $select = $(`.attribute-select[name="attribute_values[${attributeId}][]"]`);
        const $btn = $(this);
        
        // Check if all options are already selected
        const allOptions = $select.find('option');
        const selectedOptions = $select.find('option:selected');
        
        if (allOptions.length === selectedOptions.length) {
            // Deselect all
            $select.find('option').prop('selected', false);
            $btn.html('<i class="fas fa-check-square me-1"></i> Select All');
            $btn.removeClass('btn-primary').addClass('btn-outline-primary');
        } else {
            // Select all
            $select.find('option').prop('selected', true);
            $btn.html('<i class="fas fa-square me-1"></i> Deselect All');
            $btn.removeClass('btn-outline-primary').addClass('btn-primary');
        }
        
        // Trigger change event to update previews
        $select.trigger('change');
    });
    
    // Handle attribute selection and update button state
    $('.attribute-select').on('change', function() {
        const $select = $(this);
        const attributeId = $select.closest('.attribute-row').data('attribute-id');
        const $btn = $(`.select-all-btn[data-attribute-id="${attributeId}"]`);
        
        // Update button state
        const allOptions = $select.find('option');
        const selectedOptions = $select.find('option:selected');
        
        if (allOptions.length === selectedOptions.length && allOptions.length > 0) {
            $btn.html('<i class="fas fa-square me-1"></i> Deselect All');
            $btn.removeClass('btn-outline-primary').addClass('btn-primary');
        } else {
            $btn.html('<i class="fas fa-check-square me-1"></i> Select All');
            $btn.removeClass('btn-primary').addClass('btn-outline-primary');
        }
        
        // Update variation name preview
        updateVariationNamePreview();
        
        // Update combinations preview
        updateCombinationsPreview();
    });
    
    // Function to update combinations preview
    function updateCombinationsPreview() {
        const attributeData = {};
        let hasMultipleSelections = false;
        
        $('.attribute-select').each(function() {
            const attributeId = $(this).data('attribute-id') || $(this).closest('.attribute-row').data('attribute-id');
            const selectedValues = $(this).val() || [];
            
            if (Array.isArray(selectedValues) && selectedValues.length > 0) {
                attributeData[attributeId] = selectedValues;
                if (selectedValues.length > 1) {
                    hasMultipleSelections = true;
                }
            }
        });
        
        if (hasMultipleSelections && Object.keys(attributeData).length > 0) {
            // Generate combinations preview
            const combinations = generateCombinationsPreview(attributeData);
            showCombinationsPreview(combinations);
        } else {
            hideCombinationsPreview();
        }
    }
    
    // Generate combinations preview (simplified version)
    function generateCombinationsPreview(attributeData) {
        const attributes = Object.keys(attributeData);
        if (attributes.length === 0) return [];
        
        let result = [[]];
        for (const attrId of attributes) {
            const values = attributeData[attrId];
            const newResult = [];
            for (const combination of result) {
                for (const value of values) {
                    newResult.push([...combination, { attributeId: attrId, valueId: value }]);
                }
            }
            result = newResult;
        }
        
        return result;
    }
    
    // Show combinations preview
    function showCombinationsPreview(combinations) {
        const previewDiv = $('#combinations-preview');
        const previewList = $('#preview-list');
        
        if (combinations.length === 0) {
            hideCombinationsPreview();
            return;
        }
        
        let html = `<strong>${combinations.length} combinations will be created:</strong><br>`;
        combinations.forEach((combo, index) => {
            const comboText = combo.map(item => {
                const option = $(`.attribute-select option[value="${item.valueId}"]`);
                return option.text().trim();
            }).join(' - ');
            html += `${index + 1}. ${comboText}<br>`;
        });
        
        previewList.html(html);
        previewDiv.show();
    }
    
    // Hide combinations preview
    function hideCombinationsPreview() {
        $('#combinations-preview').hide();
    }
    
    // Handle form submission
    $('form').on('submit', function(e) {
        // Check if all required fields are filled
        let isValid = true;
        $('.attribute-select').each(function() {
            const $row = $(this).closest('.attribute-row');
            const isRequired = $row.data('is-required') === 1 || $row.data('is-required') === '1';
            
            if (isRequired && !$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please select all required attributes before submitting.');
            return false;
        }
        
        // Debug: log form data before submission
        console.log('Form data being submitted:', $(this).serialize());
        console.log('Attribute values:', $('.attribute-select').map(function() { return $(this).val(); }).get());
        console.log('Attributes:', $('input[name="attributes[]"]').map(function() { return $(this).val(); }).get());
        
        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Creating...');
    });
    
    // Auto-generate SKU and name based on selections
    function updateVariationNamePreview() {
        const selectedValues = [];
        $('.attribute-select').each(function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                selectedValues.push(selectedOption.text().trim());
            }
        });
        
        if (selectedValues.length > 0) {
            const variationName = selectedValues.join(' - ');
            $('#name').val(variationName);
            
            // Generate SKU
            const productSku = '{{ $product->sku }}';
            const skuSuffix = selectedValues.map(v => v.replace(/\s+/g, '').substring(0, 3).toUpperCase()).join('');
            $('#sku').val(productSku + '-' + skuSuffix);
        }
    }
    
    // Initialize button states on page load
    $('.attribute-select').each(function() {
        const $select = $(this);
        const attributeId = $select.closest('.attribute-row').data('attribute-id');
        const $btn = $(`.select-all-btn[data-attribute-id="${attributeId}"]`);
        
        const allOptions = $select.find('option');
        const selectedOptions = $select.find('option:selected');
        
        if (allOptions.length === selectedOptions.length && allOptions.length > 0) {
            $btn.html('<i class="fas fa-square me-1"></i> Deselect All');
            $btn.removeClass('btn-outline-primary').addClass('btn-primary');
        }
    });
    
    // Initialize on page load
    updateVariationNamePreview();
});
</script>
@endpush
