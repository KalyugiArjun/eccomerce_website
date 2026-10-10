<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Refund;
use App\Models\Review;
use App\Models\SellerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['sellerOrders.items.product', 'sellerOrders.seller', 'sellerOrders.shipment', 'payment'])
            ->where('user_id', Auth::id())
            ->latest('placed_at')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(string $orderNumber)
    {
        $order = Order::with([
            'sellerOrders.items.product',
            'sellerOrders.items.variant',
            'sellerOrders.seller',
            'sellerOrders.shipment',
            'payment',
            'refunds'
        ])
        ->where('order_number', $orderNumber)
        ->where('user_id', Auth::id())
        ->firstOrFail();

        return view('customer.orders.show', compact('order'));
    }

    public function invoice(string $orderNumber)
    {
        $order = Order::with(['sellerOrders.items', 'sellerOrders.seller', 'payment'])
            ->where('order_number', $orderNumber)
            ->where(function ($q) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('user_id', Auth::id());
                }
            })
            ->firstOrFail();

        return view('customer.orders.invoice', compact('order'));
    }

    public function requestRefund(Request $request, string $orderNumber)
    {
        $request->validate([
            'seller_order_id' => 'required|exists:seller_orders,id',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $order = Order::where('order_number', $orderNumber)->where('user_id', Auth::id())->firstOrFail();
        $sellerOrder = SellerOrder::where('id', $request->seller_order_id)->where('order_id', $order->id)->firstOrFail();

        $existing = Refund::where('seller_order_id', $sellerOrder->id)->where('status', 'pending')->first();
        if ($existing) {
            return back()->with('warning', 'A return request is already pending review for this sub-order.');
        }

        Refund::create([
            'order_id' => $order->id,
            'seller_order_id' => $sellerOrder->id,
            'user_id' => Auth::id(),
            'refund_number' => 'REF-' . strtoupper(Str::random(10)),
            'amount' => $sellerOrder->grand_total,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $sellerOrder->update(['status' => 'returned']);

        return back()->with('success', 'Return & refund request submitted. The seller and administrator have been notified.');
    }

    public function storeReview(Request $request, string $orderNumber)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'seller_id' => 'required|exists:sellers,id',
            'rating' => 'required|integer|between:1,5',
            'headline' => 'nullable|string|max:255',
            'comment' => 'required|string|min:10|max:2000',
        ]);

        $order = Order::where('order_number', $orderNumber)->where('user_id', Auth::id())->firstOrFail();

        // Check if verified purchase
        $isPurchased = $order->items()->where('product_id', $request->product_id)->exists();

        Review::create([
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'seller_id' => $request->seller_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'headline' => $request->headline,
            'comment' => $request->comment,
            'is_verified_purchase' => $isPurchased,
            'status' => 'approved',
        ]);

        // Recalculate product rating
        $product = \App\Models\Product::find($request->product_id);
        if ($product) {
            $product->rating = round($product->reviews()->avg('rating') ?: 5.0, 2);
            $product->total_reviews = $product->reviews()->count();
            $product->save();
        }

        return back()->with('success', 'Thank you! Your verified product review has been published.');
    }
}
