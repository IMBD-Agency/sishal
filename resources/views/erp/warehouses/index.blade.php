@extends('erp.master')

@section('title', 'Warehouses')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark">Warehouses</h2>
                            <p class="text-muted small mb-0">Central hubs for your ecommerce and branch fulfillment.</p>
                        </div>
                        <a href="{{ route('warehouses.create') }}" class="btn btn-primary shadow-sm rounded-3">
                            <i class="fas fa-plus me-1"></i>Create Warehouse
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive" style="overflow: visible;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                                        <tr>
                                            <th class="ps-4">Warehouse Name</th>
                                            <th>Location</th>
                                            <th>Manager</th>
                                            <th>Contact Info</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($warehouses as $warehouse)
                                            <tr>
                                                <td class="ps-4 fw-bold text-dark">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                                                            <i class="fas fa-warehouse text-primary small"></i>
                                                        </div>
                                                        {{ $warehouse->name }}
                                                    </div>
                                                </td>
                                                <td>{{ $warehouse->location }}</td>
                                                <td>
                                                    @if($warehouse->manager && $warehouse->manager->user)
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-light rounded-circle p-1 me-2" style="width: 24px; height: 24px; font-size: 10px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                            <small>{{ $warehouse->manager->user->first_name }} {{ $warehouse->manager->user->last_name }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($warehouse->contact_phone || $warehouse->contact_email)
                                                        <div class="small">
                                                            @if($warehouse->contact_phone)
                                                                <div class="text-muted"><i class="fas fa-phone-alt me-1 tiny-icon"></i> {{ $warehouse->contact_phone }}</div>
                                                            @endif
                                                            @if($warehouse->contact_email)
                                                                <div class="text-muted mt-1"><i class="fas fa-envelope me-1 tiny-icon"></i> {{ $warehouse->contact_email }}</div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $warehouse->status == 'active' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $warehouse->status == 'active' ? 'success' : 'danger' }} border border-{{ $warehouse->status == 'active' ? 'success' : 'danger' }} border-opacity-25 rounded-pill px-3">
                                                        {{ ucfirst($warehouse->status ?? 'active') }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm rounded-circle border-0" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 p-2">
                                                            <li><a class="dropdown-item rounded-2" href="{{ route('warehouses.show', $warehouse->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                                            <li><a class="dropdown-item rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#editWarehouseModal{{ $warehouse->id }}"><i class="fas fa-edit me-2 text-warning"></i>Edit Info</a></li>
                                                            <li><hr class="dropdown-divider border-light"></li>
                                                            <li>
                                                                <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" onsubmit="return confirm('Delete this warehouse?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item rounded-2 text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    
                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editWarehouseModal{{ $warehouse->id }}" tabindex="-1" aria-hidden="true">
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
                                                                            <div class="col-md-6 text-start">
                                                                                <label class="form-label small fw-bold text-muted">Warehouse Name</label>
                                                                                <input type="text" class="form-control rounded-3 shadow-sm-hover" name="name" value="{{ $warehouse->name }}" required>
                                                                            </div>
                                                                            <div class="col-md-6 text-start">
                                                                                <label class="form-label small fw-bold text-muted">Location</label>
                                                                                <input type="text" class="form-control rounded-3 shadow-sm-hover" name="location" value="{{ $warehouse->location }}" required>
                                                                            </div>
                                                                            <div class="col-md-6 text-start">
                                                                                <label class="form-label small fw-bold text-muted">Contact Phone</label>
                                                                                <input type="text" class="form-control rounded-3 shadow-sm-hover" name="contact_phone" value="{{ $warehouse->contact_phone }}">
                                                                            </div>
                                                                            <div class="col-md-6 text-start">
                                                                                <label class="form-label small fw-bold text-muted">Contact Email</label>
                                                                                <input type="email" class="form-control rounded-3 shadow-sm-hover" name="contact_email" value="{{ $warehouse->contact_email }}">
                                                                            </div>
                                                                            <div class="col-md-6 text-start">
                                                                                <label class="form-label small fw-bold text-muted">Manager</label>
                                                                                <select class="form-select rounded-3 shadow-sm-hover" name="manager_id">
                                                                                    <option value="">-- No Manager --</option>
                                                                                    @foreach($employees as $employee)
                                                                                        <option value="{{ $employee->id }}" {{ $warehouse->manager_id == $employee->id ? 'selected' : '' }}>
                                                                                            {{ $employee->user->first_name ?? '' }} {{ $employee->user->last_name ?? '' }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-6 text-start">
                                                                                <label class="form-label small fw-bold text-muted">Status</label>
                                                                                <select class="form-select rounded-3 shadow-sm-hover" name="status" required>
                                                                                    <option value="active" {{ $warehouse->status == 'active' ? 'selected' : '' }}>Active</option>
                                                                                    <option value="inactive" {{ $warehouse->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer border-light p-4 pt-0">
                                                                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Update Warehouse</button>
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
                                                        <p class="mb-0">No warehouses found.</p>
                                                        <a href="{{ route('warehouses.create') }}" class="btn btn-link btn-sm mt-2 text-decoration-none transition-2">Create your first warehouse</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($warehouses->hasPages())
                                <div class="p-4 border-top border-light">
                                    {{ $warehouses->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
