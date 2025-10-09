@extends('erp.master')

@section('title', 'Edit Product Variation')

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
                        <i class="fas fa-edit me-2"></i>
                        Edit Product Variation - {{ $product->name }}
                    </h4>
                    <div>
                        <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> Back to Variations
                        </a>
                        <a href="{{ route('erp.products.variations.show', [$product->id, $variation->id]) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye me-1"></i> View
                        </a>
                    </div>
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
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('erp.products.variations.update', [$product->id, $variation->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $variation->sku) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Variation Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $variation->name) }}" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="price" class="form-label">Price Override</label>
                                                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $variation->price) }}" step="0.01" min="0">
                                                    <small class="form-text text-muted">Leave empty to use product price</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="cost" class="form-label">Cost Override</label>
                                                    <input type="number" class="form-control" id="cost" name="cost" value="{{ old('cost', $variation->cost) }}" step="0.01" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="discount" class="form-label">Discount Override</label>
                                                    <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', $variation->discount) }}" step="0.01" min="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="image" class="form-label">Variation Image</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            @if($variation->image)
                                                <div class="mt-2">
                                                    <img src="/{{ ltrim($variation->image,'/') }}" alt="Image" style="height:80px;width:auto;object-fit:cover">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $variation->is_default) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_default">Set as default variation</label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="active" {{ old('status', $variation->status) === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $variation->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header"><h5 class="mb-0">Attribute Combinations</h5></div>
                                    <div class="card-body">
                                        @if($attributes->count() > 0)
                                            <div id="attribute-combinations">
                                                @foreach($attributes as $index => $attribute)
                                                    @php($selectedCombo = $variation->combinations->firstWhere('attribute_id', $attribute->id))
                                                    <div class="mb-3 attribute-row" data-attribute-id="{{ $attribute->id }}">
                                                        <label class="form-label">{{ $attribute->name }} <span class="text-danger">*</span></label>
                                                        <select class="form-select attribute-select" name="attribute_values[]" required>
                                                            <option value="">Select {{ $attribute->name }}</option>
                                                @foreach($attribute->values as $value)
                                                                <option value="{{ $value->id }}"
                                                                        data-attribute-id="{{ $attribute->id }}"
                                                                        data-value-id="{{ $value->id }}"
                                                                        data-color-code="{{ $value->color_code }}"
                                                                        {{ (string) old('attribute_values.' . $index, optional($selectedCombo)->attribute_value_id) === (string) $value->id ? 'selected' : '' }}>
                                                                    {{ $value->value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="attributes[]" value="{{ $attribute->id }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-0">No attributes found. Please create attributes first.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header"><h5 class="mb-0">Gallery Images</h5></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="gallery" class="form-label">Additional Images</label>
                                            <input type="file" class="form-control" id="gallery" name="gallery[]" accept="image/*" multiple>
                                            <small class="form-text text-muted">Uploading new images will append to existing gallery.</small>
                                        </div>
                                        @if($variation->galleries && $variation->galleries->count())
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($variation->galleries as $img)
                                                    <img src="/{{ ltrim($img->image,'/') }}" alt="Gallery" style="height:60px;width:auto;object-fit:cover"/>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Variation
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
@endsection


