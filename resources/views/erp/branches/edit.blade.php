@extends('erp.master')

@section('title', 'Modify Branch Settings')

@section('body')
<style>
    :root {
        --primary-color: #2d5a4c;
        --border-radius: 16px;
    }

    .main-content {
        background: #f4f7f6;
        min-height: 100vh;
    }

    .form-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: white;
    }

    .card-title-premium {
        color: var(--primary-color);
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .form-label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.85rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(45, 90, 76, 0.1);
    }

    .btn-submit {
        background: var(--primary-color);
        color: white;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #23473b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(45, 90, 76, 0.2);
        color: white;
    }

    .btn-cancel {
        background: #edf2f7;
        color: #4a5568;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #2d3748;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
    }

    .switch-premium {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        border-radius: 12px;
    }
</style>

@include('erp.components.sidebar')
<div class="main-content" id="mainContent">
    @include('erp.components.header')
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="form-card p-4 p-md-5">
                    <div class="text-center mb-5">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-edit fs-2 text-warning"></i>
                        </div>
                        <h2 class="card-title-premium">Modify Branch Settings</h2>
                        <p class="text-muted">Update configuration for <strong>{{ $branch->name }}</strong>.</p>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                        <ul class="mb-0 small fw-bold">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('branches.update', $branch->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-12">
                                <label for="name" class="form-label">Branch Name</label>
                                <div class="input-icon">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $branch->name) }}" required>
                                    <i class="fas fa-heading"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location Address</label>
                                <div class="input-icon">
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $branch->location) }}" required>
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="contact_info" class="form-label">Contact Details</label>
                                <div class="input-icon">
                                    <input type="text" class="form-control @error('contact_info') is-invalid @enderror" id="contact_info" name="contact_info" value="{{ old('contact_info', $branch->contact_info) }}" required>
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="warehouse_id" class="form-label">Parent Warehouse</label>
                                <div class="input-icon">
                                    <select class="form-select @error('warehouse_id') is-invalid @enderror" id="warehouse_id" name="warehouse_id">
                                        <option value="">-- No Parent Warehouse --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $branch->warehouse_id) == $wh->id ? 'selected' : '' }}>🏭 {{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="manager_id" class="form-label">Branch Manager</label>
                                <div class="input-icon">
                                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                                        <option value="">-- No Manager Assigned --</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->user_id }}" {{ old('manager_id', $branch->manager_id) == $employee->user_id ? 'selected' : '' }}>
                                                👤 {{ $employee->user->first_name ?? '' }} {{ $employee->user->last_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Operations -->
                            <div class="col-12">
                                <div class="switch-premium shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-globe me-2 text-info"></i>Ecommerce Active</h6>
                                        <div class="form-check form-switch p-0">
                                            <input class="form-check-input ms-0" type="checkbox" id="show_online" name="show_online" value="1" {{ old('show_online', $branch->show_online) ? 'checked' : '' }} style="width: 3rem; height: 1.5rem;">
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">Allow products at this location to be synchronized with your online store inventory.</p>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="status" class="form-label">Operational Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="active" {{ old('status', $branch->status) == 'active' ? 'selected' : '' }}>🟢 Currently Active</option>
                                    <option value="inactive" {{ old('status', $branch->status) == 'inactive' ? 'selected' : '' }}>🔴 Suspended/Inactive</option>
                                </select>
                            </div>

                            <div class="col-12 pt-4 d-flex flex-column flex-md-row gap-3">
                                <button type="submit" class="btn-submit flex-grow-1 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>Save Final Changes
                                </button>
                                <a href="{{ route('branches.index') }}" class="btn-cancel flex-grow-1 text-center shadow-none">
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
 