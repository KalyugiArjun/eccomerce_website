@extends('layouts.seller')

@section('page_title', 'Seller Sub-Orders & Fulfillment')

@section('content')
<div class="card border-0 shadow-sm p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <!-- Status Filter Pills -->
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('seller.orders.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
            <a href="{{ route('seller.orders.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') === 'confirmed' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">Confirmed</a>
            <a href="{{ route('seller.orders.index', ['status' => 'processing']) }}" class="btn btn-sm {{ request('status') === 'processing' ? 'btn-warning fw-bold' : 'btn-outline-secondary' }}">Processing</a>
            <a href="{{ route('seller.orders.index', ['status' => 'shipped']) }}" class="btn btn-sm {{ request('status') === 'shipped' ? 'btn-primary' : 'btn-outline-secondary' }}">Shipped</a>
            <a href="{{ route('seller.orders.index', ['status' => 'delivered']) }}" class="btn btn-sm {{ request('status') === 'delivered' ? 'btn-success' : 'btn-outline-secondary' }}">Delivered</a>
        </div>

        <small class="text-muted">Showing orders specifically assigned to your merchant store.</small>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Sub-Order #</th>
                    <th>Customer Name</th>
                    <th>Package Items</th>
                    <th>Subtotal</th>
                    <th>Platform Comm. (10%)</th>
                    <th>Your Net Share</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $so)
                    <tr>
                        <td>
                            <strong class="font-monospace text-dark">{{ $so->sub_order_number }}</strong>
                            <small class="text-muted d-block">{{ $so->created_at->format('d M, h:i A') }}</small>
                        </td>
                        <td>
                            <div>{{ $so->order->customer_name }}</div>
                            <small class="text-muted">{{ $so->order->customer_phone }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $so->items->count() }} items</span>
                        </td>
                        <td class="fw-bold">₹{{ number_format($so->grand_total) }}</td>
                        <td class="text-danger small">-₹{{ number_format($so->platform_commission_amount) }}</td>
                        <td class="text-success fw-bold">₹{{ number_format($so->seller_net_earnings) }}</td>
                        <td>
                            <span class="badge {{ $so->status_badge_class }}">{{ ucfirst($so->status) }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('seller.orders.show', $so->sub_order_number) }}" class="btn btn-sm btn-outline-primary">
                                Manage & Dispatch &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No orders found matching criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
