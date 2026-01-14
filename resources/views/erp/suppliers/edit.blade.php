@extends('erp.master')

@section('title', 'Edit Supplier')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="mb-4">
                <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm rounded-3 border-0 shadow-sm px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Suppliers
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3">
                            <h4 class="fw-bold mb-0 text-dark">Edit Supplier: {{ $supplier->name }}</h4>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control border-2 rounded-3 @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name) }}" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Company Name</label>
                                        <input type="text" name="company_name" class="form-control border-2 rounded-3 @error('company_name') is-invalid @enderror" value="{{ old('company_name', $supplier->company_name) }}">
                                        @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control border-2 rounded-3 @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier->phone) }}" required>
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Email Address</label>
                                        <input type="email" name="email" class="form-control border-2 rounded-3 @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Full Address</label>
                                        <textarea name="address" rows="3" class="form-control border-2 rounded-3 @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
                                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">City</label>
                                        <input type="text" name="city" class="form-control border-2 rounded-3 @error('city') is-invalid @enderror" value="{{ old('city', $supplier->city) }}">
                                        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Country</label>
                                        <input type="text" name="country" class="form-control border-2 rounded-3 @error('country') is-invalid @enderror" value="{{ old('country', $supplier->country) }}">
                                        @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Zip Code</label>
                                        <input type="text" name="zip_code" class="form-control border-2 rounded-3 @error('zip_code') is-invalid @enderror" value="{{ old('zip_code', $supplier->zip_code) }}">
                                        @error('zip_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold small text-muted text-uppercase">Tax Number / VAT</label>
                                        <input type="text" name="tax_number" class="form-control border-2 rounded-3 @error('tax_number') is-invalid @enderror" value="{{ old('tax_number', $supplier->tax_number) }}">
                                        @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 text-end">
                                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                                        Update Supplier
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
