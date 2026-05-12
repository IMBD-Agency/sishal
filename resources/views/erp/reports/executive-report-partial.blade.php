<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white;">
            <div class="card-body p-4 position-relative z-1">
                <p class="small text-white-50 mb-1 fw-bold text-uppercase">Main Cash Balance</p>
                <h2 class="fw-bold mb-0">৳{{ number_format($cashBalance, 2) }}</h2>
                <div class="mt-3 small opacity-75">
                    <i class="fas fa-money-bill-wave me-1"></i> Cash in Drawer
                </div>
            </div>
            <i class="fas fa-wallet position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white;">
            <div class="card-body p-4 position-relative z-1">
                <p class="small text-white-50 mb-1 fw-bold text-uppercase">Bank Balance</p>
                <h2 class="fw-bold mb-0">৳{{ number_format($bankBalance, 2) }}</h2>
                <div class="mt-3 small opacity-75">
                    <i class="fas fa-university me-1"></i> Savings / Current Account
                </div>
            </div>
            <i class="fas fa-credit-card position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #db2777 0%, #be185d 100%); color: white;">
            <div class="card-body p-4 position-relative z-1">
                <p class="small text-white-50 mb-1 fw-bold text-uppercase">Mobile Wallet Balance</p>
                <h2 class="fw-bold mb-0">৳{{ number_format($walletBalance, 2) }}</h2>
                <div class="mt-3 small opacity-75">
                    <i class="fas fa-mobile-alt me-1"></i> bKash / Nagad / Rocket
                </div>
            </div>
            <i class="fas fa-phone-alt position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: #111827;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center text-white">
                <div>
                    <h5 class="mb-0 opacity-75 text-uppercase small fw-bold tracking-wider">Total Available Liquidity</h5>
                    <div class="fs-1 fw-bold mt-1 text-info">৳{{ number_format($totalLiquidity, 2) }}</div>
                </div>
                <div class="text-end">
                    <div class="badge bg-info bg-opacity-25 text-info px-3 py-2 rounded-pill fw-bold border border-info border-opacity-50">
                        <i class="fas fa-shield-alt me-1"></i> Verified Assets
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">Cash Flow Summary</h5>
        <span class="badge bg-light text-dark fw-bold border">{{ $startDate->format('d M') }} - {{ $endDate->format('d M, Y') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Account Name</th>
                        <th class="py-3 border-0 small text-uppercase text-muted text-center">Opening Balance</th>
                        <th class="py-3 border-0 small text-uppercase text-muted text-center text-success">Total Inflow (+)</th>
                        <th class="py-3 border-0 small text-uppercase text-muted text-center text-danger">Total Outflow (-)</th>
                        <th class="py-3 border-0 small text-uppercase text-muted text-end pe-4">Closing Balance (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cash Account -->
                    <tr>
                        <td class="ps-4 border-0 py-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-teal-subtle text-teal rounded-3 p-2 me-3">
                                    <i class="fas fa-money-bill-alt fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Main Cash</div>
                                    <div class="small text-muted">Physical drawer cash</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center border-0 fw-bold text-muted">৳{{ number_format($openingCash, 2) }}</td>
                        <td class="text-center border-0 fw-bold text-success">+৳{{ number_format($cashIn, 2) }}</td>
                        <td class="text-center border-0 fw-bold text-danger">-৳{{ number_format($cashOut, 2) }}</td>
                        <td class="text-end pe-4 border-0 fw-bold fs-6">৳{{ number_format($cashBalance, 2) }}</td>
                    </tr>
                    
                    <!-- Bank Account -->
                    <tr>
                        <td class="ps-4 border-0 py-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary-subtle text-primary rounded-3 p-2 me-3">
                                    <i class="fas fa-university fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Bank Account</div>
                                    <div class="small text-muted">Digital banking assets</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center border-0 fw-bold text-muted">৳{{ number_format($openingBank, 2) }}</td>
                        <td class="text-center border-0 fw-bold text-success">+৳{{ number_format($bankIn, 2) }}</td>
                        <td class="text-center border-0 fw-bold text-danger">-৳{{ number_format($bankOut, 2) }}</td>
                        <td class="text-end pe-4 border-0 fw-bold fs-6">৳{{ number_format($bankBalance, 2) }}</td>
                    </tr>

                    <!-- Wallet Account -->
                    <tr>
                        <td class="ps-4 border-0 py-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-pink-subtle text-pink rounded-3 p-2 me-3">
                                    <i class="fas fa-mobile-alt fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Mobile Wallets</div>
                                    <div class="small text-muted">MFS (bKash/Nagad)</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center border-0 fw-bold text-muted">৳{{ number_format($openingWallet, 2) }}</td>
                        <td class="text-center border-0 fw-bold text-success">+৳{{ number_format($walletIn, 2) }}</td>
                        <td class="text-center border-0 fw-bold text-danger">-৳{{ number_format($walletOut, 2) }}</td>
                        <td class="text-end pe-4 border-0 fw-bold fs-6">৳{{ number_format($walletBalance, 2) }}</td>
                    </tr>

                    <!-- Grand Summary Row -->
                    <tr class="bg-light">
                        <td class="ps-4 py-3 fw-bold text-uppercase border-top">Combined Liquidity</td>
                        <td class="text-center py-3 fw-bold border-top">৳{{ number_format($openingCash + $openingBank + $openingWallet, 2) }}</td>
                        <td class="text-center py-3 fw-bold text-success border-top">+৳{{ number_format($cashIn + $bankIn + $walletIn, 2) }}</td>
                        <td class="text-center py-3 fw-bold text-danger border-top">-৳{{ number_format($cashOut + $bankOut + $walletOut, 2) }}</td>
                        <td class="text-end pe-4 py-3 fw-bold text-primary fs-5 border-top">৳{{ number_format($totalLiquidity, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-teal-subtle { background-color: #f0fdfa; color: #0d9488; }
    .bg-pink-subtle { background-color: #fdf2f8; color: #db2777; }
    .bg-primary-subtle { background-color: #eff6ff; color: #2563eb; }
    .icon-box { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
    .tracking-wider { letter-spacing: 0.05em; }
</style>
