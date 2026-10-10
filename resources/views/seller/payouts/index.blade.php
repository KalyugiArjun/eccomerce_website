@extends('layouts.seller')

@section('page_title', 'Earnings & Settlement Payouts')

@section('content')
<!-- Financial Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small fw-bold text-uppercase">Gross Sales (Delivered)</span>
            <h4 class="fw-bold mb-0 text-dark">₹{{ number_format($grossSales) }}</h4>
            <small class="text-muted">Total value of completed orders</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small fw-bold text-uppercase">Platform Commission</span>
            <h4 class="fw-bold mb-0 text-danger">-₹{{ number_format($commissionDeducted) }}</h4>
            <small class="text-muted">Deducted according to rate (10%)</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small fw-bold text-uppercase">Already Settled</span>
            <h4 class="fw-bold mb-0 text-primary">₹{{ number_format($alreadyPaidOut) }}</h4>
            <small class="text-muted">Transferred to your bank account</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 bg-success bg-opacity-10 border border-success">
            <span class="text-success small fw-bold text-uppercase">Available for Payout</span>
            <h4 class="fw-bold mb-0 text-success">₹{{ number_format($availableForPayout) }}</h4>
            <small class="text-success">Net balance ready for withdrawal</small>
        </div>
    </div>
</div>

<div class="row gy-4 mb-4">
    <!-- Payout Request & Bank Snapshot -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-bank text-primary me-2"></i> Registered Bank Account</h6>
            
            <div class="p-3 bg-light rounded-3 border mb-3 small">
                <div><strong>Bank Name:</strong> {{ $seller->bank_name ?: 'Not configured' }}</div>
                <div><strong>Account Holder:</strong> {{ $seller->bank_account_name ?: 'Not configured' }}</div>
                <div><strong>Account Number:</strong> {{ $seller->bank_account_number ? '••••••••' . substr($seller->bank_account_number, -4) : 'Not configured' }}</div>
                <div><strong>IFSC Code:</strong> {{ $seller->bank_ifsc ?: 'Not configured' }}</div>
            </div>

            <form action="{{ route('seller.payouts.request') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sk-primary w-100 py-2 fw-bold" {{ $availableForPayout < 500 ? 'disabled' : '' }}>
                    <i class="bi bi-arrow-up-right-circle me-1"></i> Request Payout (₹{{ number_format($availableForPayout) }})
                </button>
            </form>
            <small class="text-muted text-center mt-2 d-block">Minimum withdrawal limit: ₹500. Direct NEFT/IMPS transfer processed within 24-48 business hours.</small>
        </div>
    </div>

    <!-- Payout History Table -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Settlement History</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Payout #</th>
                            <th>Net Amount</th>
                            <th>Status</th>
                            <th>Reference #</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $p)
                            <tr>
                                <td><code>{{ $p->payout_number }}</code></td>
                                <td class="fw-bold text-success">₹{{ number_format($p->net_amount) }}</td>
                                <td>
                                    <span class="badge {{ $p->status === 'paid' ? 'bg-success' : ($p->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td><small class="text-muted font-monospace">{{ $p->transaction_reference ?: 'Pending processing' }}</small></td>
                                <td class="small text-muted">{{ $p->created_at->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No payouts requested yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
