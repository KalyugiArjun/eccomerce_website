<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function getOrders(Request $request)
    {
        $orders = Order::with(['sellerOrders.items', 'sellerOrders.seller', 'payment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function getOrder(Request $request, string $orderNumber)
    {
        $order = Order::with(['sellerOrders.items', 'sellerOrders.seller', 'payment'])
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return new OrderResource($order);
    }

    public function getWishlist(Request $request)
    {
        $wishlist = Wishlist::with('items.product')->firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json([
            'success' => true,
            'wishlist' => $wishlist,
        ]);
    }

    public function getNotifications(Request $request)
    {
        return response()->json([
            'success' => true,
            'notifications' => $request->user()->notifications,
        ]);
    }
}
