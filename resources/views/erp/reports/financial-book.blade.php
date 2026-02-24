@extends('erp.master')

@section('title', $title)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $title }}</h4>
                    <p class="text-muted small mb-0">{{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                            <li><a class="dropdown-item py-2" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">PDF Version</a></li>
                            <li><a class="dropdown-item py-2" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4 no-print">
                <div class="card-body p-3">
                    <form method="GET" action="{{ url()->current() }}" id="filterForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Report Period</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="daily" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }} onclick="setDateRange('daily')">
                                        <label class="form-check-label small" for="daily">Daily</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="monthly" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }} onclick="setDateRange('monthly')">
                                        <label class="form-check-label small" for="monthly">Monthly</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="yearly" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }} onclick="setDateRange('yearly')">
                                        <label class="form-check-label small" for="yearly">Yearly</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Branch</label>
                                <select name="branch_id" class="form-select form-select-sm">
                                    <option value="">Consolidated View</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ ($branchId ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-dark btn-sm w-100">Analyze</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Table -->
            <div class="card border shadow-sm">
                <div class="card-body p-4">
                    <!-- Report Header (Print only style) -->
                    <div class="text-center mb-4">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                             @php 
                                $logoUrl = $general_settings && $general_settings->site_logo 
                                    ? asset($general_settings->site_logo) 
                                    : asset('static/default-logo.webp'); 
                            @endphp
                            <img src="{{ $logoUrl }}" alt="Logo" style="height: 70px;" onerror="this.src='https://via.placeholder.com/70'">
                        </div>
                        <h3 class="fw-bold mb-1">{{ $general_settings->site_title ?? 'Sisal Fashion' }}</h3>
                        <p class="mb-0 text-muted small">Purbahati, Natun Para, Hemayetpur, Savar, Dhaka, Bangladesh</p>
                        <p class="mb-0 text-muted small">Email: info@sisalfashion.com | Mobile: +8801312809597</p>
                        <h4 class="fw-bold mt-3 text-uppercase" style="letter-spacing: 2px;">{{ $title }}</h4>
                        @if($branchId)
                            <p class="mb-0 fw-bold border-bottom d-inline-block pb-1">Branch: {{ $branches->find($branchId)->name ?? 'Selected Branch' }}</p>
                        @else
                            <p class="mb-0 fw-bold border-bottom d-inline-block pb-1">Consolidated View (All Branches)</p>
                        @endif
                        <p class="text-dark small mt-2">Date: {{ $startDate->format('d-m-Y') }} @if($startDate->toDateString() != $endDate->toDateString()) to {{ $endDate->format('d-m-Y') }} @endif</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-dark text-white text-center small">
                                <tr>
                                    <th style="width: 50px;">#SN.</th>
                                    @if($title == 'Bank Book')
                                        <th>Bank Name</th>
                                    @endif
                                    @if($title == 'Mobile Book')
                                        <th>Account No.</th>
                                    @endif
                                    <th>Account Name</th>
                                    <th>Branch/Location</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th class="text-end">Current Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $index => $account)
                                <tr>
                                    <td class="text-center small">{{ $index + 1 }}</td>
                                    @if($title == 'Bank Book')
                                        <td>{{ $account->provider_name ?? '-' }}</td>
                                    @endif
                                    @if($title == 'Mobile Book')
                                        <td>{{ $account->mobile_number ?? '-' }}</td>
                                    @endif
                                    <td>
                                        <div class="fw-bold text-dark">{{ $account->account_holder_name ?? $account->chartOfAccount->name }}</div>
                                        @if($title == 'Bank Book')
                                            <div class="text-muted small">A/C: {{ $account->account_number }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $account->branch_name ?? '-' }}</td>
                                    <td class="text-end fw-bold">৳ {{ number_format($account->opening, 2) }}</td>
                                    <td class="text-end text-success">৳ {{ number_format($account->debit, 2) }}</td>
                                    <td class="text-end text-danger">৳ {{ number_format($account->credit, 2) }}</td>
                                    <td class="text-end fw-bold {{ $account->closing >= 0 ? 'text-primary' : 'text-danger' }}">
                                        ৳ {{ number_format($account->closing, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No accounts available in this category.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($accounts->isNotEmpty())
                            <tfoot class="bg-light fw-bold text-dark">
                                <tr>
                                    @php
                                        $cols = 3;
                                        if($title == 'Bank Book') $cols = 4;
                                        if($title == 'Mobile Book') $cols = 4;
                                    @endphp
                                    <td colspan="{{ $cols }}" class="text-end py-3">GRAND TOTAL</td>
                                    <td class="text-end">৳ {{ number_format($accounts->sum('opening'), 2) }}</td>
                                    <td class="text-end text-success">৳ {{ number_format($accounts->sum('debit'), 2) }}</td>
                                    <td class="text-end text-danger">৳ {{ number_format($accounts->sum('credit'), 2) }}</td>
                                    <td class="text-end text-primary">৳ {{ number_format($accounts->sum('closing'), 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <div class="text-center mt-4 no-print">
                        <button class="btn btn-primary px-4" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print, .sidebar-wrapper, .header-container, .main-header { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; }
            .container-fluid { max-width: 100% !important; border: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .table thead th { background-color: #333 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .table-bordered th, .table-bordered td { border: 1px solid #ddd !important; }
        }
        .bg-dark { background-color: #2d3436 !important; }
    </style>

    <script>
        function setDateRange(type) {
            const today = new Date();
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            
            const formatDate = (date) => {
                let d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            }

            if (type === 'daily') {
                startInput.value = formatDate(today);
                endInput.value = formatDate(today);
            } else if (type === 'monthly') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                startInput.value = formatDate(firstDay);
                endInput.value = formatDate(lastDay);
            } else if (type === 'yearly') {
                const firstDay = new Date(today.getFullYear(), 0, 1);
                const lastDay = new Date(today.getFullYear(), 11, 31);
                startInput.value = formatDate(firstDay);
                endInput.value = formatDate(lastDay);
            }
        }
    </script>
@endsection
