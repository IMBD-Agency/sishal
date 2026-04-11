@extends('erp.master')

@section('title', 'User Access & Roles')

@push('styles')
<style>
    /* Tab Navigation Styling */
    .premium-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        background: #fff;
        padding: 0.5rem;
        border-radius: 12px;
        box-shadow: var(--premium-shadow);
        width: fit-content;
    }

    .premium-tabs .nav-link {
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        color: #64748b;
        font-weight: 600;
        transition: var(--transition);
        background: transparent;
    }

    .premium-tabs .nav-link:hover {
        background: #f1f5f9;
        color: var(--primary-green);
    }

    .premium-tabs .nav-link.active {
        background: var(--primary-green);
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(25, 135, 84, 0.2);
    }

    .permission-group-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 1rem;
        transition: var(--transition);
    }

    .permission-group-card:hover {
        border-color: var(--primary-green);
        background-color: #f8fafc;
    }

    .permission-checkbox:checked + .form-check-label {
        color: var(--primary-green);
        font-weight: 600;
    }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Glass Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Access Control</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">User Access & Roles</h4>
                    <p class="text-muted small mb-0">Define permissions and manage user assignments</p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-create-premium" data-bs-toggle="modal" data-bs-target="#addUserRoleModal">
                        <i class="fas fa-plus-circle me-2"></i>Create New Role
                    </button>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Navigation Tabs -->
            <ul class="nav premium-tabs" id="accessTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-panel" type="button" role="tab">
                        <i class="fas fa-shield-alt me-2"></i>Role Definitions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-panel" type="button" role="tab">
                        <i class="fas fa-users-cog me-2"></i>User Assignments
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="accessTabsContent">
                <!-- Tab 1: Roles Management -->
                <div class="tab-pane fade show active" id="roles-panel" role="tabpanel">
                    <div class="premium-card">
                        <div class="card-header bg-white border-0 py-3 px-4">
                            <h5 class="fw-bold mb-0">Registered Roles</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table premium-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 250px;">Role Name</th>
                                            <th>Permissions Summary</th>
                                            <th class="text-end">Management</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roles as $role)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $role->name }}</div>
                                                    <small class="text-muted">{{ $role->permissions->count() }} active permissions</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($role->permissions->take(10) as $permission)
                                                            <span class="category-tag" style="font-size: 0.7rem; text-transform: capitalize;">{{ $permission->name }}</span>
                                                        @endforeach
                                                        @if($role->permissions->count() > 10)
                                                            <span class="badge bg-light text-muted border">+{{ $role->permissions->count() - 10 }} more</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-action" onclick="openEditModal({{ $role->id }}, '{{ $role->name }}', [{{ $role->permissions->pluck('id')->implode(',') }}])" title="Edit Role">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        @if($role->name !== 'SuperAdmin')
                                                            <form action="{{ route('userRole.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this role? All associated users will lose these permissions.')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-action" title="Delete Role">
                                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: User List -->
                <div class="tab-pane fade" id="users-panel" role="tabpanel">
                    <div class="premium-card">
                        <div class="card-header bg-white border-0 py-3 px-4">
                            <h5 class="fw-bold mb-0">User Access Overview</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table premium-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Account Identity</th>
                                            <th>Access Level</th>
                                            <th>Branch Alignment</th>
                                            <th class="text-end">Current Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="thumbnail-box rounded-circle shadow-sm" style="width: 42px; height: 42px; background: rgba(var(--primary-rgb), 0.1);">
                                                            <span class="text-primary fw-bold">{{ strtoupper(substr($user->first_name, 0, 1)) }}</span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                                            <div class="text-muted small">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @foreach ($user->roles as $role)
                                                        <span class="category-tag">
                                                            <i class="fas fa-user-shield me-1"></i>{{ $role->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($user->roles->isEmpty())
                                                        <span class="text-muted italic small">Restricted Guest</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->employee && $user->employee->branch)
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                                            <i class="fas fa-store-alt me-1"></i>{{ $user->employee->branch->name }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3">
                                                            <i class="fas fa-globe-americas me-1"></i>Universal
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @php $status = $user->employee->status ?? 'active'; @endphp
                                                    <span class="status-pill {{ $status == 'active' ? 'status-active' : 'status-inactive' }}">
                                                        <i class="fas {{ $status == 'active' ? 'fa-check' : 'fa-times' }}"></i>
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <i class="fas fa-user-slash fa-3x text-light mb-3"></i>
                                                    <p class="text-muted">No active user records found.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Role Modal -->
        <div class="modal fade" id="addUserRoleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Create Security Role</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('userRole.store') }}" method="post">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold small text-uppercase text-muted">Internal Role Label</label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="e.g. Regional Manager" required>
                                <div class="form-text mt-2">Roles group permissions together to be assigned to users.</div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-0">Available Permissions</label>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-success global-select-all rounded-start-pill px-3">
                                        <i class="fas fa-check-double me-1"></i>Select All Modules
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger global-deselect-all rounded-end-pill px-3">
                                        <i class="fas fa-times me-1"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Permission Tabs Inside Modal -->
                            <div class="card border-0 bg-light rounded-4 overflow-hidden">
                                <div class="card-header bg-white py-3 border-0">
                                    <ul class="nav nav-pills gap-2" id="permissionTabs" role="tablist">
                                        @foreach($permissionsByCategory as $category => $categoryPermissions)
                                            <li class="nav-item">
                                                <button class="nav-link py-2 px-3 rounded-pill {{ $loop->first ? 'active' : '' }} text-capitalize" 
                                                        id="tab-{{ Str::slug($category) }}" data-bs-toggle="tab" data-bs-target="#content-{{ Str::slug($category) }}" type="button">
                                                    {{ $category }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="card-body p-4">
                                    <div class="tab-content" id="permissionTabsContent">
                                        @foreach($permissionsByCategory as $category => $categoryPermissions)
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="content-{{ Str::slug($category) }}">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="fw-bold mb-0 text-capitalize">{{ $category }} Permissions</h6>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary select-all-category rounded-pill" data-category="{{ Str::slug($category) }}">Select All</button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary deselect-all-category rounded-pill" data-category="{{ Str::slug($category) }}">Deselect</button>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    @foreach($categoryPermissions as $permission)
                                                        <div class="col-md-4">
                                                            <div class="form-check permission-group-card p-3 mb-0">
                                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}">
                                                                <label class="form-check-label ms-1" for="permission_{{ $permission->id }}">
                                                                    {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-create-premium px-5">Save Configuration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit User Role Modal -->
        <div class="modal fade" id="editUserRoleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Modify Security Role</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editRoleForm" action="#" method="post">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label for="edit_name" class="form-label fw-bold small text-uppercase text-muted">Role Label</label>
                                <input type="text" class="form-control form-control-lg" id="edit_name" name="name" required>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-0">Adjust Permissions</label>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-success edit-global-select-all rounded-start-pill px-3">
                                        <i class="fas fa-check-double me-1"></i>Select All Modules
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger edit-global-deselect-all rounded-end-pill px-3">
                                        <i class="fas fa-times me-1"></i>Deselect All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card border-0 bg-light rounded-4 overflow-hidden">
                                <div class="card-header bg-white py-3 border-0">
                                    <ul class="nav nav-pills gap-2" id="editPermissionTabs" role="tablist">
                                        @foreach($permissionsByCategory as $category => $categoryPermissions)
                                            <li class="nav-item">
                                                <button class="nav-link py-2 px-3 rounded-pill {{ $loop->first ? 'active' : '' }} text-capitalize" 
                                                        id="edit-tab-{{ Str::slug($category) }}" data-bs-toggle="tab" data-bs-target="#edit-content-{{ Str::slug($category) }}" type="button">
                                                    {{ $category }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="card-body p-4">
                                    <div class="tab-content">
                                        @foreach($permissionsByCategory as $category => $categoryPermissions)
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="edit-content-{{ Str::slug($category) }}">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="fw-bold mb-0 text-capitalize">{{ $category }} Access</h6>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary edit-select-all-category rounded-pill" data-category="{{ Str::slug($category) }}">Select All</button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary edit-deselect-all-category rounded-pill" data-category="{{ Str::slug($category) }}">Deselect</button>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    @foreach($categoryPermissions as $permission)
                                                        <div class="col-md-4">
                                                            <div class="form-check permission-group-card p-3 mb-0">
                                                                <input class="form-check-input edit-permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="edit_permission_{{ $permission->id }}">
                                                                <label class="form-check-label ms-1" for="edit_permission_{{ $permission->id }}">
                                                                    {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-create-premium px-5">Commit Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function openEditModal(roleId, roleName, permissions) {
    const modal = document.getElementById('editUserRoleModal');
    const form = document.getElementById('editRoleForm');
    const nameInput = form.querySelector('#edit_name');
    
    form.action = `/erp/user-role/${roleId}`;
    nameInput.value = roleName;
    
    document.querySelectorAll('.edit-permission-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    if (permissions && permissions.length > 0) {
        permissions.forEach(permissionId => {
            const checkbox = document.getElementById(`edit_permission_${permissionId}`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Selection logic for Add Modal
    document.querySelectorAll('.select-all-category').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            document.querySelectorAll('#content-' + category + ' .permission-checkbox').forEach(cb => cb.checked = true);
        });
    });

    document.querySelectorAll('.deselect-all-category').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            document.querySelectorAll('#content-' + category + ' .permission-checkbox').forEach(cb => cb.checked = false);
        });
    });

    // Selection logic for Edit Modal
    document.querySelectorAll('.edit-select-all-category').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            document.querySelectorAll('#edit-content-' + category + ' .edit-permission-checkbox').forEach(cb => cb.checked = true);
        });
    });

    document.querySelectorAll('.edit-deselect-all-category').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            document.querySelectorAll('#edit-content-' + category + ' .edit-permission-checkbox').forEach(cb => cb.checked = false);
        });
    });

    // Global Selection Logic for Add Modal
    document.querySelector('.global-select-all').addEventListener('click', function() {
        document.querySelectorAll('#addUserRoleModal .permission-checkbox').forEach(cb => cb.checked = true);
    });

    document.querySelector('.global-deselect-all').addEventListener('click', function() {
        document.querySelectorAll('#addUserRoleModal .permission-checkbox').forEach(cb => cb.checked = false);
    });

    // Global Selection Logic for Edit Modal
    document.querySelector('.edit-global-select-all').addEventListener('click', function() {
        document.querySelectorAll('#editUserRoleModal .edit-permission-checkbox').forEach(cb => cb.checked = true);
    });

    document.querySelector('.edit-global-deselect-all').addEventListener('click', function() {
        document.querySelectorAll('#editUserRoleModal .edit-permission-checkbox').forEach(cb => cb.checked = false);
    });
});
</script>
@endpush