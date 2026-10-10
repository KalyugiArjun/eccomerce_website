@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number . ' - ShopKart Admin')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div>
            <h3 class="fw-bold mb-1">Order #{{ $order->order_number }}</h3>
            <span class="text-muted">Placed on {{ $order->created_at->format('d M Y, h:i A') }} • Multi-Vendor Split Status</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customer.orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline-dark">
                <i class="bi bi-printer me-1"></i> View Tax Invoice
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Order Summary & Sub-Orders -->
    <div class="col-lg-8">
        <!-- Seller Sub-Orders Breakdown -->
        <h5 class="fw-bold mb-3"><i class="bi bi-diagram-3 me-2 text-primary"></i>Decomposed Seller Sub-Orders ({{ $order->sellerOrders->count() }})</h5>

        @foreach($order->sellerOrders as $so)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-dark font-monospace me-2">{{ $so->seller_order_number }}</span>
                    <strong>{{ $so->seller->store_name ?? 'Seller Store' }}</strong>
                    <span class="text-muted small">({{ $so->seller->city ?? 'India' }})</span>
                </div>
                <div>
                    @php
                        $soBadge = match($so->status) {
                            'delivered' => 'bg-success',
                            'shipped', 'out_for_delivery' => 'bg-info text-dark',
                            'confirmed', 'processing' => 'bg-primary',
                            'cancelled', 'returned' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $soBadge }}">{{ ucwords(str_replace('_', ' ', $so->status)) }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Item</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($so->items as $item)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $item->product->main_image ?? 'https://placehold.co/50x50/f8f9fa/333?text=Item' }}" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <div class="fw-semibold">{{ $item->product_name }}</div>
                                            @if($item->variant_name)
                                                <small class="text-muted">{{ $item->variant_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>₹{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-end pe-3 fw-bold">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    @if($so->shipment)
                        <span>Courier: <strong>{{ $so->shipment->courier_name ?? 'Local Delivery' }}</strong> | Tracking: <code>{{ $so->shipment->tracking_number ?? 'In Transit' }}</code></span>
                    @else
                        <span>Shipment not dispatched yet</span>
                    @endif
                </div>
                <div>
                    Sub-Order Total: <strong class="text-dark">₹{{ number_format($so->total_amount, 2) }}</strong>
                    (Platform Comm: <span class="text-muted">₹{{ number_format($so->commission_amount, 2) }}</span>)
                </div>
            </div>
        </div>
        @endforeach

        <!-- Delivery Address & Gift Info -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Shipping & Delivery Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Delivery Address</label>
                        <div class="fw-semibold">{{ $order->shipping_address }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Customer Contact</label>
                        <div class="fw-semibold">{{ $order->customer_name }}</div>
                        <div class="text-muted small">{{ $order->customer_email }} • {{ $order->customer_phone }}</div>
                    </div>
                    @if($order->is_gift)
                    <div class="col-12">
                        <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle">
                            <div class="fw-bold text-dark mb-1"><i class="bi bi-gift-fill text-warning me-1"></i> Special Gift Mode Order</div>
                            <div class="small text-muted">Gift Message: <em>"{{ $order->gift_message ?? 'Happy Shopping!' }}"</em></div>
                            <div class="small text-muted">Price Tag Hidden: Yes</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions & Payment Breakdown -->
    <div class="col-lg-4">
        <!-- Master Order Status Management -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="fw-bold mb-0 text-white">Order Status Override</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Master Order Lifecycle</label>
                        <select name="order_status" class="form-select">
                            @foreach(['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned'] as $st)
                                <option value="{{ $st }}" {{ $order->order_status == $st ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Update Master Status</button>
                </form>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Financial Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Items Subtotal</span>
                        <span>₹{{ number_format($order->subtotal, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>GST / Taxes</span>
                        <span>₹{{ number_format($order->tax_amount, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Shipping Fees</span>
                        <span>₹{{ number_format($order->shipping_fee, 2) }}</span>
                    </li>
                    @if($order->discount_amount > 0)
                    <li class="list-group-item d-flex justify-content-between px-0 text-success">
                        <span>Coupon / Offer Discount</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between px-0 fw-bold fs-6 pt-3 border-top">
                        <span>Grand Total Paid</span>
                        <span class="text-primary">₹{{ number_format($order->grand_total, 2) }}</span>
                    </li>
                </ul>

                <hr class="my-3">
                <div class="small">
                    <div>Payment Method: <strong>{{ strtoupper($order->payment_method) }}</strong></div>
                    <div>Payment Status:
                        <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'secondary' }} ms-1">
                            {{ strtoupper($order->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
