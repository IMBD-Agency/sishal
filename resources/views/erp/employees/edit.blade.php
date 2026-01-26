@extends('erp.master')

@section('title', 'Update Personnel Record')

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
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none text-muted">Employees</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Update Profile</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <h4 class="fw-bold mb-0 text-dark">{{ $employee->user->first_name ?? 'Employee' }} {{ $employee->user->last_name ?? '' }}</h4>
                    <span class="status-pill {{ $employee->status == 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('employees.index') }}" class="btn btn-light border px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="premium-card">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Modify Personnel Data</h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4">
                                <ul class="mb-0 small fw-bold">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('employees.update', $employee->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-4">
                                <!-- Personal Account Details -->
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3">Identity Information</h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $employee->user->first_name ?? '') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $employee->user->last_name ?? '') }}" required>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label for="phone" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="fas fa-phone"></i></span>
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" required>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label">Email Address (Read Only)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control bg-light" value="{{ $employee->user->email ?? '' }}" readonly disabled>
                                        </div>
                                        <small class="text-muted">Email cannot be changed for security reasons.</small>
                                    </div>
                                </div>

                                <!-- Security & Access -->
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3">Role & Access Control</h6>

                                    <div class="mb-3">
                                        <label for="role" class="form-label">System Role <span class="text-danger">*</span></label>
                                        <select class="form-select" id="role" name="role">
                                            <option value="">Select Role...</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('role', $employee->role) == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Account Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active & Enabled</option>
                                            <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Suspended / Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                        <a href="{{ route('employees.index') }}" class="btn btn-light px-4 fw-bold">Cancel</a>
                                        <button type="submit" class="btn btn-create-premium px-5">
                                            <i class="fas fa-save me-2"></i>Update Entry
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

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Select2 logic if needed in future
});
</script>
@endpush
@endsection
