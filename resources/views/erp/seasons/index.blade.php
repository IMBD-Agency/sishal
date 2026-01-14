@extends('erp.master')

@section('title', 'Season Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('master.settings') }}">Master Settings</a></li>
                    <li class="breadcrumb-item active">Seasons</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-bold mb-1 text-dark">Product Seasons</h2>
                    <p class="text-muted mb-0">Manage seasonal categorization for your items.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSeasonModal">
                    <i class="fas fa-plus-circle"></i> <span>Add Season</span>
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3 ps-4">ID</th>
                                <th class="border-0 py-3">Season Name</th>
                                <th class="border-0 py-3 text-center">Status</th>
                                <th class="border-0 py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($seasons as $season)
                            <tr>
                                <td class="ps-4 text-muted small">#{{ $season->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $season->name }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $season->status === 'active' ? 'success-subtle text-success' : 'danger-subtle text-danger' }} rounded-pill px-3">
                                        {{ ucfirst($season->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle me-2 editSeasonBtn" 
                                            data-id="{{ $season->id }}" 
                                            data-name="{{ $season->name }}" 
                                            data-status="{{ $season->status }}"
                                            data-bs-toggle="modal" data-bs-target="#editSeasonModal">
                                        <i class="fas fa-edit text-info"></i>
                                    </button>
                                    <form action="{{ route('seasons.destroy', $season->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                <td colspan="4" class="text-center py-5 text-muted">No seasons found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Season Modal -->
    <div class="modal fade" id="addSeasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Add New Season</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('seasons.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Season Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Summer 2026">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Season</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Season Modal -->
    <div class="modal fade" id="editSeasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Edit Season</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSeasonForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Season Name</label>
                            <input type="text" name="name" id="edit_season_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" id="edit_season_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update Season</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editSeasonBtn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const status = $(this).data('status');
                
                $('#editSeasonForm').attr('action', `/erp/seasons/${id}`);
                $('#edit_season_name').val(name);
                $('#edit_season_status').val(status);
            });
        });
    </script>
@endsection
