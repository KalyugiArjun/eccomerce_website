@extends('layouts.app')

@section('title', 'Order ' . $order->order_number . ' - ShopKart')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-1">Order #{{ $order->order_number }}</h2>
            <small class="text-muted">Placed on {{ $order->placed_at->format('d M Y, h:i A') }} &bull; Payment via {{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i> Tax Invoice
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary btn-sm">
                &larr; Back to Orders
            </a>
        </div>
    </div>

    <!-- Gift Notification Card if applicable -->
    @if($order->is_gift)
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-4 py-3">
            <i class="bi bi-gift-fill fs-3 text-danger"></i>
            <div>
                <strong>Special Gift Order for {{ $order->gift_recipient_name }}:</strong>
                @if($order->gift_message)
                    <div class="small fst-italic">"{{ $order->gift_message }}"</div>
                @endif
                <small class="text-muted">Premium gift wrap applied & price hidden from the delivery receipt.</small>
            </div>
        </div>
    @endif

    <div class="row gy-4 mb-5">
        <!-- Sub-Orders & Tracking -->
        <div class="col-lg-8">
            <h5 class="fw-bold mb-3"><i class="bi bi-boxes text-primary me-2"></i> Multi-Vendor Order Packages</h5>

            @foreach($order->sellerOrders as $subOrder)
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="fw-bold mb-0">Package from {{ $subOrder->seller->store_name }}</h6>
                            <small class="text-muted">Sub-Order: <code>{{ $subOrder->sub_order_number }}</code> &bull; <i class="bi bi-geo-alt"></i> {{ $subOrder->seller->city }}</small>
                        </div>
                        <span class="badge {{ $subOrder->status_badge_class }} fs-6">{{ ucfirst($subOrder->status) }}</span>
                    </div>

                    <!-- Visual Delivery Timeline (Section 33) -->
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <div class="d-flex justify-content-between text-center small position-relative">
                            @php
                                $statuses = ['confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered'];
                                $currentIdx = array_search($subOrder->status, $statuses);
                                if ($currentIdx === false) $currentIdx = 0;
                            @endphp

                            @foreach($statuses as $idx => $st)
                                <div class="flex-grow-1 position-relative">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 {{ $idx <= $currentIdx ? 'bg-success text-white' : 'bg-secondary bg-opacity-25 text-muted' }}" style="width: 32px; height: 32px;">
                                        <i class="bi {{ $idx <= $currentIdx ? 'bi-check-lg' : 'bi-circle' }}"></i>
                                    </div>
                                    <div class="fw-semibold text-capitalize" style="font-size: 0.75rem;">
                                        {{ str_replace('_', ' ', $st) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($subOrder->shipment && !empty($subOrder->shipment->tracking_events))
                            <div class="mt-3 pt-3 border-top small text-muted">
                                <strong>Live Courier Updates:</strong>
                                @foreach(array_slice($subOrder->shipment->tracking_events, -2) as $event)
                                    <div>&bull; <span class="fw-bold">{{ $event['status'] }}</span> ({{ $event['location'] }}): {{ $event['message'] }} <span class="text-secondary">({{ $event['time'] }})</span></div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Line Items in this sub-order -->
                    <div class="mb-3">
                        @foreach($subOrder->items as $item)
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $item->product_name }}</h6>
                                    @if($item->variant_details)
                                        <small class="text-muted">{{ $item->variant_details }}</small>
                                    @endif
                                    <div class="small text-muted">Qty: {{ $item->quantity }} &bull; Unit Price: ₹{{ number_format($item->unit_price) }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark">₹{{ number_format($item->total_price) }}</div>
                                    <button class="btn btn-link btn-sm text-decoration-none p-0 text-warning" data-bs-toggle="modal" data-bs-target="#reviewModal_{{ $item->product_id }}">
                                        ★ Rate & Review
                                    </button>
                                </div>
                            </div>

                            <!-- Review Modal for this product -->
                            <div class="modal fade" id="reviewModal_{{ $item->product_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title">Write a Verified Review</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('orders.review', $order->order_number) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                            <input type="hidden" name="seller_id" value="{{ $subOrder->seller_id }}">
                                            <div class="modal-body p-4">
                                                <h6>{{ $item->product_name }}</h6>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Overall Rating (1 to 5 Stars)</label>
                                                    <select name="rating" class="form-select">
                                                        <option value="5">★★★★★ (5/5) - Excellent</option>
                                                        <option value="4">★★★★☆ (4/5) - Very Good</option>
                                                        <option value="3">★★★☆☆ (3/5) - Average</option>
                                                        <option value="2">★★☆☆☆ (2/5) - Poor</option>
                                                        <option value="1">★☆☆☆☆ (1/5) - Terrible</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Review Headline</label>
                                                    <input type="text" name="headline" class="form-control form-control-sm" placeholder="e.g. Superb local delivery and original quality!">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Detailed Feedback</label>
                                                    <textarea name="comment" class="form-control form-control-sm" rows="3" required placeholder="Tell other Indian shoppers what you liked or how this seller performed..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sk-primary btn-sm px-4">Submit Review</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <small class="text-muted">Package Subtotal: ₹{{ number_format($subOrder->grand_total) }}</small>
                        <!-- Return / Refund Request Button (Section 34) -->
                        @if($subOrder->status !== 'returned')
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#refundModal_{{ $subOrder->id }}">
                                <i class="bi bi-arrow-return-left me-1"></i> Request Return / Refund
                            </button>

                            <!-- Return Modal -->
                            <div class="modal fade" id="refundModal_{{ $subOrder->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i> Return & Refund Request</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('orders.refund', $order->order_number) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="seller_order_id" value="{{ $subOrder->id }}">
                                            <div class="modal-body p-4">
                                                <p class="text-muted small">You are requesting a return for package <strong>{{ $subOrder->sub_order_number }}</strong> from <strong>{{ $subOrder->seller->store_name }}</strong> for ₹{{ number_format($subOrder->grand_total) }}.</p>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Reason for Return</label>
                                                    <select class="form-select form-select-sm mb-2" onchange="document.getElementById('reasonBox_{{ $subOrder->id }}').value = this.value + ': '">
                                                        <option value="Damaged item received">Damaged item received</option>
                                                        <option value="Defective or not working as expected">Defective or not working as expected</option>
                                                        <option value="Item different from description">Item different from description</option>
                                                        <option value="Size or color mismatch">Size or color mismatch</option>
                                                        <option value="Missing parts or accessories">Missing parts or accessories</option>
                                                    </select>
                                                    <textarea id="reasonBox_{{ $subOrder->id }}" name="reason" class="form-control form-control-sm" rows="3" required placeholder="Please provide specific details regarding why you want to return this product..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger btn-sm px-4">Submit Return Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="badge bg-danger">Return Requested / Processing</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Order Summary & Shipping Address Sidebar -->
        <div class="col-lg-4">
            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt text-primary me-2"></i> Shipping Address</h6>
                <div class="small text-dark">
                    <strong>{{ $order->shipping_address['name'] ?? $order->customer_name }}</strong><br>
                    {{ $order->shipping_address['house_no'] ?? '' }}, {{ $order->shipping_address['street'] ?? '' }}<br>
                    {{ $order->shipping_address['area'] ?? '' }}<br>
                    {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} - {{ $order->shipping_address['pincode'] ?? '' }}<br>
                    Phone: {{ $order->shipping_address['phone'] ?? $order->customer_phone }}
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h6 class="fw-bold mb-3">Order Financial Summary</h6>
                <div class="d-flex justify-content-between text-muted mb-2 small">
                    <span>Items Subtotal:</span>
                    <span class="text-dark">₹{{ number_format($order->subtotal) }}</span>
                </div>
                <div class="d-flex justify-content-between text-muted mb-2 small">
                    <span>Tax (GST):</span>
                    <span class="text-dark">₹{{ number_format($order->tax_amount) }}</span>
                </div>
                <div class="d-flex justify-content-between text-muted mb-2 small">
                    <span>Shipping Fee:</span>
                    <span class="text-dark">{{ $order->shipping_fee == 0 ? 'FREE' : '₹' . number_format($order->shipping_fee) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between text-success mb-2 small fw-bold">
                        <span>Discount ({{ $order->coupon_code }}):</span>
                        <span>-₹{{ number_format($order->discount_amount) }}</span>
                    </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5 mb-0">
                    <span>Grand Total:</span>
                    <span class="text-dark">₹{{ number_format($order->grand_total) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
