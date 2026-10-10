@extends('layouts.seller')

@section('page_title', 'Sub-Order ' . $sellerOrder->sub_order_number)

@section('content')
<div class="row gy-4">
    <!-- Sub-Order Details & Status Update -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0">Sub-Order: <code>{{ $sellerOrder->sub_order_number }}</code></h5>
                    <small class="text-muted">Parent Order: {{ $sellerOrder->order->order_number }} &bull; Placed on {{ $sellerOrder->created_at->format('d M Y, h:i A') }}</small>
                </div>
                <span class="badge {{ $sellerOrder->status_badge_class }} fs-6">{{ ucfirst($sellerOrder->status) }}</span>
            </div>

            <!-- Status & Courier Update Form -->
            <form action="{{ route('seller.orders.update-status', $sellerOrder->sub_order_number) }}" method="POST" class="p-3 bg-light rounded-3 border mb-4">
                @csrf
                <h6 class="fw-bold mb-3"><i class="bi bi-truck text-primary me-2"></i> Update Package Dispatch & Delivery Status</h6>

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Order Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="confirmed" {{ $sellerOrder->status === 'confirmed' ? 'selected' : '' }}>Confirmed (Order Accepted)</option>
                            <option value="processing" {{ $sellerOrder->status === 'processing' ? 'selected' : '' }}>Processing (Packing Package)</option>
                            <option value="shipped" {{ $sellerOrder->status === 'shipped' ? 'selected' : '' }}>Shipped (Handed to Local Rider)</option>
                            <option value="out_for_delivery" {{ $sellerOrder->status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                            <option value="delivered" {{ $sellerOrder->status === 'delivered' ? 'selected' : '' }}>Delivered (Customer Received)</option>
                            <option value="cancelled" {{ $sellerOrder->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Courier Name</label>
                        <input type="text" name="courier_name" class="form-control form-control-sm" value="{{ old('courier_name', $sellerOrder->courier_name ?: 'ShopKart Local Express') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control form-control-sm" value="{{ old('tracking_number', $sellerOrder->tracking_number) }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sk-primary btn-sm w-100 fw-bold">Update</button>
                    </div>
                </div>
            </form>

            <!-- Line Items -->
            <h6 class="fw-bold mb-3">Items in this Package:</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light small">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sellerOrder->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    @if($item->variant_details)
                                        <small class="text-muted d-block">{{ $item->variant_details }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $item->sku }}</code></td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->total_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer & Financial Breakdown Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Destination -->
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt text-danger me-2"></i> Delivery Destination</h6>
            <div class="small text-dark">
                <strong>{{ $sellerOrder->order->customer_name }}</strong><br>
                {{ $sellerOrder->order->shipping_address['house_no'] ?? '' }}, {{ $sellerOrder->order->shipping_address['street'] ?? '' }}<br>
                {{ $sellerOrder->order->shipping_address['area'] ?? '' }}<br>
                {{ $sellerOrder->order->shipping_address['city'] ?? '' }} - {{ $sellerOrder->order->shipping_address['pincode'] ?? '' }}<br>
                <div class="mt-2 text-primary"><i class="bi bi-telephone"></i> {{ $sellerOrder->order->customer_phone }}</div>
            </div>
        </div>

        <!-- Package Net Earnings -->
        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-bold mb-3">Sub-Order Financials</h6>
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Items Subtotal:</span>
                <span class="text-dark">₹{{ number_format($sellerOrder->subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>GST Tax (18%):</span>
                <span class="text-dark">₹{{ number_format($sellerOrder->tax_amount) }}</span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Local Shipping:</span>
                <span class="text-dark">₹{{ number_format($sellerOrder->shipping_fee) }}</span>
            </div>
            <div class="d-flex justify-content-between text-danger small mb-2 fw-semibold">
                <span>Platform Commission ({{ $sellerOrder->platform_commission_rate }}%):</span>
                <span>-₹{{ number_format($sellerOrder->platform_commission_amount) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between text-success fw-bold fs-5">
                <span>Your Net Share:</span>
                <span>₹{{ number_format($sellerOrder->seller_net_earnings) }}</span>
            </div>
            <small class="text-muted mt-2 d-block">Credited to your payout ledger once delivered.</small>
        </div>
    </div>
</div>
@endsection
