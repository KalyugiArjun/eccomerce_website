<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #fff; color: #111; padding: 20px; font-size: 14px; }
        .invoice-card { max-width: 850px; margin: 0 auto; border: 1px solid #ddd; padding: 30px; border-radius: 8px; }
        @media print {
            body { padding: 0; }
            .invoice-card { border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
            Print / Save as PDF
        </button>
    </div>

    <div class="invoice-card">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold mb-0">ShopKart</h2>
                <small class="text-muted">India's Multi-Vendor Marketplace</small>
                <div class="small mt-1">GSTIN: 09SHOPKART1Z9 &bull; marketplace.shopkart.in</div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-uppercase text-secondary mb-1">Tax Invoice</h4>
                <div class="small"><strong>Invoice No:</strong> INV-{{ substr($order->order_number, 4) }}</div>
                <div class="small"><strong>Order No:</strong> {{ $order->order_number }}</div>
                <div class="small"><strong>Date:</strong> {{ $order->placed_at->format('d M, Y') }}</div>
            </div>
        </div>

        <!-- Bill To / Ship To -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase small text-muted">Billed & Shipped To:</h6>
                <div><strong>{{ $order->customer_name }}</strong></div>
                <div>{{ $order->shipping_address['house_no'] ?? '' }}, {{ $order->shipping_address['street'] ?? '' }}</div>
                <div>{{ $order->shipping_address['area'] ?? '' }}</div>
                <div>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} - {{ $order->shipping_address['pincode'] ?? '' }}</div>
                <div>Phone: {{ $order->customer_phone }}</div>
                <div>Email: {{ $order->customer_email }}</div>
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-uppercase small text-muted">Fulfillment Merchant(s):</h6>
                @foreach($order->sellerOrders as $so)
                    <div class="mb-2">
                        <strong>{{ $so->seller->store_name }}</strong><br>
                        <small class="text-muted">{{ $so->seller->city }}, {{ $so->seller->state }} (GST: {{ $so->seller->gst_number ?: 'Unregistered' }})</small>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered mb-4">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>#</th>
                    <th>Product & Merchant</th>
                    <th>SKU</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price (₹)</th>
                    <th class="text-end">Tax (GST)</th>
                    <th class="text-end">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php $n = 1; @endphp
                @foreach($order->sellerOrders as $so)
                    @foreach($so->items as $item)
                        <tr>
                            <td>{{ $n++ }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if($item->variant_details)
                                    <small class="text-muted d-block">{{ $item->variant_details }}</small>
                                @endif
                                <small class="text-secondary d-block">Fulfilled by: {{ $so->seller->store_name }} ({{ $so->sub_order_number }})</small>
                            </td>
                            <td><code>{{ $item->sku }}</code></td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div class="row">
            <div class="col-6">
                <div class="small">
                    <strong>Payment Mode:</strong> {{ strtoupper($order->payment_method) }} (Status: {{ strtoupper($order->payment_status) }})<br>
                    <strong>Transaction Ref:</strong> {{ $order->payment?->transaction_id ?? 'N/A' }}
                </div>
                @if($order->is_gift)
                    <div class="mt-2 p-2 bg-light border rounded small">
                        <strong>Gift Order:</strong> For {{ $order->gift_recipient_name }}
                    </div>
                @endif
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless text-end small">
                    <tr>
                        <td>Items Subtotal:</td>
                        <td class="fw-bold">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total GST (18%):</td>
                        <td class="fw-bold">₹{{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Shipping Fee:</td>
                        <td class="fw-bold">₹{{ number_format($order->shipping_fee, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr class="text-success">
                            <td>Coupon Savings ({{ $order->coupon_code }}):</td>
                            <td class="fw-bold">-₹{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="border-top fs-6">
                        <td><strong>Grand Total:</strong></td>
                        <td><strong>₹{{ number_format($order->grand_total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top text-center text-muted small">
            This is a computer-generated tax invoice issued by ShopKart India Marketplace on behalf of the registered merchants.
        </div>
    </div>
</body>
</html>
