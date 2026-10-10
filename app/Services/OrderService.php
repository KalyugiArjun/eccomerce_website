<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Create unified multi-vendor order and split into seller sub-orders
     */
    public function createOrder(User $user, array $data, Cart $cart): Order
    {
        return DB::transaction(function () use ($user, $data, $cart) {
            if ($cart->items->isEmpty()) {
                throw new Exception("Cannot checkout with an empty cart.");
            }

            // 1. Verify stocks
            foreach ($cart->items as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw new Exception("Product '{$item->product->name}' is out of stock or insufficient quantity available.");
                }
            }

            // 2. Calculate Order Totals
            $subtotal = $cart->subtotal;
            $taxAmount = $cart->tax_amount;
            $shippingFee = $cart->shipping_fee;
            $discountAmount = 0.0;
            $appliedCoupon = null;

            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();
                if ($coupon) {
                    $validation = $coupon->isValidForAmount($subtotal, $user->id);
                    if ($validation['valid']) {
                        $discountAmount = $validation['discount'];
                        $appliedCoupon = $coupon;
                    }
                }
            }

            $grandTotal = max(0, round($subtotal + $taxAmount + $shippingFee - $discountAmount, 2));
            $orderNumber = 'ORD-' . strtoupper(Str::random(10));

            // 3. Create Main Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'] ?? $user->name,
                'customer_email' => $data['customer_email'] ?? $user->email,
                'customer_phone' => $data['customer_phone'] ?? $user->phone,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'coupon_code' => $appliedCoupon?->code,
                'shipping_fee' => $shippingFee,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => ($data['payment_method'] ?? 'cod') === 'cod' ? 'pending' : 'processing',
                'order_status' => 'confirmed',
                'is_gift' => !empty($data['is_gift']),
                'gift_recipient_name' => $data['gift_recipient_name'] ?? null,
                'gift_message' => $data['gift_message'] ?? null,
                'gift_wrap' => !empty($data['gift_wrap']),
                'hide_price' => !empty($data['hide_price']),
                'scheduled_delivery_date' => $data['scheduled_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            // Track coupon usage
            if ($appliedCoupon) {
                CouponUsage::create([
                    'coupon_id' => $appliedCoupon->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_applied' => $discountAmount,
                ]);
                $appliedCoupon->increment('used_count');
            }

            // 4. Multi-Vendor Order Splitting by Seller
            $itemsBySeller = $cart->itemsGroupedBySeller();
            $subIndex = 1;

            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                $seller = Seller::findOrFail($sellerId);
                $sellerSubtotal = (float) $sellerItems->sum(fn($i) => $i->price * $i->quantity);
                $sellerTax = (float) $sellerItems->sum(fn($i) => ($i->price * $i->quantity) * (($i->product->tax_percent ?? 18) / 100));
                $sellerShipping = $sellerSubtotal < 499 ? 40.0 : 0.0;
                $sellerDiscount = ($subtotal > 0 && $discountAmount > 0)
                    ? round(($sellerSubtotal / $subtotal) * $discountAmount, 2)
                    : 0.0;
                $sellerGrandTotal = round($sellerSubtotal + $sellerTax + $sellerShipping - $sellerDiscount, 2);

                // Calculate platform commission and net seller earnings
                $commissionCalc = $this->commissionService->calculateCommission($seller, $sellerSubtotal);
                $commissionRate = $commissionCalc['rate'];
                $commissionAmount = $commissionCalc['amount'];
                $sellerNetEarnings = round($sellerGrandTotal - $commissionAmount, 2);

                $subOrderNumber = 'SUB-' . substr($orderNumber, 4) . '-' . chr(64 + $subIndex);
                $subIndex++;

                $sellerOrder = SellerOrder::create([
                    'order_id' => $order->id,
                    'seller_id' => $seller->id,
                    'sub_order_number' => $subOrderNumber,
                    'subtotal' => $sellerSubtotal,
                    'shipping_fee' => $sellerShipping,
                    'tax_amount' => $sellerTax,
                    'discount_amount' => $sellerDiscount,
                    'grand_total' => $sellerGrandTotal,
                    'platform_commission_rate' => $commissionRate,
                    'platform_commission_amount' => $commissionAmount,
                    'seller_net_earnings' => $sellerNetEarnings,
                    'status' => 'confirmed',
                    'estimated_delivery_at' => now()->addMinutes($seller->estimated_delivery_minutes ?: 45),
                ]);

                // Create Order Items and deduct inventory
                foreach ($sellerItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'seller_order_id' => $sellerOrder->id,
                        'seller_id' => $seller->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product->name,
                        'variant_details' => $item->variant ? ($item->variant->color . ' / ' . $item->variant->size) : null,
                        'sku' => $item->variant ? $item->variant->sku : $item->product->sku,
                        'unit_price' => $item->price,
                        'quantity' => $item->quantity,
                        'tax_amount' => round(($item->price * $item->quantity) * (($item->product->tax_percent ?? 18) / 100), 2),
                        'total_price' => round($item->price * $item->quantity, 2),
                    ]);

                    // Deduct product stock
                    $item->product->decrement('stock', $item->quantity);
                    if ($item->variant) {
                        $item->variant->decrement('stock', $item->quantity);
                    }
                }

                // Create Initial Shipment Record with Tracking
                Shipment::create([
                    'seller_order_id' => $sellerOrder->id,
                    'courier_name' => 'ShopKart QuickExpress',
                    'tracking_number' => 'SK-TRK-' . strtoupper(Str::random(10)),
                    'shipped_at' => now(),
                    'estimated_arrival' => now()->addMinutes($seller->estimated_delivery_minutes ?: 45),
                    'status' => 'shipped',
                    'current_location' => $seller->city . ' Hub',
                    'tracking_events' => [
                        [
                            'time' => now()->toDateTimeString(),
                            'status' => 'Order Placed & Confirmed',
                            'location' => $seller->city,
                            'message' => 'Seller accepted and packed the order for dispatch.',
                        ]
                    ],
                ]);

                // Record Commission ledger
                $this->commissionService->recordCommission($sellerOrder);

                // Update seller stats
                $seller->increment('total_orders');
            }

            // 5. Empty Cart
            $cart->items()->delete();

            return $order->load(['sellerOrders.items', 'sellerOrders.seller', 'sellerOrders.shipment', 'payment']);
        });
    }
}
