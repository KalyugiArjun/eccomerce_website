<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerOrder;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'sellerOrders.seller', 'payment']);

        if ($search = $request->input('search')) {
            $query->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('order_status', $status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'sellerOrders.items.product', 'sellerOrders.seller', 'sellerOrders.shipment', 'payment', 'refunds']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['order_status' => 'required|in:pending,confirmed,processing,shipped,out_for_delivery,delivered,cancelled,returned']);
        $order->update(['order_status' => $request->order_status]);

        return back()->with('success', "Order #{$order->order_number} status updated to " . strtoupper($request->order_status));
    }
}
