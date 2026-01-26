@extends('erp.master')

@section('title', 'Employee Profile | ' . ($fullName ?: 'Personnel'))

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
                        <li class="breadcrumb-item active text-primary fw-600">Personnel Profile</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <h4 class="fw-bold mb-0 text-dark">{{ $fullName }}</h4>
                    <span class="status-pill {{ $employee->status === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-light border px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-create-premium">
                    <i class="fas fa-pen-nib me-2"></i>Modify Access
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <div class="row g-4">
            <!-- Left: Identity Card -->
            <div class="col-lg-8">
                <div class="premium-card h-100">
                    <div class="card-header bg-white border-bottom p-4">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small">Identity Verification</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-5">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary me-4" style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ strtoupper(substr($employee->user->first_name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="fw-bold text-dark mb-1">{{ $fullName }}</h3>
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="fas fa-briefcase me-2 text-muted"></i>{{ $employee->designation ?? 'No Designation' }}
                                    </span>
                                    <span class="badge bg-light text-primary border border-primary px-3 py-2 rounded-pill">
                                        <i class="fas fa-shield-alt me-2"></i>{{ $primaryRole ?? 'Standard Access' }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-clock me-1"></i> Account created on {{ $employee->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <label class="small text-muted fw-bold text-uppercase mb-1">Official Email</label>
                                    <div class="d-flex align-items-center text-dark fw-bold">
                                        <i class="fas fa-envelope text-muted me-2 opacity-50"></i>
                                        {{ $employee->user->email ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <label class="small text-muted fw-bold text-uppercase mb-1">Contact Phone</label>
                                    <div class="d-flex align-items-center text-dark fw-bold">
                                        <i class="fas fa-phone text-muted me-2 opacity-50"></i>
                                        {{ $employee->phone ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Operational Stats -->
            <div class="col-lg-4">
                <div class="premium-card mb-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small">Operational Activity</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light">
                                    <div>
                                        <div class="small text-muted fw-bold">Total Sales Processed</div>
                                        <h4 class="fw-bold text-dark mb-0 mt-1">{{ number_format($salesCount) }}</h4>
                                    </div>
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-shopping-cart fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- Balance and Branch sections hidden for e-commerce only business -->
                        </div>
                    </div>
                </div>

                <div class="premium-card bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-green) 0%, #155d47 100%);">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-shield-check fa-3x mb-3 text-white opacity-50"></i>
                        <h5 class="fw-bold text-white mb-2">Secure Account</h5>
                        <p class="text-white text-opacity-75 small mb-3">This account is fully active and secured with role-based access control.</p>
                        @can('employee edit')
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-light w-100 fw-bold text-primary">
                            Manage Permissions
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
