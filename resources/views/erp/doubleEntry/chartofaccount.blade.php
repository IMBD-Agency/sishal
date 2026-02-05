@extends('erp.master')

@section('title', 'Chart of Account Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-3 bg-white border-bottom mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-0">Chart of Account</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="{{ route('erp.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item small active">Accounting</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addParentModal">
                        <i class="fas fa-folder-plus me-2"></i>Add Parent Account
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                        <i class="fas fa-plus me-2"></i>Add Chart Account
                    </button>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 pb-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Account Summary Cards -->
            <div class="row g-3 mb-4">
                @php
                    $icons = [
                        'Asset' => 'fa-vault', 'Assets' => 'fa-vault',
                        'Liability' => 'fa-hand-holding-dollar', 'Liabilities' => 'fa-hand-holding-dollar',
                        'Income' => 'fa-chart-line', 'Revenue' => 'fa-chart-line',
                        'Expense' => 'fa-money-bill-transfer', 'Expenses' => 'fa-money-bill-transfer',
                        'Equity' => 'fa-scale-balanced'
                    ];
                    $colors = [
                        'Asset' => 'bg-primary', 'Assets' => 'bg-primary',
                        'Liability' => 'bg-danger', 'Liabilities' => 'bg-danger',
                        'Income' => 'bg-success', 'Revenue' => 'bg-success',
                        'Expense' => 'bg-warning', 'Expenses' => 'bg-warning',
                        'Equity' => 'bg-info'
                    ];
                @endphp
                @foreach($accountTypes as $type)
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm h-100 overflow-hidden">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="p-2 rounded {{ $colors[$type->name] ?? 'bg-secondary' }} text-white me-2">
                                        <i class="fas {{ $icons[$type->name] ?? 'fa-circle' }} fa-fw"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold">{{ $type->name }}</h6>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">{{ $type->subTypes->count() }} Sub-types</span>
                                    <span class="badge bg-light text-dark border">{{ $accountParents->where('type_id', $type->id)->count() }} Parents</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Main Workspace -->
            <div class="row g-4">
                <!-- Parent Accounts Column -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-teal text-white py-3">
                            <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Account Hierarchy (Parents)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0 p-3">
                                @forelse($accountParents as $parent)
                                    <div class="col-md-4 p-2">
                                        <div class="border rounded p-3 bg-white position-relative hover-shadow transition">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <span class="badge bg-light text-dark mb-2 border">Code: {{ $parent->code }}</span>
                                                    <h6 class="fw-bold mb-1">{{ $parent->name }}</h6>
                                                    <small class="text-muted d-block"><i class="fas fa-tag me-1 small"></i>{{ $parent->type->name ?? 'N/A' }}</small>
                                                </div>
                                                <div class="btn-group btn-group-sm h-100">
                                                    <button class="btn btn-link text-primary p-1" onclick="editParent({{ $parent->id }}, '{{ $parent->name }}', '{{ $parent->code }}', '{{ $parent->description }}', {{ $parent->type_id }}, {{ $parent->sub_type_id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-link text-danger p-1" onclick="deleteParent({{ $parent->id }}, '{{ $parent->name }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <hr class="my-2 opacity-50">
                                            <div class="d-flex justify-content-between align-items-center small">
                                                <span class="text-muted">{{ $parent->accounts->count() }} Child Accounts</span>
                                                <span class="badge {{ $colors[$parent->type->name ?? ''] ?? 'bg-secondary' }} bg-opacity-10 {{ str_replace('bg-', 'text-', $colors[$parent->type->name ?? ''] ?? 'text-secondary') }}">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No parent accounts found. Add one to start organizing.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Chart of Accounts Table -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Detailed Chart of Accounts</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="coaTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Code</th>
                                            <th>Account Name</th>
                                            <th>Parent</th>
                                            <th>Type</th>
                                            <th>Sub-Type</th>
                                            <th>Created By</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($chartOfAccounts as $account)
                                            <tr>
                                                <td class="ps-4"><span class="fw-bold text-primary">{{ $account->code }}</span></td>
                                                <td><span class="fw-bold">{{ $account->name }}</span></td>
                                                <td><span class="badge bg-light text-dark border">{{ $account->parent->name ?? 'None' }}</span></td>
                                                <td><span class="badge bg-opacity-10 {{ str_replace('bg-', 'text-', $colors[$account->type->name ?? ''] ?? 'text-secondary') }} {{ $colors[$account->type->name ?? ''] ?? 'bg-secondary' }} px-3">{{ $account->type->name ?? 'N/A' }}</span></td>
                                                <td><small class="text-muted">{{ $account->subType->name ?? 'N/A' }}</small></td>
                                                <td><small>{{ $account->createdBy->first_name ?? 'System' }}</small></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('ledger.account', $account->id) }}" class="btn btn-outline-info" title="View Ledger">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button class="btn btn-outline-primary" onclick="editAccount({{ $account->id }}, '{{ $account->name }}', '{{ $account->code }}', '{{ $account->description }}', {{ $account->parent_id }}, {{ $account->type_id }}, {{ $account->sub_type_id }})">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteAccount({{ $account->id }}, '{{ $account->name }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
            </div>
        </div>
    </div>

    <!-- Modals (Restored and Refined) -->
    @include('erp.doubleEntry.modals.coa_modals')

    <style>
        .bg-teal { background-color: #2a8a91 !important; }
        .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; transform: translateY(-3px); }
        .transition { transition: all 0.3s ease; }
        .table thead th { font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    </style>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Load sub-types dynamically
            $('#parent_type_id, #account_type_id').on('change', function() {
                var typeId = $(this).val();
                var isParent = $(this).attr('id') === 'parent_type_id';
                var $subTypeSelect = isParent ? $('#parent_sub_type_id') : $('#account_sub_type_id');
                
                $subTypeSelect.empty().append('<option value="">Loading...</option>').prop('disabled', true);
                
                if (typeId) {
                    $.ajax({
                        url: '{{ url("erp/double-entry/get-sub-types") }}/' + typeId,
                        type: 'GET',
                        success: function(data) {
                            $subTypeSelect.empty().append('<option value="">Select Sub Type</option>');
                            $.each(data, function(key, value) {
                                $subTypeSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        },
                        complete: function() { $subTypeSelect.prop('disabled', false); }
                    });
                }
            });
        });

        function editParent(id, name, code, desc, typeId, subId) {
            $('#parent_name').val(name); $('#parent_code').val(code); $('#parent_description').val(desc); $('#parent_type_id').val(typeId).trigger('change');
            setTimeout(() => { $('#parent_sub_type_id').val(subId); }, 500);
            $('#parentForm').attr('action', '{{ url("erp/double-entry/chart-of-account-parents") }}/' + id).append('<input type="hidden" name="_method" value="PUT">');
            $('#addParentModal').modal('show');
        }

        function editAccount(id, name, code, desc, parentId, typeId, subId) {
            $('#account_name').val(name); $('#account_code').val(code); $('#account_description').val(desc); 
            $('#account_parent_id').val(parentId); $('#account_type_id').val(typeId).trigger('change');
            setTimeout(() => { $('#account_sub_type_id').val(subId); }, 500);
            $('#accountForm').attr('action', '{{ url("erp/double-entry/chart-of-accounts") }}/' + id).append('<input type="hidden" name="_method" value="PUT">');
            $('#addAccountModal').modal('show');
        }

        function deleteParent(id, name) {
            if (confirm('Delete parent account "' + name + '"?')) {
                $.ajax({
                    url: '{{ url("erp/double-entry/chart-of-account-parents") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) { if(r.success) location.reload(); else alert(r.message); }
                });
            }
        }

        function deleteAccount(id, name) {
            if (confirm('Delete chart account "' + name + '"?')) {
                $.ajax({
                    url: '{{ url("erp/double-entry/chart-of-accounts") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) { if(r.success) location.reload(); else alert(r.message); }
                });
            }
        }
    </script>
    @endpush
@endsection