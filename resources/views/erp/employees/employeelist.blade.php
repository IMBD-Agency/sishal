@extends('erp.master')

@section('title', 'Workforce Directory')

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
                            <li class="breadcrumb-item active text-primary fw-600">Employee List</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Workforce Directory</h4>
                    <p class="text-muted small mb-0">Manage personnel credentials, roles, and status</p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-3 align-items-md-center">
                    <form action="" method="GET" class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="search" name="name" class="form-control" placeholder="Quick find employee..." value="{{ request('name') }}">
                    </form>
                    @can('employee create')
                    <a href="{{ route('employees.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus-circle me-2"></i>Add Employee
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="container-fluid px-4">
            <!-- Advanced Filters -->
            <div class="premium-card mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Mobile Number</label>
                                <input type="text" class="form-control" name="phone" placeholder="e.g. 017..." value="{{ request('phone') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Department/Designation</label>
                                <input type="text" class="form-control" name="designation" placeholder="e.g. Manager" value="{{ request('designation') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Access Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Personnel</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Members</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Members</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button class="btn btn-create-premium flex-grow-1" type="submit">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                                <a href="{{ route('employees.index') }}" class="btn btn-action" style="width: 45px; height: 42px;" title="Reset Filters">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Employee Ledger -->
            <div class="premium-card">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">Active Personnel</h5>
                        <div class="text-muted small">
                            Total Records: <span class="fw-bold text-primary">{{ $employees->total() }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table mb-0" id="employeesTable">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">SL</th>
                                    <th>Personnel Identity</th>
                                    <th>Contact Details</th>
                                    <th class="text-center">Assigned Branch</th>
                                    <th class="text-center">Role / Designation</th>
                                    <th class="text-center">Status</th>
                                    @canany(['employee view', 'employee edit', 'employee delete'])
                                    <th class="text-end">Management</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($employees as $employee)
                                    <tr>
                                        <td class="text-muted fw-600">#{{ ($employees->currentPage()-1) * $employees->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="thumbnail-box rounded-circle shadow-sm" style="width: 40px; height: 40px; background: rgba(var(--primary-rgb), 0.1);">
                                                    <span class="text-primary fw-bold">{{ strtoupper(substr($employee->user->first_name ?? 'U', 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ ($employee->user->first_name ?? '') . ' ' . ($employee->user->last_name ?? 'Member') }}</div>
                                                    <div class="text-muted small">Joined: {{ $employee->created_at->format('M Y') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark small fw-600"><i class="fas fa-envelope me-2 opacity-50"></i>{{ $employee->user->email ?? 'no-email' }}</span>
                                                <span class="text-muted small mt-1"><i class="fas fa-phone me-2 opacity-50"></i>{{ $employee->phone ?? 'no-phone' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($employee->branch)
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 mb-1">
                                                        <i class="fas fa-store tiny-icon me-1"></i>{{ $employee->branch->name }}
                                                    </span>
                                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $employee->branch->location }}</small>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3">
                                                    <i class="fas fa-globe tiny-icon me-1"></i>Global Access
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="category-tag">
                                                {{ $employee->designation ?? 'Personnel' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $status = $employee->status ?: 'active';
                                            @endphp
                                            <span class="status-pill {{ $status == 'active' ? 'status-active' : 'status-inactive' }}">
                                                <i class="fas {{ $status == 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        @canany(['employee view', 'employee edit', 'employee delete'])
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-action" title="View Profile">
                                                    <i class="fas fa-user-circle"></i>
                                                </a>
                                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-action" title="Edit Access">
                                                    <i class="fas fa-pen-nib"></i>
                                                </a>
                                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-action" onclick="return confirm('Archive this personnel record?')" title="Delete Member">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="py-5">
                                                <i class="fas fa-user-slash fa-3x text-light mb-3"></i>
                                                <h5 class="text-muted font-weight-bold">Personnel Not Found</h5>
                                                <p class="text-muted small">Try refining your filter criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($employees->hasPages())
                <div class="card-footer bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing <span class="fw-bold">{{ $employees->firstItem() }}</span> to <span class="fw-bold">{{ $employees->lastItem() }}</span> of <span class="fw-bold">{{ $employees->total() }}</span> Personnel
                        </span>
                        {{ $employees->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection