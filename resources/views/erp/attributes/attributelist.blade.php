@extends('erp.master')

@section('title', 'Product Attributes Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Header Section -->
        <div class="container-fluid px-4 py-4 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('product.list') }}" class="text-decoration-none">Products</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Attributes</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-1">
                        <i class="fas fa-tags text-primary me-2"></i>Product Attributes
                    </h2>
                    <p class="text-muted mb-0">Manage product specifications and attributes for better product organization.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('attribute.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Attribute
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="container-fluid px-4 py-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <div class="container-fluid px-4 py-4">
            <!-- Search and Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search attributes by name or slug...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <span class="badge bg-info fs-6">
                                <i class="fas fa-list me-1"></i>{{ $attributes->total() }} Total Attributes
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attributes Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2 text-primary"></i>Attributes List
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="attributesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 py-3 px-4">
                                        <i class="fas fa-hashtag me-1 text-muted"></i>ID
                                    </th>
                                    <th class="border-0 py-3 px-4">
                                        <i class="fas fa-tag me-1 text-muted"></i>Attribute Name
                                    </th>
                                    <th class="border-0 py-3 px-4">
                                        <i class="fas fa-link me-1 text-muted"></i>Slug
                                    </th>
                                    <th class="border-0 py-3 px-4">
                                        <i class="fas fa-info-circle me-1 text-muted"></i>Description
                                    </th>
                                    <th class="border-0 py-3 px-4">
                                        <i class="fas fa-toggle-on me-1 text-muted"></i>Status
                                    </th>
                                    <th class="border-0 py-3 px-4 text-center">
                                        <i class="fas fa-cogs me-1 text-muted"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attributes as $attribute)
                                    <tr class="attribute-row" data-name="{{ strtolower($attribute->name) }}" data-slug="{{ strtolower($attribute->slug) }}" data-status="{{ $attribute->status }}">
                                        <td class="px-4 py-3">
                                            <span class="fw-bold text-primary">#{{ $attribute->id }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                    <i class="fas fa-tag text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $attribute->name }}</h6>
                                                    <small class="text-muted">Created {{ $attribute->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <code class="bg-light px-2 py-1 rounded">{{ $attribute->slug }}</code>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-muted">{{ Str::limit($attribute->description, 50) ?: 'No description' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-{{ $attribute->status === 'active' ? 'success' : 'secondary' }} fs-6 px-3 py-2">
                                                <i class="fas fa-{{ $attribute->status === 'active' ? 'check' : 'times' }} me-1"></i>
                                                {{ ucfirst($attribute->status ?? 'inactive') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('attribute.show', $attribute->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('attribute.edit', $attribute->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Attribute">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $attribute->id }}, '{{ $attribute->name }}')" title="Delete Attribute">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <h5>No Attributes Found</h5>
                                                <p>Start by creating your first product attribute.</p>
                                                <a href="{{ route('attribute.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Add First Attribute
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($attributes->hasPages())
                    <div class="card-footer bg-white border-0 py-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            <div class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing {{ $attributes->firstItem() }} to {{ $attributes->lastItem() }} of {{ $attributes->total() }} results
                            </div>
                            <div class="d-flex align-items-center">
                                {{ $attributes->appends(request()->query())->links('pagination.bootstrap-4-custom') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Showing all {{ $attributes->total() }} results
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the attribute <strong id="attributeName"></strong>?</p>
                    <p class="text-muted small">This action cannot be undone and may affect products using this attribute.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Attribute</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .attribute-row:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        /* Custom Pagination Styles */
        .pagination {
            margin: 0;
            gap: 0.25rem;
        }
        .pagination .page-link {
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            margin: 0 0.125rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
            text-decoration: none;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-radius: 0.375rem;
        }
        .pagination .page-item .fas {
            font-size: 0.75rem;
        }
    </style>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.attribute-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.dataset.name;
                const slug = row.dataset.slug;
                
                if (name.includes(searchTerm) || slug.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update pagination info if searching
            if (searchTerm) {
                updatePaginationInfo(visibleCount, searchTerm);
            } else {
                resetPaginationInfo();
            }
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value;
            const rows = document.querySelectorAll('.attribute-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const status = row.dataset.status;
                
                if (!selectedStatus || status === selectedStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update pagination info if filtering
            if (selectedStatus) {
                updatePaginationInfo(visibleCount, `status: ${selectedStatus}`);
            } else {
                resetPaginationInfo();
            }
        });

        function updatePaginationInfo(count, filter) {
            const paginationInfo = document.querySelector('.card-footer .text-muted');
            if (paginationInfo) {
                paginationInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Showing ${count} results (filtered by: ${filter})`;
            }
        }

        function resetPaginationInfo() {
            const paginationInfo = document.querySelector('.card-footer .text-muted');
            if (paginationInfo) {
                paginationInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Showing {{ $attributes->firstItem() }} to {{ $attributes->lastItem() }} of {{ $attributes->total() }} results`;
            }
        }

        // Delete confirmation
        function confirmDelete(id, name) {
            document.getElementById('attributeName').textContent = name;
            document.getElementById('deleteForm').action = `/erp/attributes/${id}`;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
@endsection