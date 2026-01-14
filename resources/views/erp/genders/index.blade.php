@extends('erp.master')

@section('title', 'Gender Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('master.settings') }}">Master Settings</a></li>
                    <li class="breadcrumb-item active">Genders</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-bold mb-1 text-dark">Product Genders</h2>
                    <p class="text-muted mb-0">Define gender categories for your apparel or products.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addGenderModal">
                    <i class="fas fa-plus-circle"></i> <span>Add Gender</span>
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3 ps-4">ID</th>
                                <th class="border-0 py-3">Gender Name</th>
                                <th class="border-0 py-3 text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($genders as $gender)
                            <tr>
                                <td class="ps-4 text-muted small">#{{ $gender->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $gender->name }}</div>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle me-2 editGenderBtn" 
                                            data-id="{{ $gender->id }}" 
                                            data-name="{{ $gender->name }}" 
                                            data-bs-toggle="modal" data-bs-target="#editGenderModal">
                                        <i class="fas fa-edit text-info"></i>
                                    </button>
                                    <form action="{{ route('genders.destroy', $gender->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                <td colspan="3" class="text-center py-5 text-muted">No gender categories found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Gender Modal -->
    <div class="modal fade" id="addGenderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Add New Gender</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('genders.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Gender Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Mens, Womens, Unisex">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Gender</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Gender Modal -->
    <div class="modal fade" id="editGenderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Edit Gender</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editGenderForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Gender Name</label>
                            <input type="text" name="name" id="edit_gender_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update Gender</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editGenderBtn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#editGenderForm').attr('action', `/erp/genders/${id}`);
                $('#edit_gender_name').val(name);
            });
        });
    </script>
@endsection
