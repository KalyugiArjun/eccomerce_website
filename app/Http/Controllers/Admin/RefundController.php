<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = Refund::with(['order.user', 'sellerOrder.seller'])->latest()->paginate(15);
        return view('admin.refunds.index', compact('refunds'));
    }

    public function update(Request $request, Refund $refund)
    {
        $request->validate([
            'status' => 'required|in:admin_approved,rejected,processed',
            'admin_notes' => 'nullable|string',
        ]);

        $refund->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_at' => $request->status === 'processed' ? now() : null,
        ]);

        if ($request->status === 'processed') {
            $refund->order->update(['payment_status' => 'refunded', 'order_status' => 'refunded']);
            $refund->sellerOrder->update(['status' => 'returned']);
        }

        return back()->with('success', "Refund {$refund->refund_number} marked as " . strtoupper($request->status));
    }
}
