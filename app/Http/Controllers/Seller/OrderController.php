<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::user()->seller;

        $query = SellerOrder::where('seller_id', $seller->id)
            ->with(['order.user', 'items.product', 'shipment']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('seller.orders.index', compact('orders'));
    }

    public function show(string $subOrderNumber)
    {
        $seller = Auth::user()->seller;

        $sellerOrder = SellerOrder::where('sub_order_number', $subOrderNumber)
            ->where('seller_id', $seller->id)
            ->with(['order.user', 'items.product', 'shipment', 'commission'])
            ->firstOrFail();

        return view('seller.orders.show', compact('sellerOrder'));
    }

    public function updateStatus(Request $request, string $subOrderNumber)
    {
        $seller = Auth::user()->seller;

        $sellerOrder = SellerOrder::where('sub_order_number', $subOrderNumber)
            ->where('seller_id', $seller->id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:confirmed,processing,shipped,out_for_delivery,delivered,cancelled',
            'courier_name' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $sellerOrder->update([
            'status' => $request->status,
            'courier_name' => $request->courier_name ?: $sellerOrder->courier_name,
            'tracking_number' => $request->tracking_number ?: $sellerOrder->tracking_number,
            'delivered_at' => $request->status === 'delivered' ? now() : $sellerOrder->delivered_at,
        ]);

        // If sub-order is marked delivered, update seller completed_orders and on_time_deliveries
        if ($request->status === 'delivered') {
            $seller->increment('completed_orders');
            $seller->increment('on_time_deliveries');
            $seller->trust_score = $seller->calculateTrustScore();
            $seller->save();
        }

        // Update shipment tracking event
        if ($sellerOrder->shipment) {
            $events = $sellerOrder->shipment->tracking_events ?: [];
            $events[] = [
                'time' => now()->toDateTimeString(),
                'status' => ucfirst(str_replace('_', ' ', $request->status)),
                'location' => $seller->city,
                'message' => "Order marked as {$request->status} by store manager.",
            ];

            $sellerOrder->shipment->update([
                'status' => in_array($request->status, ['shipped', 'out_for_delivery', 'delivered']) ? $request->status : 'in_transit',
                'tracking_events' => $events,
            ]);
        }

        return back()->with('success', "Sub-order {$sellerOrder->sub_order_number} status updated to " . strtoupper($request->status));
    }
}
