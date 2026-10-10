@extends('layouts.app')

@section('title', 'My Orders - ShopKart')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-box-seam text-primary me-2"></i> My Orders</h2>
        <a href="{{ route('products.index') }}" class="btn btn-sk-primary btn-sm">Explore More Products</a>
    </div>

    @if($orders->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="bi bi-bag-x text-muted display-4 mb-3"></i>
            <h4>No Orders Yet</h4>
            <p class="text-muted">You haven't placed any orders on ShopKart yet.</p>
            <div>
                <a href="{{ route('products.index') }}" class="btn btn-sk-primary">Start Shopping</a>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-4">
            @foreach($orders as $order)
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="text-muted small d-block">ORDER PLACED</span>
                                <strong>{{ $order->placed_at->format('d M Y') }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small d-block">TOTAL</span>
                                <strong>₹{{ number_format($order->grand_total) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small d-block">SHIP TO</span>
                                <strong>{{ $order->customer_name }}</strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">ORDER # <code>{{ $order->order_number }}</code></span>
                            <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-outline-primary btn-sm">
                                View Order Details
                            </a>
                            <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Print Invoice">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Sub-orders summary -->
                        @foreach($order->sellerOrders as $subOrder)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-shop text-primary"></i>
                                        <strong>{{ $subOrder->seller->store_name }}</strong>
                                        <span class="badge bg-secondary font-monospace">{{ $subOrder->sub_order_number }}</span>
                                    </div>
                                    <span class="badge {{ $subOrder->status_badge_class }}">{{ ucfirst($subOrder->status) }}</span>
                                </div>

                                <div class="row align-items-center gy-2">
                                    <div class="col-md-8">
                                        @foreach($subOrder->items as $item)
                                            <div class="small text-dark mb-1">
                                                &bull; <strong>{{ $item->product_name }}</strong> &times; {{ $item->quantity }}
                                                <span class="text-muted">(₹{{ number_format($item->unit_price) }} each)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        @if($subOrder->tracking_number)
                                            <small class="text-muted d-block">Tracking: <code>{{ $subOrder->tracking_number }}</code></small>
                                        @endif
                                        <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-sm btn-link text-decoration-none">
                                            Track Package &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
