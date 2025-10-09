@extends('erp.master')

@section('title', 'Create Variation Attribute')

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
                        Create Variation Attribute
                    </h4>
                    <a href="{{ route('erp.variation-attributes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Attributes
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('erp.variation-attributes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Attribute Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Attribute Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" required>
                                            <small class="form-text text-muted">URL-friendly version of the name (e.g., "color", "size")</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sort_order" class="form-label">Sort Order</label>
                                                    <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                                           value="{{ old('sort_order', 0) }}" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="status" name="status" required>
                                                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required') ? 'checked' : 'checked' }}>
                                                <label class="form-check-label" for="is_required">
                                                    Required attribute
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_color" name="is_color" value="1" {{ old('is_color') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_color">
                                                    Color attribute (enables color picker)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Attribute Values</h5>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addValueRow()">
                                            <i class="fas fa-plus me-1"></i> Add Value
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="attribute-values">
                                            <div class="value-row mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Value <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="values[0][value]" required>
                                                    </div>
                                                    <div class="col-md-4 color-field" style="display: none;">
                                                        <label class="form-label">Color Code</label>
                                                        <input type="color" class="form-control form-control-color" name="values[0][color_code]">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Image</label>
                                                        <input type="file" class="form-control" name="values[0][image]" accept="image/*">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('erp.variation-attributes.index') }}" class="btn btn-secondary me-2">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Create Attribute
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
let valueIndex = 1;

$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        $('#slug').val(slug);
    });

    // Toggle color fields based on is_color checkbox
    $('#is_color').on('change', function() {
        if ($(this).is(':checked')) {
            $('.color-field').show();
        } else {
            $('.color-field').hide();
        }
    });

    // Initialize color fields visibility
    if ($('#is_color').is(':checked')) {
        $('.color-field').show();
    }
});

function addValueRow() {
    const valueRow = `
        <div class="value-row mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Value <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="values[${valueIndex}][value]" required>
                </div>
                <div class="col-md-4 color-field" style="display: ${$('#is_color').is(':checked') ? 'block' : 'none'};">
                    <label class="form-label">Color Code</label>
                    <input type="color" class="form-control form-control-color" name="values[${valueIndex}][color_code]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Image</label>
                    <input type="file" class="form-control" name="values[${valueIndex}][image]" accept="image/*">
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeValueRow(this)">
                <i class="fas fa-trash me-1"></i> Remove
            </button>
        </div>
    `;
    
    $('#attribute-values').append(valueRow);
    valueIndex++;
}

function removeValueRow(button) {
    $(button).closest('.value-row').remove();
}
</script>
@endpush
