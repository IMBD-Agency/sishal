@extends('erp.master')

@section('title', 'Unit Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('master.settings') }}">Master Settings</a></li>
                    <li class="breadcrumb-item active">Units</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-bold mb-1 text-dark">Measurement Units</h2>
                    <p class="text-muted mb-0">Manage units of measure for your products.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                    <i class="fas fa-plus-circle"></i> <span>Add Unit</span>
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3 ps-4">ID</th>
                                <th class="border-0 py-3">Unit Name</th>
                                <th class="border-0 py-3">Short Name</th>
                                <th class="border-0 py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($units as $unit)
                            <tr>
                                <td class="ps-4 text-muted small">#{{ $unit->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $unit->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info px-3">{{ $unit->short_name }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle me-2 editUnitBtn" 
                                            data-id="{{ $unit->id }}" 
                                            data-name="{{ $unit->name }}" 
                                            data-short="{{ $unit->short_name }}"
                                            data-bs-toggle="modal" data-bs-target="#editUnitModal">
                                        <i class="fas fa-edit text-info"></i>
                                    </button>
                                    <form action="{{ route('units.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border-0 rounded-circle">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No units found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div class="modal fade" id="addUnitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Add New Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Unit Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Kilogram">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Short Name</label>
                            <input type="text" name="short_name" class="form-control" required placeholder="e.g. Kg">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUnitForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Unit Name</label>
                            <input type="text" name="name" id="edit_unit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Short Name</label>
                            <input type="text" name="short_name" id="edit_unit_short" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editUnitBtn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const short = $(this).data('short');
                
                $('#editUnitForm').attr('action', `/erp/units/${id}`);
                $('#edit_unit_name').val(name);
                $('#edit_unit_short').val(short);
            });
        });
    </script>
@endsection
