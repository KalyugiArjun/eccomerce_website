@extends('layouts.admin')

@section('title', 'Returns & Refunds - ShopKart Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Customer Returns & Refund Moderation</h3>
        <p class="text-muted mb-0">Review return requests, verify seller compliance, and authorize customer refunds.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Refund #</th>
                        <th>Order / Sub-Order</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $refund)
                    <tr>
                        <td class="ps-4 font-monospace fw-semibold">{{ $refund->refund_number }}</td>
                        <td>
                            <div><a href="{{ route('admin.orders.show', $refund->order->id) }}" class="fw-semibold text-decoration-none">#{{ $refund->order->order_number }}</a></div>
                            <small class="text-muted font-monospace">{{ $refund->sellerOrder->seller_order_number ?? '' }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $refund->order->customer_name ?? 'Customer' }}</div>
                            <small class="text-muted">{{ $refund->order->customer_phone ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $refund->sellerOrder->seller->store_name ?? 'Seller' }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-danger">₹{{ number_format($refund->amount, 2) }}</span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="{{ $refund->reason }}">{{ $refund->reason }}</div>
                        </td>
                        <td>
                            @php
                                $statusBadge = match($refund->status) {
                                    'processed' => 'bg-success',
                                    'admin_approved', 'approved' => 'bg-primary',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $refund->status)) }}</span>
                        </td>
                        <td class="text-end pe-4">
                            @if($refund->status != 'processed')
                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#refundModal{{ $refund->id }}">
                                    Review
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="refundModal{{ $refund->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('admin.refunds.update', $refund->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Moderate Refund #{{ $refund->refund_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="p-3 bg-light rounded-3 mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Refund Amount:</span>
                                                            <strong class="text-danger">₹{{ number_format($refund->amount, 2) }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Order:</span>
                                                            <span class="font-monospace">#{{ $refund->order->order_number }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted">Claim Reason:</span>
                                                            <p class="mb-0 mt-1 fst-italic">"{{ $refund->reason }}"</p>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Action Decision</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="admin_approved" {{ $refund->status == 'admin_approved' ? 'selected' : '' }}>Admin Approved (Awaiting gateway batch)</option>
                                                            <option value="processed" {{ $refund->status == 'processed' ? 'selected' : '' }}>Processed (Funds returned to original source / UPI)</option>
                                                            <option value="rejected" {{ $refund->status == 'rejected' ? 'selected' : '' }}>Reject Claim (Violates return policy)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Admin Notes & Resolution</label>
                                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Explain decision to customer and seller">{{ $refund->admin_notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Save Moderation</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="small text-muted">Settled</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                            No return or refund requests pending.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($refunds->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $refunds->links() }}
    </div>
    @endif
</div>
@endsection
