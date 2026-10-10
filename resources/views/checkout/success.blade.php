@extends('layouts.app')

@section('title', 'Order Confirmed - ' . $order->order_number)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 text-center mb-4">
                <div class="mb-3 text-success">
                    <i class="bi bi-check-circle-fill display-3"></i>
                </div>
                <h2 class="fw-bold mb-1">Thank You! Your Order has been Placed</h2>
                <p class="text-muted">We have sent an order confirmation & invoice receipt to <strong>{{ $order->customer_email }}</strong>.</p>
                
                <div class="d-inline-block bg-light p-3 rounded-3 border my-3">
                    <div class="small text-muted text-uppercase fw-bold">Main Order Number</div>
                    <div class="fs-4 fw-bold text-dark font-monospace">{{ $order->order_number }}</div>
                    <small class="text-success"><i class="bi bi-clock-history"></i> Placed on {{ $order->placed_at->format('d M Y, h:i A') }}</small>
                </div>

                <!-- Multi-Vendor Split Packages (Section 11) -->
                <div class="text-start mt-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-boxes text-primary me-2"></i> Multi-Vendor Delivery Packages ({{ $order->sellerOrders->count() }} Stores)</h5>
                    <p class="text-muted small">Your items are being fulfilled directly by our verified local merchants. Each seller manages their own quick local dispatch:</p>

                    <div class="d-flex flex-column gap-3">
                        @foreach($order->sellerOrders as $subOrder)
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong class="text-dark">{{ $subOrder->seller->store_name }}</strong>
                                        <span class="badge bg-secondary ms-2 font-monospace">{{ $subOrder->sub_order_number }}</span>
                                    </div>
                                    <span class="badge {{ $subOrder->status_badge_class }}">{{ ucfirst($subOrder->status) }}</span>
                                </div>

                                <div class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt"></i> {{ $subOrder->seller->city }} &bull;
                                    <i class="bi bi-truck"></i> Tracking: <code>{{ $subOrder->shipment?->tracking_number ?? 'Generating...' }}</code>
                                </div>

                                <div class="border-top pt-2">
                                    @foreach($subOrder->items as $item)
                                        <div class="d-flex justify-content-between align-items-center py-1 small">
                                            <span>{{ $item->product_name }} &times; {{ $item->quantity }}</span>
                                            <span class="fw-bold">₹{{ number_format($item->total_price) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-sk-primary">
                        <i class="bi bi-map me-1"></i> Track Order Live
                    </a>
                    <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-1"></i> View Tax Invoice
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-link text-decoration-none">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
