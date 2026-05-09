@extends('erp.master')

@section('title', 'New Fund Transfer')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transfers.index') }}" class="text-decoration-none text-muted">Fund Transfers</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">New Transfer</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-success text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Create Fund Transfer</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('transfers.index') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small">
                                <i class="fas fa-money-bill-transfer me-2 text-primary"></i>Transfer Details
                            </h6>
                        </div>
                        <div class="card-body p-4 p-xl-5">
                            <form action="{{ route('transfers.store') }}" method="POST" id="transferForm">
                                @csrf
                                
                                <!-- From Account -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted text-uppercase small">
                                        <i class="fas fa-sign-out-alt me-2 text-danger"></i>From Account (Source) <span class="text-danger">*</span>
                                    </label>
                                    <select name="from_financial_account_id" id="from_account" class="form-select select2" required>
                                        <option value="">Select Source Account</option>
                                        @foreach($fromAccounts as $account)
                                            @php
                                                $location = '';
                                                if($account->branch_id) $location = $account->branch->name ?? 'Branch';
                                                elseif($account->warehouse_id) $location = $account->warehouse->name ?? 'Warehouse';
                                            @endphp
                                            <option value="{{ $account->id }}" data-balance="{{ $account->balance }}">
                                                {{ $account->provider_name }} - {{ $account->account_number }} 
                                                ({{ $location }}) [Balance: {{ number_format($account->balance, 2) }}৳]
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="from_balance_display" class="form-text text-muted mt-2">
                                        <i class="fas fa-wallet me-1"></i>Available Balance: <span class="fw-bold text-success">Select an account</span>
                                    </div>
                                </div>

                                <!-- Arrow -->
                                <div class="text-center mb-4">
                                    <div class="d-inline-block p-3 rounded-circle bg-light">
                                        <i class="fas fa-arrow-down text-primary fa-2x"></i>
                                    </div>
                                </div>

                                <!-- To Account -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted text-uppercase small">
                                        <i class="fas fa-sign-in-alt me-2 text-success"></i>To Account (Destination) <span class="text-danger">*</span>
                                    </label>
                                    <select name="to_financial_account_id" id="to_account" class="form-select select2" required>
                                        <option value="">Select Destination Account</option>
                                        @foreach($toAccounts as $account)
                                            @php
                                                $location = '';
                                                if($account->branch_id) $location = $account->branch->name ?? 'Branch';
                                                elseif($account->warehouse_id) $location = $account->warehouse->name ?? 'Warehouse';
                                            @endphp
                                            <option value="{{ $account->id }}">
                                                {{ $account->provider_name }} - {{ $account->account_number }} 
                                                ({{ $location }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <hr class="my-4">

                                <!-- Amount -->
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted text-uppercase small">
                                            <i class="fas fa-money-bill me-2 text-warning"></i>Transfer Amount <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="amount" id="amount" class="form-control fw-bold" placeholder="0.00" required min="0.01">
                                            <span class="input-group-text fw-bold">৳</span>
                                        </div>
                                        <div class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>Enter amount to transfer
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted text-uppercase small">
                                            <i class="fas fa-calendar me-2 text-info"></i>Transfer Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <!-- Reference -->
                                <div class="mt-4">
                                    <label class="form-label fw-bold text-muted text-uppercase small">
                                        <i class="fas fa-hashtag me-2"></i>Reference / Cheque No
                                    </label>
                                    <input type="text" name="reference" class="form-control" placeholder="Optional reference number">
                                </div>

                                <!-- Memo -->
                                <div class="mt-4">
                                    <label class="form-label fw-bold text-muted text-uppercase small">
                                        <i class="fas fa-sticky-note me-2"></i>Memo / Notes
                                    </label>
                                    <textarea name="memo" class="form-control" rows="2" placeholder="Optional notes about this transfer"></textarea>
                                </div>

                                <!-- Submit -->
                                <div class="mt-5 text-center">
                                    <button type="submit" class="btn btn-create-premium px-5 py-3 rounded-pill fw-bold shadow-lg" id="submitBtn">
                                        <i class="fas fa-check-circle me-2"></i>CONFIRM & TRANSFER
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select an option',
                allowClear: true
            });

            // Update balance display when from account changes
            $('#from_account').on('change', function() {
                const selected = $(this).find('option:selected');
                const balance = selected.data('balance');
                
                if (balance !== undefined) {
                    $('#from_balance_display').html(
                        '<i class="fas fa-wallet me-1"></i>Available Balance: <span class="fw-bold text-success">' + 
                        parseFloat(balance).toFixed(2) + '৳</span>'
                    );
                    $('#amount').attr('max', balance);
                } else {
                    $('#from_balance_display').html(
                        '<i class="fas fa-wallet me-1"></i>Available Balance: <span class="fw-bold text-muted">Select an account</span>'
                    );
                }
            });

            // Validate different accounts
            $('#transferForm').on('submit', function(e) {
                const fromAccount = $('#from_account').val();
                const toAccount = $('#to_account').val();
                
                if (fromAccount === toAccount) {
                    e.preventDefault();
                    alert('Source and destination accounts cannot be the same!');
                    return false;
                }

                const amount = parseFloat($('#amount').val()) || 0;
                const maxBalance = parseFloat($('#from_account option:selected').data('balance')) || 0;
                
                if (amount > maxBalance) {
                    e.preventDefault();
                    alert('Insufficient balance! Available: ' + maxBalance.toFixed(2) + '৳');
                    return false;
                }
            });
        });
    </script>
@endpush
