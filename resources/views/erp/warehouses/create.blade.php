@extends('erp.master')

@section('title', 'Create Warehouse')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-12">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                        <h2 class="mb-0">Create New Warehouse</h2>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Warehouse Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('warehouses.store') }}" method="POST">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                               id="location" name="location" value="{{ old('location') }}" required>
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">Branch (Optional)</label>
                                        <select class="form-control @error('branch_id') is-invalid @enderror" 
                                                id="branch_id" name="branch_id">
                                            <option value="">-- No Branch (Ecommerce Only) --</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Leave empty for ecommerce warehouses. Select a branch only if this warehouse is for POS operations.
                                        </small>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="manager_id" class="form-label">Manager (Optional)</label>
                                        <select class="form-control @error('manager_id') is-invalid @enderror" 
                                                id="manager_id" name="manager_id">
                                            <option value="">-- Select Manager --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('manager_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Create Warehouse
                                    </button>
                                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

