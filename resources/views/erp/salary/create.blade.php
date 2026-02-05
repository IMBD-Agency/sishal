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
                                <label class="form-label fw-bold small text-primary">Amount Paid *</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control border-primary" required placeholder="0.00">
                                    <span class="input-group-text bg-primary text-white border-primary">৳</span>
                                </div>
                                <div id="due_hint" class="small text-danger mt-1 fw-bold"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Account Type *</label>
                                <select name="payment_method" class="form-select select2-premium-42" required>
                                    <option value="">Select One</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                    <option value="Mobile Banking">Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Account No *</label>
                                <select name="account_id" class="form-select select2-premium-42" required>
                                    <option value="">Select One</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
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
                            if(res.due > 0) {
                                $('#due_hint').text('Remaining Due: ' + res.due + '৳');
                            } else {
                                $('#due_hint').text('Fully Paid for this month');
                            }
                        }
                    });
                }
            }

            $('#employee_id, #month, #year').on('change', fetchSalaryDetails);
            
            $('#salaryForm').on('submit', function(e) {
                let paid = parseFloat($('#paid_amount').val());
                let due = parseFloat($('#total_salary').val()) - parseFloat($('#previous_paid').val());
                
                if(paid > due + 0.01) { // 0.01 buffer for float issues
                    if(!confirm('Paid amount exceeds the current due. Do you want to continue?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
    @endpush
@endsection
