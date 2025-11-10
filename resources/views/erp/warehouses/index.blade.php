@extends('erp.master')

@section('title', 'Warehouses')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Warehouses</h2>
                        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create Warehouse
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>Branch</th>
                                            <th>Manager</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($warehouses as $warehouse)
                                            <tr>
                                                <td>{{ $warehouse->name }}</td>
                                                <td>{{ $warehouse->location }}</td>
                                                <td>
                                                    @if($warehouse->branch)
                                                        <span class="badge bg-info">{{ $warehouse->branch->name }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">Ecommerce Only</span>
                                                    @endif
                                                </td>
                                                <td>{{ $warehouse->manager ? ($warehouse->manager->first_name . ' ' . $warehouse->manager->last_name) : 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ ($warehouse->status ?? 'active') == 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($warehouse->status ?? 'active') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                    <a href="{{ route('warehouses.show', $warehouse->id) }}" 
                                                           class="btn btn-sm btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <!-- Edit Button -->
                                                        <button class="btn btn-sm btn-outline-warning" 
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editWarehouseModal{{ $warehouse->id }}" 
                                                                title="Edit Warehouse">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <!-- Delete Form -->
                                                        <form action="{{ route('warehouses.destroy', $warehouse->id) }}" 
                                                              method="POST"
                                                              style="display:inline-block"
                                                              onsubmit="return confirm('Are you sure you want to delete this warehouse? This action cannot be undone.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Warehouse">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    
                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editWarehouseModal{{ $warehouse->id }}" tabindex="-1"
                                                        aria-labelledby="editWarehouseModalLabel{{ $warehouse->id }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"
                                                                        id="editWarehouseModalLabel{{ $warehouse->id }}">Edit Warehouse
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <form action="{{ route('warehouses.update', $warehouse->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label for="warehouse_name_{{ $warehouse->id }}"
                                                                                class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                                                            <input type="text" class="form-control"
                                                                                id="warehouse_name_{{ $warehouse->id }}" name="name"
                                                                                value="{{ $warehouse->name }}" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="warehouse_location_{{ $warehouse->id }}"
                                                                                class="form-label">Location <span class="text-danger">*</span></label>
                                                                            <input type="text" class="form-control"
                                                                                id="warehouse_location_{{ $warehouse->id }}"
                                                                                name="location" value="{{ $warehouse->location }}"
                                                                                required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="warehouse_branch_{{ $warehouse->id }}"
                                                                                class="form-label">Branch (Optional)</label>
                                                                            <select class="form-control" 
                                                                                    id="warehouse_branch_{{ $warehouse->id }}"
                                                                                    name="branch_id">
                                                                                <option value="">-- No Branch (Ecommerce Only) --</option>
                                                                                @foreach($branches as $branch)
                                                                                    <option value="{{ $branch->id }}" 
                                                                                            {{ $warehouse->branch_id == $branch->id ? 'selected' : '' }}>
                                                                                        {{ $branch->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                            <small class="form-text text-muted">
                                                                                <i class="fas fa-info-circle"></i> Leave empty for ecommerce warehouses. Select a branch only if this warehouse is for POS operations.
                                                                            </small>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="warehouse_manager_{{ $warehouse->id }}"
                                                                                class="form-label">Manager (Optional)</label>
                                                                            <select class="form-control" 
                                                                                    id="warehouse_manager_{{ $warehouse->id }}"
                                                                                    name="manager_id">
                                                                                <option value="">-- Select Manager --</option>
                                                                                @foreach($users as $user)
                                                                                    <option value="{{ $user->id }}" 
                                                                                            {{ $warehouse->manager_id == $user->id ? 'selected' : '' }}>
                                                                                        {{ $user->first_name }} {{ $user->last_name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Update Warehouse</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-warehouse fa-3x mb-3"></i>
                                                        <p>No warehouses found. Create your first warehouse to get started.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $warehouses->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

