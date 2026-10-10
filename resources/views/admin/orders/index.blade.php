@extends('layouts.admin')

@section('title', 'Manage Orders - ShopKart Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Platform Orders & Sub-Orders</h3>
        <p class="text-muted mb-0">Track unified multi-vendor customer orders and their decomposed seller packages.</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by order #, customer name, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Order Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Order #</th>
                        <th>Customer</th>
                        <th>Sellers / Sub-Orders</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Placed At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold font-monospace text-primary">{{ $order->order_number }}</span>
                            @if($order->is_gift)
                                <span class="badge bg-warning-subtle text-warning border ms-1" title="Gift Order">
                                    <i class="bi bi-gift"></i>
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $order->customer_name }}</div>
                            <small class="text-muted">{{ $order->customer_phone }}</small>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($order->sellerOrders as $so)
                                    <span class="badge bg-light text-dark border font-monospace" title="{{ $so->seller->store_name ?? 'Seller' }}">
                                        {{ $so->seller_order_number }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</span>
                        </td>
                        <td>
                            @if($order->payment_status == 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Paid ({{ strtoupper($order->payment_method) }})
                                </span>
                            @elseif($order->payment_status == 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">{{ strtoupper($order->payment_status) }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($order->order_status) {
                                    'delivered' => 'bg-success',
                                    'shipped', 'out_for_delivery' => 'bg-info text-dark',
                                    'processing', 'confirmed' => 'bg-primary',
                                    'cancelled', 'returned' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $order->order_status)) }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye me-1"></i> Inspect
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                            No orders found matching the criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
