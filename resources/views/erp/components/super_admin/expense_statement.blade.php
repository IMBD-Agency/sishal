<!-- 6. Expense Statement Component -->
<div class="sa-card mb-4" id="cardExpenseStatement">
    <div class="sa-card-overlay"><div class="spinner-border text-primary" role="status"></div></div>
    <div class="sa-card-header">
        <h2 class="sa-card-title">
            <i class="fas fa-wallet sa-icon-expense"></i>
            Expense Statement (Branch Wise)
        </h2>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-danger font-semibold">
                <i class="fas fa-receipt me-1"></i> View All Expenses
            </a>
        </div>
    </div>

    <div class="table-container-scroll">
        <table class="sa-table sa-table-sticky-col">
            <thead>
                <tr id="expenseStatementHead">
                    <th>Branch</th>
                    @if(!empty($expenseStatement['months']))
                        @foreach($expenseStatement['months'] as $m)
                            <th class="text-end">{{ $m }}</th>
                        @endforeach
                    @else
                        @for($i = 0; $i < 6; $i++)
                            <th class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 45px;"></div></th>
                        @endfor
                    @endif
                    <th class="text-end">Year Total</th>
                </tr>
            </thead>
            <tbody id="expenseStatementBody">
                @forelse(($expenseStatement['branches'] ?? $expenseStatement['categories'] ?? []) as $bRow)
                <tr>
                    <td class="fw-bold text-dark">
                        <i class="fas fa-building text-danger me-2 opacity-75"></i>{{ $bRow['branch'] ?? $bRow['category'] ?? '' }}
                    </td>
                    @foreach($bRow['months'] as $mVal)
                        <td class="text-end">৳{{ number_format($mVal) }}</td>
                    @endforeach
                    <td class="text-end fw-bold text-danger">৳{{ number_format($bRow['year_total']) }}</td>
                </tr>
                @empty
                @for($i = 0; $i < 3; $i++)
                <tr class="sa-skeleton-row">
                    <td><div class="sa-skeleton sa-skeleton-text" style="width: 130px;"></div></td>
                    @for($j = 0; $j < 6; $j++)
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 60px;"></div></td>
                    @endfor
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 80px;"></div></td>
                </tr>
                @endfor
                @endforelse
            </tbody>
            <tfoot id="expenseStatementFoot">
                @if(!empty($expenseStatement['total']['months']) && !empty($expenseStatement['branches'] ?? $expenseStatement['categories'] ?? []))
                <tr>
                    <td>TOTAL EXPENSE</td>
                    @foreach($expenseStatement['total']['months'] as $tVal)
                        <td class="text-end text-danger fw-bold">৳{{ number_format($tVal) }}</td>
                    @endforeach
                    <td class="text-end text-danger fs-6 fw-extrabold">৳{{ number_format($expenseStatement['total']['year_total']) }}</td>
                </tr>
                @else
                <tr>
                    <td>TOTAL EXPENSE</td>
                    @for($j = 0; $j < 6; $j++)
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 60px;"></div></td>
                    @endfor
                    <td class="text-end"><div class="sa-skeleton sa-skeleton-text ms-auto" style="width: 80px;"></div></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
