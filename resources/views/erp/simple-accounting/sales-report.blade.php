@extends('erp.master')

@section('title', 'Sales Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Sales Report</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#salesReportModal">
                        <i class="fas fa-file-export me-1"></i> Generate Report
                    </button>
                    <select id="dateRange" class="form-select form-select-sm" style="width: auto;" onchange="updateDateRange()">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>Last Quarter</option>
                        <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>Last Year</option>
                    </select>
                </div>
            </div>

            <!-- Date Range Info -->
            <div class="alert alert-info py-2 px-3 mb-4">
                <i class="fas fa-info-circle me-1"></i>
                Showing data from <strong>{{ $startDate->format('M d, Y') }}</strong> to <strong>{{ $endDate->format('M d, Y') }}</strong>
            </div>

            <!-- Main Tabs -->
            <ul class="nav nav-tabs mb-4 border-0" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="online-tabs-btn" data-bs-toggle="tab" data-bs-target="#onlineContent" type="button" role="tab">
                        <i class="fas fa-globe me-2"></i> Online Sales
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="pos-tabs-btn" data-bs-toggle="tab" data-bs-target="#posContent" type="button" role="tab">
                        <i class="fas fa-cash-register me-2"></i> POS Sales
                    </button>
                </li>
            </ul>

            <div class="tab-content border-0">
                <!-- Online Sales Content -->
                <div class="tab-pane fade show active" id="onlineContent" role="tabpanel">
                    <div class="card card-custom border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active btn-sm py-1 px-3 me-2" data-bs-toggle="pill" data-bs-target="#onlineProductTab">Product Wise</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#onlineCategoryTab">Category Wise</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content">
                                <!-- Online Product Wise -->
                                <div class="tab-pane fade show active" id="onlineProductTab">
                                    @include('erp.simple-accounting.components.sales-table', ['data' => $onlineProductProfits, 'type' => 'product'])
                                </div>
                                <!-- Online Category Wise -->
                                <div class="tab-pane fade" id="onlineCategoryTab">
                                    @include('erp.simple-accounting.components.sales-table', ['data' => $onlineCategoryProfits, 'type' => 'category'])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POS Sales Content -->
                <div class="tab-pane fade" id="posContent" role="tabpanel">
                    <div class="card card-custom border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active btn-sm py-1 px-3 me-2" data-bs-toggle="pill" data-bs-target="#posProductTab">Product Wise</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link btn-sm py-1 px-3" data-bs-toggle="pill" data-bs-target="#posCategoryTab">Category Wise</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content">
                                <!-- POS Product Wise -->
                                <div class="tab-pane fade show active" id="posProductTab">
                                    @include('erp.simple-accounting.components.sales-table', ['data' => $posProductProfits, 'type' => 'product'])
                                </div>
                                <!-- POS Category Wise -->
                                <div class="tab-pane fade" id="posCategoryTab">
                                    @include('erp.simple-accounting.components.sales-table', ['data' => $posCategoryProfits, 'type' => 'category'])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('erp.simple-accounting.components.report-modal')

    @push('scripts')
    <script>
    function updateDateRange() {
        const range = document.getElementById('dateRange').value;
        const url = new URL(window.location);
        url.searchParams.set('range', range);
        window.location.href = url.toString();
    }
    </script>
    <style>
        #reportTabs {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1.5rem !important;
            gap: 10px;
        }
        #reportTabs .nav-link {
            color: #4b5563 !important;
            border: 1px solid transparent !important;
            border-bottom: none !important;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
            background: #f9fafb !important;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 8px 8px 0 0 !important;
            margin-bottom: -1px;
        }
        #reportTabs .nav-link.active {
            color: var(--primary-color) !important;
            border: 1px solid #dee2e6 !important;
            border-bottom: 1px solid #fff !important;
            background: #fff !important;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.02);
        }
        #reportTabs i {
            font-size: 1.1rem;
            opacity: 0.8;
        }
        #reportTabs .nav-link.active i {
            color: var(--primary-color);
            opacity: 1;
        }
        
        /* Nav Pills Styling */
        .nav-pills .nav-link {
            color: #64748b;
            background: #f1f5f9;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .nav-pills .nav-link.active {
            background: var(--primary-color) !important;
            color: white !important;
        }

        /* Table Styling */
        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            padding: 1rem;
            background: #f8fafc;
        }
        .table tbody td {
            padding: 1rem;
            color: #334155;
        }
        .text-primary { color: var(--primary-color) !important; }
    </style>
    @endpush
@endsection
