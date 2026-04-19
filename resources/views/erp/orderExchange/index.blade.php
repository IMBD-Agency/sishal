@extends('erp.master')

@section('title', 'Order Exchanges')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order Exchanges</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Order Exchange List</h2>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('orderExchange.export', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold export-link-excel">
                            <i class="fas fa-file-excel me-1"></i>EXCEL
                        </a>
                        <a href="{{ route('orderExchange.create') }}" class="btn btn-primary btn-sm fw-bold">
                            <i class="fas fa-plus me-1"></i>NEW EXCHANGE
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Filter Section -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="filterForm" action="{{ route('orderExchange.list') }}" method="GET" autocomplete="off">
                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly</label>
                            </div>
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Keywords</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Ref, Customer, Phone..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Customer</label>
                                <select name="customer_id" class="form-select form-select-sm select2">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Daily Fields -->
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-4 report-field monthly-group d-none">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                        <select name="month" class="form-select form-select-sm">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                        <select name="year" class="form-select form-select-sm">
                                            @foreach(range(date('Y')-5, date('Y')+1) as $y)
                                                <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-2 report-field yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select form-select-sm">
                                    @foreach(range(date('Y')-5, date('Y')+1) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-auto ms-auto">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fas fa-filter me-1"></i>APPLY
                                    </button>
                                    <a href="{{ route('orderExchange.list') }}" class="btn btn-light border btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fas fa-undo me-1"></i>RESET
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="report-content-area">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase" style="font-size: 0.7rem;">
                                    <tr>
                                        <th class="ps-4">Ref</th>
                                        <th>Product Details</th>
                                        <th>Brand/Category</th>
                                        <th>Season</th>
                                        <th>Size</th>
                                        <th class="text-center">Qty</th>
                                        <th>Credit</th>
                                        <th>Exch. Order</th>
                                        <th>Discount</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exchanges as $exchange)
                                        @php
                                            $newOrder = $exchange->exchangeOrder;
                                        @endphp
                                        @foreach($exchange->items as $index => $item)
                                        <tr style="font-size: 0.8rem;">
                                            @if($index === 0)
                                            <td class="ps-4" rowspan="{{ $exchange->items->count() }}">
                                                <a href="{{ route('orderExchange.show', $exchange->id) }}" class="fw-bold text-decoration-none">
                                                    #EXC-{{ str_pad($exchange->id, 5, '0', STR_PAD_LEFT) }}
                                                </a>
                                                <div class="small text-muted" style="font-size: 0.65rem;">{{ $exchange->created_at->format('d/m/y') }}</div>
                                            </td>
                                            @endif
                                            
                                            <td>
                                                <div class="fw-bold text-truncate" style="max-width: 130px;" title="{{ $item->product->name ?? 'N/A' }}">
                                                    {{ $item->product->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.7rem;">{{ $exchange->customer->name ?? 'Walk-in' }}</div>
                                            </td>
                                            <td>
                                                <div class="badge bg-light text-dark border p-1" style="font-size: 0.65rem;">{{ $item->product->brand->name ?? '-' }}</div>
                                                <div class="small text-muted d-block text-truncate" style="max-width: 80px;">{{ $item->product->category->name ?? '-' }}</div>
                                            </td>
                                            <td class="small">{{ $item->product->season->name ?? '-' }}</td>
                                            <td><span class="badge bg-info-subtle text-info">{{ $item->variation->name ?? 'Std' }}</span></td>
                                            <td class="text-center fw-bold">{{ $item->returned_qty }}</td>
                                            <td class="fw-bold">৳{{ number_format($item->total_price, 2) }}</td>

                                            @if($index === 0)
                                            <td rowspan="{{ $exchange->items->count() }}">
                                                @if($newOrder)
                                                    <a href="{{ route('order.show', $newOrder->id) }}" class="badge bg-success bg-opacity-10 text-success text-decoration-none border border-success border-opacity-25 py-1">
                                                        {{ $newOrder->order_number }}
                                                    </a>
                                                @else
                                                    <span class="text-muted small">N/A</span>
                                                @endif
                                            </td>
                                            <td rowspan="{{ $exchange->items->count() }}" class="text-muted">
                                                ৳{{ number_format($newOrder->discount ?? 0, 2) }}
                                            </td>
                                            <td rowspan="{{ $exchange->items->count() }}" class="text-success fw-bold">
                                                ৳{{ number_format($newOrder->invoice->paid_amount ?? 0, 2) }}
                                            </td>
                                            <td rowspan="{{ $exchange->items->count() }}" class="text-danger fw-bold">
                                                ৳{{ number_format($newOrder->invoice->due_amount ?? 0, 2) }}
                                            </td>
                                            <td rowspan="{{ $exchange->items->count() }}" class="text-end pe-4">
                                                <a href="{{ route('orderExchange.show', $exchange->id) }}" class="btn btn-sm btn-outline-primary border shadow-sm px-2">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-5 text-muted">No exchanges found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light border-top-2 fw-bold">
                                    @php
                                        $totalQty = 0;
                                        $totalCredit = 0;
                                        $totalDisc = 0;
                                        $totalPaid = 0;
                                        $totalDue = 0;
                                        foreach($exchanges as $exc) {
                                            $totalQty += $exc->items->sum('returned_qty');
                                            $totalCredit += $exc->items->sum('total_price');
                                            $totalDisc += ($exc->exchangeOrder->discount ?? 0);
                                            $totalPaid += ($exc->exchangeOrder->invoice->paid_amount ?? 0);
                                            $totalDue += ($exc->exchangeOrder->invoice->due_amount ?? 0);
                                        }
                                    @endphp
                                    <tr style="font-size: 0.8rem;">
                                        <td colspan="5" class="ps-4 py-3 text-end text-uppercase small" style="letter-spacing: 0.05em;">Current Page Totals:</td>
                                        <td class="text-center py-3">{{ $totalQty }}</td>
                                        <td class="py-3">৳{{ number_format($totalCredit, 2) }}</td>
                                        <td class="py-3"></td>
                                        <td class="py-3 text-muted">৳{{ number_format($totalDisc, 2) }}</td>
                                        <td class="py-3 text-success">৳{{ number_format($totalPaid, 2) }}</td>
                                        <td class="py-3 text-danger">৳{{ number_format($totalDue, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $exchanges->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function toggleReportFields() {
        var reportType = $('.report-type-radio:checked').val();
        $('.report-field').addClass('d-none');
        
        if (reportType === 'daily') {
            $('.daily-group').removeClass('d-none');
        } else if (reportType === 'monthly') {
            $('.monthly-group').removeClass('d-none');
        } else if (reportType === 'yearly') {
            $('.yearly-group').removeClass('d-none');
        }
    }

    toggleReportFields();

    $('.report-type-radio').change(function() {
        const type = $(this).val();
        if (type === 'daily') {
            const today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
            $('#end_date').val(today);
        }
        toggleReportFields();
    });

    function refreshOrderExchanges() {
        const form = $('#filterForm');
        const container = $('#report-content-area');
        container.css('opacity', '0.5');
        
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            success: function(response) {
                // Since this might return the full layout, we need to extract the partial content
                // but usually our controllers check for request->ajax(). 
                // If it doesn't return partial, we just refresh the whole page or extract the area.
                const newContent = $(response).find('#report-content-area').html();
                if (newContent) {
                    container.html(newContent);
                } else {
                    container.html(response);
                }
                container.css('opacity', '1');
                
                // Update Excel link
                const queryParams = form.serialize();
                $('.export-link-excel').attr('href', '{{ route("orderExchange.export") }}?' + queryParams);
            },
            error: function() {
                container.css('opacity', '1');
            }
        });
    }

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        refreshOrderExchanges();
    });

    // Handle pagination links
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const container = $('#report-content-area');
        container.css('opacity', '0.5');
        
        $.ajax({
            url: url,
            success: function(response) {
                const newContent = $(response).find('#report-content-area').html();
                if (newContent) {
                    container.html(newContent);
                } else {
                    container.html(response);
                }
                container.css('opacity', '1');
                window.scrollTo(0, 0);
            }
        });
    });
});
</script>
@endpush

