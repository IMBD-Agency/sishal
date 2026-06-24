@extends('erp.master')
@section('title', 'Product Adjustment List')

@section('body')
    @include('erp.components.sidebar')

    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <style>
            .premium-card {
                border: none;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                background: #fff;
                margin-bottom: 1.5rem;
                overflow: hidden;
                border: 1px solid #edf2f7;
            }

            .glass-header {
                position: relative !important;
                top: 0 !important;
                box-shadow: none !important;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
                margin-bottom: 1rem !important;
            }

            .table-responsive {
                max-height: 80vh;
                overflow: auto !important;
                position: relative;
                background: #fff;
            }

            #adjustmentTable {
                border-collapse: separate;
                border-spacing: 0;
                width: 100%;
            }

            #adjustmentTable thead th {
                position: sticky !important;
                top: 0 !important;
                z-index: 100 !important;
                background-color: #f8fafc !important;
                color: #64748b !important;
                text-transform: uppercase;
                font-size: 0.75rem;
                font-weight: 700;
                padding: 16px 20px !important;
                border: none !important;
                letter-spacing: 0.5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            #adjustmentTable tbody td {
                padding: 16px 20px !important;
                border-bottom: 1px solid #f1f5f9 !important;
                vertical-align: middle !important;
                background: #fff !important;
                font-size: 0.85rem;
            }

            .main-inventory-container {
                padding: 0 2rem;
            }
        </style>

        <div class="main-inventory-container">
            <!-- Top Header -->
            <div class="glass-header px-5 py-3 bg-white border-bottom mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold mb-0 text-dark">Product Adjustment History</h4>
                    </div>
                    <div class="col-md-6 text-md-end">
                        @can('adjust stock')

                            <a href="{{ route('stock.adjustment.create') }}" class="btn btn-create-premium">
                                <i class="fas fa-plus me-2"></i>New Adjustment
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="container-fluid px-0 py-4">
                <!-- Advanced Filters -->
                <div class="premium-card mb-4">
                    <div class="card-body p-4">
                        <form action="{{ route('stock.adjustment.list') }}" method="GET" id="filterForm" autocomplete="off">
                            <div class="d-flex gap-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input report-type-radio" type="radio" name="report_type"
                                        id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily
                                        Reports</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input report-type-radio" type="radio" name="report_type"
                                        id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly
                                        Reports</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input report-type-radio" type="radio" name="report_type"
                                        id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly
                                        Reports</label>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-2 date-range-field">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Start
                                        Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-2 date-range-field">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">End Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ request('end_date') }}">
                                </div>

                                <div class="col-md-2 month-field" style="display: none;">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Month</label>
                                    <select name="month" class="form-select select2-simple">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 year-field" style="display: none;">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Year</label>
                                    <select name="year" class="form-select select2-simple">
                                        @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                            <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                                {{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Adjustment
                                        ID</label>
                                    <input type="text" name="adjustment_number" class="form-control" placeholder="ADJ-XXXX"
                                        value="{{ request('adjustment_number') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Global
                                        Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Name, SKU, Style..."
                                        value="{{ request('search') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Product</label>
                                    <select name="product_id" class="form-select select2-simple"
                                        data-placeholder="All Products">
                                        <option value="">All Products</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Category</label>
                                    <select name="category_id" class="form-select select2-simple"
                                        data-placeholder="All Categories">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Branch</label>
                                    <select name="branch_id" class="form-select select2-simple"
                                        data-placeholder="All Branches">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-1">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">Per Page</label>
                                    <select name="per_page" class="form-select">
                                        <option value="50" selected>50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>
                            </div>
                    </div>
                    <div class="card-footer bg-light border-top p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3"
                                    id="btn-excel-export">
                                    <i class="fas fa-file-excel me-2"></i>Excel
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3"
                                    id="btn-pdf-export">
                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" id="resetBtn" class="btn btn-light border px-4 fw-bold text-muted"
                                    style="height: 42px;">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>

            <!-- Table Container -->
            <div id="table-data-container">
                @include('erp.productStock.components.adjustmentTable')
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                function toggleReportFields() {
                    const reportType = $('.report-type-radio:checked').val();
                    $('.date-range-field, .month-field, .year-field').hide();
                    if (reportType === 'daily') $('.date-range-field').show();
                    else if (reportType === 'monthly') { $('.month-field').show(); $('.year-field').show(); }
                    else if (reportType === 'yearly') $('.year-field').show();
                }

                toggleReportFields();
                $('.report-type-radio').on('change', toggleReportFields);

                function refreshAdjustments(url = null) {
                    const form = $('#filterForm');
                    const targetUrl = url || form.attr('action');
                    const container = $('#table-data-container');
                    container.css('opacity', '0.5');

                    $.ajax({
                        url: targetUrl,
                        method: 'GET',
                        data: form.serialize(),
                        success: function (response) {
                            container.html(response);
                            container.css('opacity', '1');
                        },
                        error: function () { container.css('opacity', '1'); alert('Error loading data'); }
                    });
                }

                $('#filterForm').on('submit', function (e) { e.preventDefault(); refreshAdjustments(); });

                $('select').on('change', function () {
                    refreshAdjustments();
                });

                $('#resetBtn').on('click', function () {
                    $('#filterForm')[0].reset();
                    $('.select2-simple').val('').trigger('change');
                    toggleReportFields();
                    refreshAdjustments("{{ route('stock.adjustment.list') }}");
                });

                $(document).on('click', '.pagination a', function (e) {
                    e.preventDefault();
                    refreshAdjustments($(this).attr('href'));
                    window.scrollTo(0, 0);
                });

                $('#btn-excel-export').on('click', function () {
                    let data = $('#filterForm').serialize();
                    window.location.href = "{{ route('stock.adjustment.excel') }}?" + data;
                });

                $('#btn-pdf-export').on('click', function () {
                    let data = $('#filterForm').serialize();
                    window.location.href = "{{ route('stock.adjustment.pdf') }}?" + data;
                });

                // ── Delete Adjustment ──────────────────────────────────────────────
                let pendingAdjId = null;

                $(document).on('click', '.btn-delete-adjustment', function () {
                    const adjId = $(this).data('adj-id');
                    const adjNumber = $(this).data('adj-number');
                    const product = $(this).data('product');
                    const diff = parseInt($(this).data('diff'));

                    pendingAdjId = adjId;

                    $('#modal-adj-number').text(adjNumber || 'N/A');
                    $('#modal-adj-product').text(product || '—');

                    if (diff > 0) {
                        $('#modal-adj-diff').html('<span class="text-success">+' + diff + ' (Stock In)</span>');
                        $('#modal-adj-reversal').html('<span class="text-danger">−' + diff + ' (will be removed)</span>');
                    } else if (diff < 0) {
                        $('#modal-adj-diff').html('<span class="text-danger">' + diff + ' (Stock Out)</span>');
                        $('#modal-adj-reversal').html('<span class="text-success">+' + Math.abs(diff) + ' (will be restored)</span>');
                    } else {
                        $('#modal-adj-diff').html('<span class="text-muted">0 (no change)</span>');
                        $('#modal-adj-reversal').html('<span class="text-muted">No reversal needed</span>');
                    }

                    var modal = new bootstrap.Modal(document.getElementById('deleteAdjustmentModal'));
                    modal.show();
                });

                $(document).on('click', '#confirmDeleteBtn', function () {
                    if (!pendingAdjId) return;

                    const btn = $(this);
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

                    $.ajax({
                        url: '/erp/stock/adjustment/' + pendingAdjId,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            bootstrap.Modal.getInstance(document.getElementById('deleteAdjustmentModal')).hide();
                            pendingAdjId = null;
                            showAdjToast('success', res.message || 'Adjustment deleted successfully.');
                            refreshAdjustments();
                        },
                        error: function (xhr) {
                            const modalEl = document.getElementById('deleteAdjustmentModal');
                            if (modalEl && bootstrap.Modal.getInstance(modalEl)) {
                                bootstrap.Modal.getInstance(modalEl).hide();
                            }
                            const msg = xhr.responseJSON?.message || 'Failed to delete adjustment.';
                            showAdjToast('danger', msg);
                        },
                        complete: function () {
                            btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete & Reverse Stock');
                        }
                    });
                });

                function showAdjToast(type, message) {
                    const colors = { success: '#198754', danger: '#dc3545' };
                    const icons = { success: 'fa-check-circle', danger: 'fa-times-circle' };
                    const id = 'adj-toast-' + Date.now();
                    $('body').append(`
                        <div id="${id}" style="
                            position:fixed;bottom:24px;right:24px;z-index:9999;
                            background:${colors[type]};color:#fff;
                            padding:14px 20px;border-radius:10px;
                            font-weight:600;font-size:0.9rem;
                            box-shadow:0 6px 20px rgba(0,0,0,0.18);
                            display:flex;align-items:center;gap:10px;">
                            <i class="fas ${icons[type]}"></i> ${message}
                        </div>`);
                    setTimeout(() => $('#' + id).fadeOut(400, function () { $(this).remove(); }), 3500);
                }
                // ───────────────────────────────────────────────────────────────────
            });
        </script>
    @endpush
@endsection