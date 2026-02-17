@extends('erp.master')

@section('title', 'Branch Management')

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
                        <li class="breadcrumb-item active text-primary fw-600">Branch Management</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Operational Hubs</h4>
                <p class="text-muted small mb-0"><span id="outletCount">Loading...</span> in our network</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-file-export me-2"></i>Export Center
                </button>
                <a href="{{ route('branches.create') }}" class="btn btn-create-premium">
                    <i class="fas fa-plus-circle me-2"></i>New Outlet
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <!-- Advanced Filters -->
        <div class="filter-section shadow-sm mb-4 p-4">
            <form id="filterForm" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-uppercase text-muted">Search Store</label>
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" name="name" placeholder="Name, Location..." value="{{ request('name') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold small text-uppercase">Status</label>
                    <select class="form-select border-radius-10" name="status" style="border-radius: 10px;">
                        <option value="">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-semibold small text-uppercase">Online Status</label>
                    <select class="form-select" name="show_online" style="border-radius: 10px;">
                        <option value="">All Types</option>
                        <option value="1">Ecommerce Enabled</option>
                        <option value="0">Offline Only</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn px-4 w-100 fw-bold" style="background: var(--primary-color); color: white; border-radius: 10px; height: 42px;">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <button type="button" class="btn btn-light px-4 w-100 fw-bold border" id="resetFilter" style="border-radius: 10px; height: 42px;">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Branches Table -->
        <div class="premium-card shadow-sm mb-5">
            <div class="card-body p-0">
                <!-- Desktop Table -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table premium-table mb-0" id="branchesTable">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>Branch Identity</th>
                                <th>Contact & Location</th>
                                <th class="text-center">Show Online</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile List -->
                <div class="d-md-none" id="branchesListMobile">
                <!-- Loaded via AJAX -->
                </div>
            </div>
            <div class="card-footer bg-white py-3 border-top">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <span class="text-muted small" id="branchCount">Data is being synchronized...</span>
                    </div>
                    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end">
                        <nav id="branchesPaginationContainer">
                            <ul class="pagination pagination-sm mb-0" id="branchesPagination">
                                <!-- Loaded via AJAX -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header text-white px-4" style="background: var(--primary-color);">
                    <h5 class="modal-title fw-bold">Generate Branch Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Customize your export by selecting the data points you need for your branch network report.</p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded-3 h-100">
                                <input class="form-check-input ms-0 me-2 column-selector" type="checkbox" value="id" id="col_id" checked>
                                <label class="form-check-label fw-bold" for="col_id">Branch ID</label>
                                <div class="small text-muted">Include unique system identifiers.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded-3 h-100">
                                <input class="form-check-input ms-0 me-2 column-selector" type="checkbox" value="name" id="col_name" checked>
                                <label class="form-check-label fw-bold" for="col_name">Branch Name</label>
                                <div class="small text-muted">Include the official outlet name.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded-3 h-100">
                                <input class="form-check-input ms-0 me-2 column-selector" type="checkbox" value="location" id="col_location" checked>
                                <label class="form-check-label fw-bold" for="col_location">Location Details</label>
                                <div class="small text-muted">Include address and area info.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check p-3 border rounded-3 h-100">
                                <input class="form-check-input ms-0 me-2 column-selector" type="checkbox" value="contact_info" id="col_contact_info" checked>
                                <label class="form-check-label fw-bold" for="col_contact_info">Contact Number</label>
                                <div class="small text-muted">Include branch phone numbers.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-link px-0 text-decoration-none fw-bold" id="selectAllColumns" style="color: var(--primary-color);">Select All Channels</button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success px-4" id="exportExcel" style="border-radius: 10px;">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </button>
                            <button type="button" class="btn btn-danger px-4" id="exportPdf" style="border-radius: 10px;">
                                <i class="fas fa-file-pdf me-2"></i>PDF Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilterState = {};

    // Initial load
    fetchBranches();

    // Filters
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchBranches();
    });

    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('filterForm').reset();
        fetchBranches();
    });

    function fetchBranches(page = 1) {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        params.append('page', page);
        
        // UI Feedback
        const tbody = document.querySelector('#branchesTable tbody');
        if(tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        fetch(`{{ url('/erp/branches/fetch') }}?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                renderDesktopTable(data);
                renderMobileList(data);
                renderPagination(data);
                updateCounter(data);
            })
            .catch(error => {
                console.error('Error:', error);
                if(tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-2"></i>Sync error. Please refresh.</td></tr>';
            });
    }

    function renderDesktopTable(data) {
        const tbody = document.querySelector('#branchesTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No branches matching your criteria.</td></tr>';
            return;
        }

        data.data.forEach(branch => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-muted fw-medium">#${branch.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; border: 1px solid #edf2f7;">
                            <i class="fas fa-store text-primary"></i>
                        </div>
                        <div>
                            <a href="/erp/branches/${branch.id}" class="d-block fw-bold text-dark text-decoration-none hover-primary">${branch.name}</a>
                            <small class="text-muted">Manager: ${branch.manager ? branch.manager.first_name + ' ' + branch.manager.last_name : 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-dark small"><i class="fas fa-map-marker-alt text-muted me-2"></i>${branch.location || 'N/A'}</div>
                    <div class="text-muted small mt-1"><i class="fas fa-phone-alt text-muted me-2"></i>${branch.contact_info || 'N/A'}</div>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill ${branch.show_online ? 'bg-info bg-opacity-10 text-info' : 'bg-secondary bg-opacity-10 text-secondary'}" style="font-size: 0.7rem; border: 1px solid currentColor;">
                        <i class="fas ${branch.show_online ? 'fa-globe' : 'fa-times-circle'} me-1"></i>
                        ${branch.show_online ? 'Ecommerce' : 'Offline'}
                    </span>
                </td>
                <td class="text-center">
                    <span class="status-badge ${branch.status === 'active' ? 'status-active' : 'status-inactive'}">
                        <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                        ${branch.status.toUpperCase()}
                    </span>
                </td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <a href="/erp/branches/${branch.id}/edit" class="btn-action btn-light border text-warning" title="Edit Properties">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <button onclick="deleteBranch(${branch.id})" class="btn-action btn-light border text-danger" title="Shut Down Branch">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderMobileList(data) {
        const container = document.getElementById('branchesListMobile');
        if (!container) return;
        container.innerHTML = '';

        if (!data.data || data.data.length === 0) {
            container.innerHTML = '<div class="p-5 text-center text-muted">No results.</div>';
            return;
        }

        data.data.forEach(branch => {
            const item = document.createElement('div');
            item.className = 'p-3 border-bottom bg-white';
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0">${branch.name}</h6>
                    <span class="badge ${branch.status === 'active' ? 'bg-success' : 'bg-danger'}">${branch.status}</span>
                </div>
                <div class="text-muted small mb-3">
                    <div><i class="fas fa-map-marker-alt me-2"></i>${branch.location}</div>
                    <div><i class="fas fa-phone me-2"></i>${branch.contact_info}</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="/erp/branches/${branch.id}" class="btn btn-sm btn-light border flex-grow-1">View Details</a>
                    <a href="/erp/branches/${branch.id}/edit" class="btn btn-sm btn-light border"><i class="fas fa-edit text-warning"></i></a>
                </div>
            `;
            container.appendChild(item);
        });
    }

    function renderPagination(data) {
        const container = document.getElementById('branchesPagination');
        if (!container) return;
        container.innerHTML = '';
        
        if (data.last_page <= 1) return;

        // Simplified logic for brevity
        for (let i = 1; i <= data.last_page; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === data.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link shadow-none border-0" href="#" style="${i === data.current_page ? 'background: var(--primary-color);' : ''}">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                fetchBranches(i);
            });
            container.appendChild(li);
        }
    }

    function updateCounter(data) {
        const count = document.getElementById('branchCount');
        const topCount = document.getElementById('branchCountTop');
        const text = `Displaying ${data.from || 0} - ${data.to || 0} of ${data.total} branches`;
        if(count) count.innerText = text;
        if(topCount) topCount.innerText = `${data.total} Outlets Registered`;
    }

    window.deleteBranch = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This branch and its associated records will be permanently removed. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, shut it down!',
            cancelButtonText: 'Keep it open',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'px-4 py-2 rounded-3 fw-bold',
                cancelButton: 'px-4 py-2 rounded-3 fw-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/erp/branches/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    };

    // Export Logic
    document.getElementById('exportExcel').addEventListener('click', () => triggerExport('excel'));
    document.getElementById('exportPdf').addEventListener('click', () => triggerExport('pdf'));

    function triggerExport(type) {
        const selectedColumns = Array.from(document.querySelectorAll('.column-selector:checked')).map(cb => cb.value);
        if (selectedColumns.length === 0) {
            Swal.fire({
                title: 'Data Required',
                text: 'Please select at least one data point to export.',
                icon: 'info',
                confirmButtonColor: 'var(--primary-color)',
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0',
                    confirmButton: 'px-4 py-2 rounded-3 fw-bold'
                }
            });
            return;
        }

        const params = new URLSearchParams();
        params.append('columns', selectedColumns.join(','));
        // Add existing filters
        const formData = new FormData(document.getElementById('filterForm'));
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        window.location.href = `/erp/branches/export-${type}?${params.toString()}`;
    }
    
    document.getElementById('selectAllColumns').addEventListener('click', function(e) {
        e.preventDefault();
        const checks = document.querySelectorAll('.column-selector');
        const allChecked = Array.from(checks).every(c => c.checked);
        checks.forEach(c => c.checked = !allChecked);
        this.innerText = allChecked ? 'Select All Channels' : 'Deselect All Channels';
    });
});
</script>
@endpush
@endsection

