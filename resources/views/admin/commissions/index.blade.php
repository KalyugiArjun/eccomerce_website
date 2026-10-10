@extends('layouts.admin')

@section('title', 'Commissions & Payouts - ShopKart Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Platform Commissions & Seller Payouts</h3>
        <p class="text-muted mb-0">Track platform fees, view net vendor receivables, and disburse bank transfers.</p>
    </div>
</div>

<!-- Financial KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small">Total Platform Commission Settled</span>
                    <h3 class="fw-bold text-success mb-0">₹{{ number_format($totalCommissionEarned, 2) }}</h3>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-circle">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small">Total Disbursed to Sellers</span>
                    <h3 class="fw-bold text-primary mb-0">₹{{ number_format($totalPaidOut, 2) }}</h3>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                    <i class="bi bi-bank fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small">Pending Payout Requests</span>
                    <h3 class="fw-bold text-warning mb-0">{{ $pendingPayoutsCount }}</h3>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seller Payouts Queue -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="fw-bold mb-0">Seller Payout Disbursements</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Payout #</th>
                        <th>Seller / Store</th>
                        <th>Gross Sales</th>
                        <th>Platform Fee</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                    <tr>
                        <td class="ps-4 font-monospace fw-semibold">{{ $payout->payout_number }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payout->seller->store_name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $payout->seller->bank_account_name ?? 'Bank' }} • IFSC: {{ $payout->seller->bank_ifsc ?? 'N/A' }}</small>
                        </td>
                        <td>₹{{ number_format($payout->gross_amount, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($payout->commission_deducted, 2) }}</td>
                        <td class="fw-bold text-success fs-6">₹{{ number_format($payout->net_amount, 2) }}</td>
                        <td>
                            @if($payout->status == 'paid')
                                <span class="badge bg-success">Disbursed</span>
                            @elseif($payout->status == 'processing')
                                <span class="badge bg-info text-dark">Processing</span>
                            @elseif($payout->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $payout->created_at->format('d M Y') }}</small></td>
                        <td class="text-end pe-4">
                            @if($payout->status != 'paid')
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payoutModal{{ $payout->id }}">
                                    Process Payout
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="payoutModal{{ $payout->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('admin.payouts.process', $payout->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Disburse Payout #{{ $payout->payout_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Transferring net amount <strong>₹{{ number_format($payout->net_amount, 2) }}</strong> to:</p>
                                                    <div class="p-3 bg-light rounded-3 mb-3 small">
                                                        <div>Beneficiary: <strong>{{ $payout->seller->bank_account_name ?? 'Seller' }}</strong></div>
                                                        <div>Account: <strong>{{ $payout->seller->bank_account_number ?? 'N/A' }}</strong></div>
                                                        <div>IFSC: <strong>{{ $payout->seller->bank_ifsc ?? 'N/A' }}</strong></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Disbursement Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="paid">Paid (Funds successfully sent)</option>
                                                            <option value="processing">Processing (NEFT/RTGS initiated)</option>
                                                            <option value="failed">Failed (Account invalid)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Bank UTR / Transaction Ref</label>
                                                        <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. UTR1234567890" value="{{ $payout->transaction_reference }}">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes">{{ $payout->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Confirm Disbursement</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="small text-muted font-monospace">{{ $payout->transaction_reference ?? 'SETTLED' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No payout requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Platform Commission Audit Logs -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="fw-bold mb-0">Commission Ledger (Audit Trail)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Sub-Order #</th>
                        <th>Seller</th>
                        <th>Order Amount</th>
                        <th>Commission Rate</th>
                        <th>Platform Fee</th>
                        <th>Seller Net</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $comm)
                    <tr>
                        <td class="ps-4 font-monospace">{{ $comm->sellerOrder->seller_order_number ?? 'SUB-ORDER' }}</td>
                        <td>{{ $comm->seller->store_name ?? 'N/A' }}</td>
                        <td>₹{{ number_format($comm->order_amount, 2) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $comm->commission_rate }}%</span></td>
                        <td class="fw-bold text-success">₹{{ number_format($comm->commission_amount, 2) }}</td>
                        <td>₹{{ number_format($comm->seller_earnings, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $comm->status == 'settled' ? 'success' : 'warning text-dark' }}">
                                {{ ucfirst($comm->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No commission records yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
