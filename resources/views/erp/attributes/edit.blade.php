@extends('erp.master')

@section('title', 'Edit Attribute')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <!-- Header Section -->
        <div class="container-fluid px-4 py-4 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('product.list') }}" class="text-decoration-none">Products</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('attribute.list') }}" class="text-decoration-none">Attributes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Attribute</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-1">
                        <i class="fas fa-edit text-primary me-2"></i>Edit Attribute
                    </h2>
                    <p class="text-muted mb-0">Update the attribute information for better product organization.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('attribute.list') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Attributes
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-tag text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Edit Attribute: {{ $attribute->name }}</h5>
                                    <p class="text-muted mb-0">Update the details for this product attribute</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Please fix the following errors:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('attribute.update', $attribute->id) }}" method="POST" id="attributeForm">
                                @csrf
                                @method('PUT')
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-semibold">
                                            <i class="fas fa-tag me-1 text-primary"></i>Attribute Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                               value="{{ old('name', $attribute->name) }}" required placeholder="Enter attribute name">
                                        <div class="form-text">Enter a descriptive name for this attribute</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="slug" class="form-label fw-semibold">
                                            <i class="fas fa-link me-1 text-primary"></i>Slug
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="slug" name="slug" 
                                               value="{{ old('slug', $attribute->slug) }}" placeholder="Auto-generated from name">
                                        <div class="form-text">URL-friendly version of the name (auto-generated if empty)</div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label for="description" class="form-label fw-semibold">
                                            <i class="fas fa-info-circle me-1 text-primary"></i>Description
                                        </label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Describe what this attribute is used for...">{{ old('description', $attribute->description) }}</textarea>
                                        <div class="form-text">Optional description to help understand the attribute's purpose</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="status" class="form-label fw-semibold">
                                            <i class="fas fa-toggle-on me-1 text-primary"></i>Status
                                        </label>
                                        <select class="form-select form-select-lg" id="status" name="status">
                                            <option value="active" {{ old('status', $attribute->status)=='active'?'selected':'' }}>Active</option>
                                            <option value="inactive" {{ old('status', $attribute->status)=='inactive'?'selected':'' }}>Inactive</option>
                                        </select>
                                        <div class="form-text">Active attributes can be used in products</div>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('attribute.list') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-arrow-left me-2"></i>Cancel
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-outline-warning btn-lg me-3" onclick="resetToOriginal()">
                                                <i class="fas fa-undo me-2"></i>Reset to Original
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-save me-2"></i>Update Attribute
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attribute Info Section -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle text-info me-2"></i>Attribute Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Created:</strong> {{ $attribute->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="mb-2"><strong>Last Updated:</strong> {{ $attribute->updated_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Attribute ID:</strong> #{{ $attribute->id }}</p>
                                    <p class="mb-0"><strong>Current Status:</strong> 
                                        <span class="badge bg-{{ $attribute->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($attribute->status) }}
                                        </span>
                                    </p>
                                </div>
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
    function slugify(text) {
        return text.toString().toLowerCase().trim().replace(/[\s\W-]+/g,'-').replace(/^-+|-+$/g,'');
    }
    
    function resetToOriginal() {
        if (confirm('Are you sure you want to reset to the original values? All changes will be lost.')) {
            location.reload();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const name = document.getElementById('name');
        const slug = document.getElementById('slug');
        
        if (name && slug) {
            name.addEventListener('input', function() {
                if (!slug.value) {
                    slug.value = slugify(name.value);
                }
            });
        }
        
        // Auto-focus on name field
        name.focus();
    });
</script>
@endpush


