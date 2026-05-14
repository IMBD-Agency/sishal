<div class="row">
    <!-- Left Column: INFLOWS (Activities increasing assets/cash) -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #28a745 !important;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h4 class="text-success fw-bold"><i class="fas fa-arrow-down me-2"></i> Money In & Activities</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                            <i class="fas fa-chart-line fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Total Revenue</h6>
                                            <small class="text-muted">Total Invoiced Value (Today)</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-primary fs-5">
                                    {{ number_format($data['totalRevenue'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-success bg-opacity-10">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-25 text-success rounded p-2 me-3">
                                            <i class="fas fa-cash-register fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Sales Collections</h6>
                                            <small class="text-muted">Actual money from today's sales</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-success fs-5">
                                    {{ number_format($data['salesCollections'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                            <i class="fas fa-money-bill-wave fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Money Receipts</h6>
                                            <small class="text-muted">Due collections for past sales</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-success fs-5">
                                    {{ number_format($data['moneyReceipts'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                            <i class="fas fa-undo-alt fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Purchase Returns</h6>
                                            <small class="text-muted">Refunds/Value from Suppliers</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-success fs-5">
                                    {{ number_format($data['purchaseReturns'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td class="fw-bold text-end">Total Cash Inflow:</td>
                                <td class="text-end fw-bold text-success fs-4">{{ number_format($data['totalInflow'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: OUTFLOWS (Activities decreasing assets/cash) -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #dc3545 !important;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h4 class="text-danger fw-bold"><i class="fas fa-arrow-up me-2"></i> Money Out & Costs</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                            <i class="fas fa-file-invoice fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Total Purchases</h6>
                                            <small class="text-muted">Total Supplier Bills (Today)</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-primary fs-5">
                                    {{ number_format($data['totalPurchasesValue'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-danger bg-opacity-10">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-25 text-danger rounded p-2 me-3">
                                            <i class="fas fa-hand-holding-usd fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Supplier Payments</h6>
                                            <small class="text-muted">Actual cash paid to suppliers</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-danger fs-5">
                                    {{ number_format($data['supplierPayments'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-3">
                                            <i class="fas fa-file-invoice-dollar fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Expense Payments</h6>
                                            <small class="text-muted">Operating expenses paid</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-danger fs-5">
                                    {{ number_format($data['totalExpenses'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                            <i class="fas fa-undo fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Total Sales Returns</h6>
                                            <small class="text-muted">Total value of returned items</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-primary fs-5">
                                    {{ number_format($data['totalSalesReturnsValue'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-danger bg-opacity-10">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-25 text-danger rounded p-2 me-3">
                                            <i class="fas fa-coins fa-fw"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Cash Refunds</h6>
                                            <small class="text-muted">Actual money paid back</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-danger fs-5">
                                    {{ number_format($data['actualCashRefunds'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td class="fw-bold text-end">Total Cash Outflow:</td>
                                <td class="text-end fw-bold text-danger fs-4">{{ number_format($data['totalOutflow'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


