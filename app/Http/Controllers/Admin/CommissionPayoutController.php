<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\SellerPayout;
use Illuminate\Http\Request;

class CommissionPayoutController extends Controller
{
    public function index()
    {
        $commissions = Commission::with(['seller', 'sellerOrder'])->latest()->paginate(15);
        $payouts = SellerPayout::with('seller')->latest()->paginate(15);
        $totalCommissionEarned = (float) Commission::where('status', 'settled')->sum('commission_amount');
        $totalPaidOut = (float) SellerPayout::where('status', 'paid')->sum('net_amount');
        $pendingPayoutsCount = SellerPayout::where('status', 'pending')->count();

        return view('admin.commissions.index', compact(
            'commissions',
            'payouts',
            'totalCommissionEarned',
            'totalPaidOut',
            'pendingPayoutsCount'
        ));
    }

    public function processPayout(Request $request, SellerPayout $payout)
    {
        $request->validate([
            'status' => 'required|in:processing,paid,failed',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payout->update([
            'status' => $request->status,
            'transaction_reference' => $request->transaction_reference,
            'notes' => $request->notes,
            'processed_at' => $request->status === 'paid' ? now() : null,
        ]);

        return back()->with('success', "Payout {$payout->payout_number} marked as " . strtoupper($request->status));
    }
}
