@extends('erp.master')

@section('title', 'Financial Accounts')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-0"><i class="fas fa-university me-2 text-primary"></i>Financial Accounts</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Financial Accounts</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                        <i class="fas fa-plus me-2"></i>Add Account
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
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="p-3 rounded-3 bg-primary bg-opacity-10">
                                <i class="fas fa-wallet fa-lg text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Accounts</div>
                                <div class="fw-bold fs-4">{{ $accounts->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="p-3 rounded-3 bg-success bg-opacity-10">
                                <i class="fas fa-money-bill fa-lg text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Cash Accounts</div>
                                <div class="fw-bold fs-4">{{ $accounts->where('type', 'cash')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="p-3 rounded-3 bg-info bg-opacity-10">
                                <i class="fas fa-university fa-lg text-info"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Bank Accounts</div>
                                <div class="fw-bold fs-4">{{ $accounts->where('type', 'bank')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="p-3 rounded-3 bg-warning bg-opacity-10">
                                <i class="fas fa-mobile-alt fa-lg text-warning"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Mobile Accounts</div>
                                <div class="fw-bold fs-4">{{ $accounts->where('type', 'mobile')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounts Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Financial Accounts</h5>
                    <span class="badge bg-light text-dark">{{ $accounts->count() }} accounts</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Type</th>
                                    <th>Provider / Name</th>
                                    <th>Account Number</th>
                                    <th>Holder</th>
                                    <th>Currency</th>
                                    <th>Details</th>
                                    <th>Chart Account</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $index => $account)
                                    <tr>
                                        <td class="ps-4">{{ $index + 1 }}</td>
                                        <td>
                                            @if($account->type === 'cash')
                                                <span class="badge bg-success"><i class="fas fa-money-bill me-1"></i>Cash</span>
                                            @elseif($account->type === 'bank')
                                                <span class="badge bg-primary"><i class="fas fa-university me-1"></i>Bank</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="fas fa-mobile-alt me-1"></i>Mobile</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $account->provider_name }}</strong></td>
                                        <td><span class="badge bg-secondary font-monospace">{{ $account->account_number }}</span></td>
                                        <td>{{ $account->account_holder_name ?? '—' }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $account->currency }}</span></td>
                                        <td class="small text-muted">
                                            @if($account->type === 'bank')
                                                @if($account->branch_name) <div>Branch: {{ $account->branch_name }}</div> @endif
                                                @if($account->swift_code) <div>Swift: {{ $account->swift_code }}</div> @endif
                                            @elseif($account->type === 'mobile')
                                                @if($account->mobile_number) <div>Mobile: {{ $account->mobile_number }}</div> @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if($account->chartOfAccount)
                                                <span class="badge bg-light text-dark border">{{ $account->chartOfAccount->name }}</span>
                                            @else
                                                <span class="text-muted">Not linked</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="Edit"
                                                    onclick="editAccount({{ $account->id }}, '{{ $account->type }}', '{{ addslashes($account->provider_name) }}', '{{ $account->account_number }}', '{{ addslashes($account->account_holder_name) }}', '{{ $account->currency }}', '{{ addslashes($account->branch_name) }}', '{{ $account->swift_code }}', '{{ $account->mobile_number }}', {{ $account->account_id ?? 'null' }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" title="Delete"
                                                    onclick="deleteAccount({{ $account->id }}, '{{ addslashes($account->provider_name) }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-university fa-3x mb-3 d-block opacity-25"></i>
                                            <h6>No Financial Accounts Found</h6>
                                            <p class="small">Click "Add Account" to create your first account.</p>
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

    <!-- Add / Edit Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalTitle"><i class="fas fa-plus me-2"></i>Add Financial Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="accountForm" action="{{ route('financial-accounts.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    @foreach($accountTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Provider Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Provider / Account Name <span class="text-danger">*</span></label>
                                <input type="text" name="provider_name" id="provider_name" class="form-control" placeholder="e.g. Dutch Bangla Bank, bKash, Main Cash" required>
                            </div>
                            <!-- Account Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Number <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" id="account_number" class="form-control" placeholder="Account / wallet number" required>
                            </div>
                            <!-- Holder -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Holder Name</label>
                                <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" placeholder="Name of the account holder">
                            </div>
                            <!-- Currency -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                                <select name="currency" id="currency" class="form-select" required>
                                    <option value="BDT" selected>BDT (Bangladeshi Taka)</option>
                                    <option value="USD">USD (US Dollar)</option>
                                    <option value="EUR">EUR (Euro)</option>
                                    <option value="GBP">GBP (British Pound)</option>
                                </select>
                            </div>
                            <!-- Chart of Account Link -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Link to Chart of Account</label>
                                <select name="account_id" id="account_id" class="form-select">
                                    <option value="">— Not linked —</option>
                                    @foreach($chartAccounts as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->name }} ({{ $coa->code }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Optional: link to your double-entry ledger.</div>
                            </div>

                            <!-- Bank-specific fields -->
                            <div id="bankFields" class="col-12 d-none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Branch Name</label>
                                        <input type="text" name="branch_name" id="branch_name" class="form-control" placeholder="e.g. Dhanmondi Branch">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Swift Code</label>
                                        <input type="text" name="swift_code" id="swift_code" class="form-control" placeholder="e.g. DBBLBDDH">
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile-specific fields -->
                            <div id="mobileFields" class="col-12 d-none">
                                <label class="form-label fw-semibold">Mobile Number</label>
                                <input type="text" name="mobile_number" id="mobile_number" class="form-control" placeholder="e.g. 01712345678">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><span id="submitBtnText">Save Account</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Show/hide conditional fields based on account type
        function toggleTypeFields(type) {
            document.getElementById('bankFields').classList.toggle('d-none', type !== 'bank');
            document.getElementById('mobileFields').classList.toggle('d-none', type !== 'mobile');
        }

        document.getElementById('type').addEventListener('change', function () {
            toggleTypeFields(this.value);
        });

        // Reset modal on close
        document.getElementById('addAccountModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('accountForm').reset();
            document.getElementById('accountForm').action = '{{ route("financial-accounts.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add Financial Account';
            document.getElementById('submitBtnText').textContent = 'Save Account';
            document.getElementById('bankFields').classList.add('d-none');
            document.getElementById('mobileFields').classList.add('d-none');
        });

        function editAccount(id, type, providerName, accountNumber, holderName, currency, branchName, swiftCode, mobileNumber, accountId) {
            document.getElementById('type').value = type;
            document.getElementById('provider_name').value = providerName;
            document.getElementById('account_number').value = accountNumber;
            document.getElementById('account_holder_name').value = holderName;
            document.getElementById('currency').value = currency;
            document.getElementById('branch_name').value = branchName;
            document.getElementById('swift_code').value = swiftCode;
            document.getElementById('mobile_number').value = mobileNumber;
            if (accountId) document.getElementById('account_id').value = accountId;

            toggleTypeFields(type);

            document.getElementById('accountForm').action = '{{ url("erp/financial-accounts") }}/' + id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Financial Account';
            document.getElementById('submitBtnText').textContent = 'Update Account';

            new bootstrap.Modal(document.getElementById('addAccountModal')).show();
        }

        function deleteAccount(id, name) {
            if (confirm('Delete account "' + name + '"? This cannot be undone.')) {
                fetch('{{ url("erp/financial-accounts") }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(data => {
                    if (data.success) location.reload();
                    else alert(data.message);
                });
            }
        }
    </script>
    @endpush
@endsection