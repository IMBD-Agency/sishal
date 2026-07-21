@extends('erp.master')

@section('title', 'Staff Payment Entry')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Staff Payment</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('salary.index') }}" class="text-decoration-none">List</a></li>
                                <li class="breadcrumb-item active">New Payment</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('salary.store') }}" method="POST" id="salaryForm">
                        @csrf
                        <div class="row g-4">
                            <!-- Basic Selectors -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Select Month *</label>
                                <select name="month" id="month" class="form-select select2-premium-42" required>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                                        <option value="{{ $m }}" {{ date('F') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Select Year *</label>
                                <select name="year" id="year" class="form-select select2-premium-42" required>
                                    @for($y = date('Y')-1; $y <= date('Y')+1; $y++)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Select Staff *</label>
                                <select name="employee_id" id="employee_id" class="form-select select2-premium-42" required>
                                    <option value="">Select One</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->user->first_name }} {{ $emp->user->last_name }} ({{ $emp->phone }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Details (Calculated) -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Staff Salary *</label>
                                <div class="input-group">
                                    <input type="text" name="total_salary" id="total_salary" class="form-control bg-light" readonly placeholder="0.00">
                                    <span class="input-group-text">৳</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Previous Payment *</label>
                                <div class="input-group">
                                    <input type="text" id="previous_paid" class="form-control bg-light" readonly placeholder="0.00">
                                    <span class="input-group-text">৳</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-primary">Salary Paid Amount (Main)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control border-primary" value="0" placeholder="0.00">
                                    <span class="input-group-text bg-primary text-white border-primary">৳</span>
                                </div>
                                <div id="due_hint" class="small text-danger mt-1 fw-bold"></div>
                            </div>

                            <!-- Bonus Section -->
                            <div class="col-12 mt-3">
                                <div class="card border-success">
                                    <div class="card-header bg-success bg-opacity-10 py-2">
                                        <h6 class="mb-0 text-success fw-bold">
                                            <i class="fas fa-gift me-2"></i>Bonus Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small">Target Bonus</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>
                                                    <input type="number" step="0.01" name="bonus_amount" id="bonus_amount" class="form-control" value="0" placeholder="0.00">
                                                </div>
                                                <div id="bonus_info" class="small text-info mt-1"></div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="is_bonus_editable" id="is_bonus_editable" checked>
                                                    <label class="form-check-label small" for="is_bonus_editable">
                                                        Editable Target Bonus
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small text-warning">Festival Bonus %</label>
                                                <select id="festival_bonus_percentage" class="form-select border-warning">
                                                    <option value="0">0% (None)</option>
                                                    <option value="25">25%</option>
                                                    <option value="50">50% (Half Salary)</option>
                                                    <option value="75">75%</option>
                                                    <option value="100">100% (Full Salary)</option>
                                                </select>
                                                <div class="small text-warning mt-1">Select percentage</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small text-warning">Festival Bonus Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-warning text-dark border-warning">৳</span>
                                                    <input type="number" step="0.01" name="festival_bonus_amount" id="festival_bonus_amount" class="form-control border-warning" value="0" placeholder="0.00">
                                                </div>
                                                <div class="small text-muted mt-1">Or edit manually</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small">Total Payment</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white">৳</span>
                                                    <input type="text" id="total_payment" class="form-control bg-light" readonly placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Account Type *</label>
                                <select name="payment_method" id="accountTypeSelect" class="form-select select2-premium-42" required>
                                    <option value="">Select One</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                    <option value="Mobile Banking">Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Account No *</label>
                                <select name="account_id" id="accountIdSelect" class="form-select select2-premium-42" required>
                                    <option value="">Select One</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                            {{ $acc->provider_name }} ({{ $acc->account_number }}) - {{ $acc->chartOfAccount->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Note</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="If have any Note"></textarea>
                            </div>
                        </div>

                        <div class="mt-5 d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fas fa-save me-2"></i>Submit
                            </button>
                            <a href="{{ route('salary.index') }}" class="btn btn-danger px-5 py-2 fw-bold">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            function fetchSalaryDetails() {
                let empId = $('#employee_id').val();
                let month = $('#month').val();
                let year = $('#year').val();

                if(empId && month && year) {
                    $.ajax({
                        url: "{{ route('salary.details') }}",
                        method: 'GET',
                        data: { employee_id: empId, month: month, year: year },
                        success: function(res) {
                            $('#total_salary').val(res.salary);
                            $('#previous_paid').val(res.previous_paid);
                            $('#paid_amount').attr('max', res.due);
                            
                            // Handle bonus information
                            if (res.has_bonus && res.bonus_amount > 0) {
                                $('#bonus_amount').val(res.bonus_amount);
                                $('#bonus_info').html('<i class="fas fa-info-circle"></i> Auto-calculated bonus from sales target');
                                if (!$('#is_bonus_editable').is(':checked')) {
                                    $('#bonus_amount').prop('readonly', true);
                                }
                            } else if (res.bonus_data && res.bonus_data.has_target) {
                                $('#bonus_info').html('<i class="fas fa-exclamation-triangle"></i> Target not achieved yet');
                            } else {
                                $('#bonus_info').html('<i class="fas fa-info-circle"></i> No sales target for this period');
                            }
                            
                            if(res.due > 0) {
                                $('#due_hint').text('Remaining Due: ' + res.due + '৳');
                            } else {
                                $('#due_hint').text('Fully Paid for this month');
                            }
                            
                            calculateTotal();
                        }
                    });
                }
            }

            $('#employee_id, #month, #year').on('change', fetchSalaryDetails);
            
            // Bonus calculation handlers
            $('#bonus_amount, #paid_amount, #festival_bonus_amount').on('input', calculateTotal);
            
            $('#festival_bonus_percentage').on('change', function() {
                let percentage = parseFloat($(this).val()) || 0;
                let baseSalary = parseFloat($('#total_salary').val()) || 0;
                
                if (percentage > 0 && baseSalary > 0) {
                    let festivalBonus = (baseSalary * percentage) / 100;
                    $('#festival_bonus_amount').val(festivalBonus.toFixed(2));
                } else {
                    $('#festival_bonus_amount').val(0);
                }
                calculateTotal();
            });

            $('#is_bonus_editable').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bonus_amount').prop('readonly', false);
                } else {
                    $('#bonus_amount').prop('readonly', true);
                }
            });
            
            function calculateTotal() {
                let paidAmount = parseFloat($('#paid_amount').val()) || 0;
                let bonusAmount = parseFloat($('#bonus_amount').val()) || 0;
                let festivalBonusAmount = parseFloat($('#festival_bonus_amount').val()) || 0;
                let total = paidAmount + bonusAmount + festivalBonusAmount;
                $('#total_payment').val(total.toFixed(2));
            }
            
            $('#salaryForm').on('submit', function(e) {
                let paid = parseFloat($('#paid_amount').val()) || 0;
                let bonus = parseFloat($('#bonus_amount').val()) || 0;
                let festBonus = parseFloat($('#festival_bonus_amount').val()) || 0;
                let totalPaid = paid + bonus + festBonus;

                if (totalPaid <= 0) {
                    alert('Please enter an amount for Salary Paid, Target Bonus, or Festival Bonus.');
                    e.preventDefault();
                    return false;
                }

                let due = parseFloat($('#total_salary').val()) - parseFloat($('#previous_paid').val());
                if (paid > due + 0.01) { // 0.01 buffer for float issues
                    if (!confirm('Main salary paid amount exceeds current salary due. Do you want to continue?')) {
                        e.preventDefault();
                    }
                }
            });

            // Robust Select2 / Dropdown Filtering Logic
            const $accountSelect = $('#accountIdSelect');
            const allAccountOptions = $accountSelect.find('option').clone(); // Backup all options

            $('#accountTypeSelect').on('change', function() {
                const method = $(this).val();
                
                // Clear the current select
                $accountSelect.empty();
                
                // Add the placeholder back
                $accountSelect.append('<option value="">Select One</option>');

                allAccountOptions.each(function() {
                    const $opt = $(this);
                    const type = $opt.data('type');
                    const value = $opt.val();

                    if (!value) return; // Skip original placeholder

                    let shouldShow = false;
                    if (!method) {
                        shouldShow = true;
                    } else if (method === 'Cash' && type === 'cash') {
                        shouldShow = true;
                    } else if (method === 'Bank' && type === 'bank') {
                        shouldShow = true;
                    } else if (method === 'Mobile Banking' && type === 'mobile') {
                        shouldShow = true;
                    }

                    if (shouldShow) {
                        $accountSelect.append($opt.clone());
                    }
                });

                // Refresh Select2 to show new options
                $accountSelect.val('').trigger('change.select2').trigger('change');
            });
        });
    </script>
    @endpush
@endsection
