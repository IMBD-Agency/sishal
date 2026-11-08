<!-- 3. Branch Sales Statement Component -->
<div class="sa-card mb-4" id="cardBranchStatement">
    <div class="sa-card-overlay"><div class="spinner-border text-primary" role="status"></div></div>
    <div class="sa-card-header">
        <h2 class="sa-card-title">
            <i class="fas fa-table sa-icon-statement"></i>
            Branch Sales Statement
        </h2>
        @php
            $overviewBadge = '6 Months Qty Overview';
            if (($dateRange ?? '') === 'this_year') {
                $overviewBadge = '12 Months Overview (This Year)';
            } elseif (($dateRange ?? '') === 'this_quarter') {
                $overviewBadge = '3 Months Overview (This Quarter)';
            } elseif (($dateRange ?? '') === 'today') {
                $overviewBadge = 'Today (6 Months Trend)';
            } elseif (($dateRange ?? '') === 'this_month') {
                $overviewBadge = 'This Month (6 Months Trend)';
            }
        @endphp
        <span id="branchStatementTimeframeBadge" class="badge bg-light text-dark border">{{ $overviewBadge }}</span>
    </div>

    <div class="table-container-scroll">
        <table class="sa-table sa-table-sticky-col">
            <thead>
                <tr id="branchStatementHead">
                    <th>Branch</th>
                    @if(!empty($branchSalesStatement['months']))
                        @foreach($branchSalesStatement['months'] as $m)
                            <th class="text-end">{{ $m }}</th>
                        @endforeach
                    @else
                        @for($i = 0; $i < 6; $i++)
                            <th class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 45px;"></div></th>
                        @endfor
                    @endif
                    <th class="text-end">Year Qty</th>
                    <th class="text-end">Total Value</th>
                    <th class="text-end">Total Profit</th>
                    <th class="text-end">Profit %</th>
                    <th class="text-end">Markup %</th>
                </tr>
            </thead>
            <tbody id="branchStatementBody">
                @forelse($branchSalesStatement['rows'] ?? [] as $row)
                <tr>
                    <td class="fw-bold text-dark">{{ $row['branch'] }}</td>
                    @foreach($row['months'] as $mVal)
                        <td class="text-end">{{ number_format($mVal) }} pcs</td>
                    @endforeach
                    <td class="text-end fw-bold text-dark">{{ number_format($row['year_total']) }} pcs</td>
                    <td class="text-end fw-bold text-primary">৳{{ number_format($row['total_value'], 2) }}</td>
                    <td class="text-end fw-bold text-success">৳{{ number_format($row['total_profit'], 2) }}</td>
                    <td class="text-end fw-bold">
                        <span class="badge @if($row['profit_pct'] >= 25) bg-success-subtle text-success border border-success-subtle @elseif($row['profit_pct'] >= 10) bg-warning-subtle text-warning-emphasis border border-warning-subtle @else bg-danger-subtle text-danger border border-danger-subtle @endif px-2 py-1">
                            {{ number_format($row['profit_pct'], 1) }}%
                        </span>
                    </td>
                    <td class="text-end fw-bold">
                        <span class="badge @if(($row['markup_pct'] ?? 0) >= 30) bg-primary-subtle text-primary border border-primary-subtle @elseif(($row['markup_pct'] ?? 0) >= 15) bg-info-subtle text-info-emphasis border border-info-subtle @else bg-secondary-subtle text-secondary border border-secondary-subtle @endif px-2 py-1">
                            {{ number_format($row['markup_pct'] ?? 0, 1) }}%
                        </span>
                    </td>
                </tr>
                @empty
                @for($i = 0; $i < 3; $i++)
                <tr class="sa-skeleton-row">
                    <td><div class="sa-skeleton sa-skeleton-text" style="width: 120px;"></div></td>
                    @for($j = 0; $j < 6; $j++)
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 50px;"></div></td>
                    @endfor
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 65px;"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 80px;"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 80px;"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-pill ms-auto"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-pill ms-auto"></div></td>
                </tr>
                @endfor
                @endforelse
            </tbody>
            <tfoot id="branchStatementFoot">
                @if(!empty($branchSalesStatement['totals']['months']) && !empty($branchSalesStatement['rows']))
                <tr>
                    <td>TOTAL</td>
                    @foreach($branchSalesStatement['totals']['months'] as $tVal)
                        <td class="text-end text-dark">{{ number_format($tVal) }} pcs</td>
                    @endforeach
                    <td class="text-end text-dark fw-bold">{{ number_format($branchSalesStatement['totals']['year_total']) }} pcs</td>
                    <td class="text-end text-primary fw-bold fs-6">৳{{ number_format($branchSalesStatement['totals']['total_value'], 2) }}</td>
                    <td class="text-end text-success fw-bold fs-6">৳{{ number_format($branchSalesStatement['totals']['total_profit'], 2) }}</td>
                    <td class="text-end fw-bold fs-6">
                        <span class="badge bg-primary px-2 py-1">
                            {{ number_format($branchSalesStatement['totals']['profit_pct'], 1) }}%
                        </span>
                    </td>
                    <td class="text-end fw-bold fs-6">
                        <span class="badge bg-dark text-white px-2 py-1">
                            {{ number_format($branchSalesStatement['totals']['markup_pct'] ?? 0, 1) }}%
                        </span>
                    </td>
                </tr>
                @else
                <tr>
                    <td>TOTAL</td>
                    @for($j = 0; $j < 6; $j++)
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 50px;"></div></td>
                    @endfor
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 65px;"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 80px;"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 80px;"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-pill ms-auto"></div></td>
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-pill ms-auto"></div></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
