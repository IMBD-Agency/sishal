@extends('erp.master')

@section('title', 'Create Warehouse')

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
        text-align: center;
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

    .custom-check.active {
        border-color: var(--primary-color) !important;
        background-color: rgba(45, 90, 76, 0.05);
    }
</style>

@include('erp.components.sidebar')
<div class="main-content" id="mainContent">
    @include('erp.components.header')
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="form-card p-4 p-md-5">
                    <div class="text-center mb-5">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-warehouse fs-2" style="color: var(--primary-color);"></i>
                        </div>
                        <h2 class="card-title-premium">Create New Warehouse</h2>
                        <p class="text-muted">Register a central storage facility to manage your inventory levels across branches.</p>
                        
                        <nav aria-label="breadcrumb" class="d-flex justify-content-center mt-2">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}" class="text-decoration-none">Warehouses</a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </nav>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <ul class="mb-0 small fw-bold">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('warehouses.store') }}">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <label for="name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Central Warehouse Dhaka" required>
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location Address <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location') }}" placeholder="Street, City, Country" required>
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <div class="input-icon">
                                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+880 1XXX-XXXXXX">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <div class="input-icon">
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" placeholder="warehouse@example.com">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="manager_id" class="form-label">Assign Manager</label>
                                <div class="input-icon">
                                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                                        <option value="">-- No Manager --</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ old('manager_id') == $employee->id ? 'selected' : '' }}>
                                                👤 {{ $employee->user->first_name ?? 'N/A' }} {{ $employee->user->last_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Initial Status</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check custom-check border rounded-3 p-2 flex-fill text-center {{ old('status', 'active') == 'active' ? 'active' : '' }}" style="cursor: pointer;">
                                        <input class="form-check-input d-none" type="radio" name="status" id="status_active" value="active" {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 mb-0" for="status_active" style="cursor: pointer;">
                                            <i class="fas fa-check-circle text-success mb-1 d-block"></i>
                                            <span class="fw-bold d-block small">Active</span>
                                        </label>
                                    </div>
                                    <div class="form-check custom-check border rounded-3 p-2 flex-fill text-center {{ old('status') == 'inactive' ? 'active' : '' }}" style="cursor: pointer;">
                                        <input class="form-check-input d-none" type="radio" name="status" id="status_inactive" value="inactive" {{ old('status') == 'inactive' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 mb-0" for="status_inactive" style="cursor: pointer;">
                                            <i class="fas fa-pause-circle text-secondary mb-1 d-block"></i>
                                            <span class="fw-bold d-block small">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 pt-4 d-flex flex-column flex-md-row gap-3">
                                <button type="submit" class="btn-submit flex-grow-1 shadow-sm">
                                    <i class="fas fa-save me-2"></i>Save Warehouse
                                </button>
                                <a href="{{ route('warehouses.index') }}" class="btn-cancel flex-grow-1">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
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

        // Make the card clickable for the status radio
        document.querySelectorAll('.custom-check').forEach(card => {
            card.addEventListener('click', function() {
                this.querySelector('input').click();
            });
        });
    });
</script>
@endsection
