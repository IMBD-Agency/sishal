@extends('erp.master')

@section('title', 'Create Warehouse')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid">
            <div class="row justify-content-center my-4">
                <div class="col-lg-10 col-xl-8">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-white shadow-sm btn-sm me-3">
                                <i class="fas fa-arrow-left text-primary"></i>
                            </a>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">Create New Warehouse</h4>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">Warehouses</a></li>
                                        <li class="breadcrumb-item active">Create</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="fas fa-warehouse text-primary"></i>
                                </div>
                                <h5 class="mb-0 fw-bold">General Information</h5>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('warehouses.store') }}" method="POST">
                                @csrf
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-semibold text-muted">Warehouse Name <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-modern border rounded-3 overflow-hidden shadow-sm-hover">
                                            <span class="input-group-text bg-white border-0"><i class="fas fa-building text-muted"></i></span>
                                            <input type="text" class="form-control border-0 @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" placeholder="Enter warehouse name" required>
                                        </div>
                                        @error('name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="location" class="form-label fw-semibold text-muted">Location <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-modern border rounded-3 overflow-hidden shadow-sm-hover">
                                            <span class="input-group-text bg-white border-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                            <input type="text" class="form-control border-0 @error('location') is-invalid @enderror" 
                                                   id="location" name="location" value="{{ old('location') }}" placeholder="Street, City, Country" required>
                                        </div>
                                        @error('location')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contact_phone" class="form-label fw-semibold text-muted">Contact Phone</label>
                                        <div class="input-group input-group-modern border rounded-3 overflow-hidden shadow-sm-hover">
                                            <span class="input-group-text bg-white border-0"><i class="fas fa-phone text-muted"></i></span>
                                            <input type="text" class="form-control border-0 @error('contact_phone') is-invalid @enderror" 
                                                   id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+880 1XXX-XXXXXX">
                                        </div>
                                        @error('contact_phone')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contact_email" class="form-label fw-semibold text-muted">Contact Email</label>
                                        <div class="input-group input-group-modern border rounded-3 overflow-hidden shadow-sm-hover">
                                            <span class="input-group-text bg-white border-0"><i class="fas fa-envelope text-muted"></i></span>
                                            <input type="email" class="form-control border-0 @error('contact_email') is-invalid @enderror" 
                                                   id="contact_email" name="contact_email" value="{{ old('contact_email') }}" placeholder="warehouse@example.com">
                                        </div>
                                        @error('contact_email')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="manager_id" class="form-label fw-semibold text-muted">Assign Manager</label>
                                        <div class="input-group input-group-modern border rounded-3 overflow-hidden shadow-sm-hover">
                                            <span class="input-group-text bg-white border-0"><i class="fas fa-user-tie text-muted"></i></span>
                                            <select class="form-select border-0 @error('manager_id') is-invalid @enderror" 
                                                    id="manager_id" name="manager_id">
                                                <option value="">-- No Manager --</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}" {{ old('manager_id') == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->user->first_name ?? 'N/A' }} {{ $employee->user->last_name ?? '' }} ({{ $employee->position ?? 'Employee' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('manager_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status" class="form-label fw-semibold text-muted">Initial Status</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check custom-check card-select border rounded-3 p-3 flex-fill text-center {{ old('status', 'active') == 'active' ? 'active' : '' }}">
                                                <input class="form-check-input d-none" type="radio" name="status" id="status_active" value="active" {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                                                <label class="form-check-label w-100 cursor-pointer" for="status_active">
                                                    <i class="fas fa-check-circle text-success fs-4 mb-2 d-block"></i>
                                                    <span class="fw-bold d-block">Active</span>
                                                    <small class="text-muted">Available for stock</small>
                                                </label>
                                            </div>
                                            <div class="form-check custom-check card-select border rounded-3 p-3 flex-fill text-center {{ old('status') == 'inactive' ? 'active' : '' }}">
                                                <input class="form-check-input d-none" type="radio" name="status" id="status_inactive" value="inactive" {{ old('status') == 'inactive' ? 'checked' : '' }}>
                                                <label class="form-check-label w-100 cursor-pointer" for="status_inactive">
                                                    <i class="fas fa-pause-circle text-secondary fs-4 mb-2 d-block"></i>
                                                    <span class="fw-bold d-block">Inactive</span>
                                                    <small class="text-muted">Currently disabled</small>
                                                </label>
                                            </div>
                                        </div>
                                        @error('status')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mt-5">
                                        <div class="d-flex gap-3">
                                            <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm rounded-3">
                                                <i class="fas fa-save me-2"></i>Save Warehouse
                                            </button>
                                            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary px-5 py-2 rounded-3">
                                                Cancel
                                            </a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="status"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.custom-check').forEach(el => el.classList.remove('active'));
                    this.closest('.custom-check').classList.add('active');
                });
            });
        });
    </script>
@endsection
