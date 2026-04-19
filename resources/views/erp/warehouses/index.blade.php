@extends('erp.master')

@section('title', 'Warehouses Management')

@section('body')
<style>
    :root {
        --primary-color: #2d5a4c;
        --border-radius: 16px;
    }

    .glass-header {
        background: white;
        padding: 2rem 2.5rem;
        border-bottom: 1px solid #edf2f7;
        margin-bottom: 0;
    }

    .btn-create-premium {
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(45, 90, 76, 0.2);
    }

    .btn-create-premium:hover {
        background: #23473b;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(45, 90, 76, 0.3);
        color: white;
    }

    .premium-card {
        border: none;
        border-radius: var(--border-radius);
        background: white;
        overflow: hidden;
    }

    .premium-table thead th {
        background: #f8fafc;
        border-bottom: 2px solid #edf2f7;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
    }

    .premium-table tbody td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .hover-primary:hover {
        color: var(--primary-color) !important;
    }
</style>

@include('erp.components.sidebar')

<div class="main-content bg-light min-vh-100" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Warehouses Management</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Central Distribution Hubs</h4>
                <p class="text-muted small mb-0">{{ $warehouses->total() }} Warehouses registered in the system</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 align-items-md-center">
                <a href="{{ route('warehouses.create') }}" class="btn btn-create-premium">
                    <i class="fas fa-plus-circle me-2"></i>Create Warehouse
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="premium-card shadow-sm mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Warehouse Name</th>
                                <th>Location</th>
                                <th>Manager</th>
                                <th>Contact & Stock</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses as $warehouse)
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; border: 1px solid rgba(45, 90, 76, 0.1);">
                                                <i class="fas fa-warehouse text-primary"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('warehouses.show', $warehouse->id) }}" class="d-block fw-bold text-dark text-decoration-none hover-primary">{{ $warehouse->name }}</a>
                                                <small class="text-muted">ID: #{{ $warehouse->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark"><i class="fas fa-map-marker-alt text-muted me-2"></i>{{ $warehouse->location }}</div>
                                    </td>
                                    <td>
                                        @if($warehouse->manager && $warehouse->manager->user)
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-muted small"></i>
                                                </div>
                                                <div class="small fw-medium">{{ $warehouse->manager->user->first_name }} {{ $warehouse->manager->user->last_name }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted small italic">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($warehouse->contact_phone || $warehouse->contact_email)
                                            <div class="small">
                                                @if($warehouse->contact_phone)
                                                    <div class="text-muted"><i class="fas fa-phone-alt me-2 tiny-icon"></i>{{ $warehouse->contact_phone }}</div>
                                                @endif
                                                @if($warehouse->contact_email)
                                                    <div class="text-muted mt-1"><i class="fas fa-envelope me-2 tiny-icon"></i>{{ $warehouse->contact_email }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill {{ $warehouse->status == 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} px-3 py-2 border" style="border-color: currentColor !important; font-size: 0.75rem;">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            {{ strtoupper($warehouse->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-light border" title="View Details">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editWarehouseModal{{ $warehouse->id }}" title="Edit">
                                                <i class="fas fa-edit text-warning"></i>
                                            </button>
                                            <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" onsubmit="return confirm('Archive this warehouse?')" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border" title="Delete">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editWarehouseModal{{ $warehouse->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4">
                                                    <div class="modal-header border-light p-4">
                                                        <h5 class="modal-title fw-bold"><i class="fas fa-edit text-warning me-2"></i>Edit Warehouse: {{ $warehouse->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-body p-4">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted text-uppercase">Warehouse Name</label>
                                                                    <input type="text" class="form-control" name="name" value="{{ $warehouse->name }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted text-uppercase">Location Address</label>
                                                                    <input type="text" class="form-control" name="location" value="{{ $warehouse->location }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted text-uppercase">Contact Phone</label>
                                                                    <input type="text" class="form-control" name="contact_phone" value="{{ $warehouse->contact_phone }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted text-uppercase">Contact Email</label>
                                                                    <input type="email" class="form-control" name="contact_email" value="{{ $warehouse->contact_email }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted text-uppercase">Manager</label>
                                                                    <select class="form-select" name="manager_id">
                                                                        <option value="">-- No Manager --</option>
                                                                        @foreach($employees as $employee)
                                                                            <option value="{{ $employee->id }}" {{ $warehouse->manager_id == $employee->id ? 'selected' : '' }}>
                                                                                {{ $employee->user->first_name ?? '' }} {{ $employee->user->last_name ?? '' }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted text-uppercase">Operational Status</label>
                                                                    <select class="form-select" name="status" required>
                                                                        <option value="active" {{ $warehouse->status == 'active' ? 'selected' : '' }}>🟢 Active</option>
                                                                        <option value="inactive" {{ $warehouse->status == 'inactive' ? 'selected' : '' }}>🔴 Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-light p-4 pt-0">
                                                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Discard</button>
                                                            <button type="submit" class="btn-create-premium py-2 px-4 shadow-none">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-warehouse fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">No distribution hubs found in records.</p>
                                            <a href="{{ route('warehouses.create') }}" class="btn btn-link btn-sm mt-2 text-decoration-none transition-2">Establish your first hub</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($warehouses->hasPages())
                    <div class="p-4 border-top border-light d-flex justify-content-center">
                        {{ $warehouses->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
