<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\SellerOrder;
use App\Models\SellerPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    public function index()
    {
        $seller = Auth::user()->seller;

        $grossSales = (float) SellerOrder::where('seller_id', $seller->id)->where('status', 'delivered')->sum('grand_total');
        $commissionDeducted = (float) SellerOrder::where('seller_id', $seller->id)->where('status', 'delivered')->sum('platform_commission_amount');
        $refundsDeducted = (float) Refund::where('seller_order_id', function ($q) use ($seller) {
            $q->select('id')->from('seller_orders')->where('seller_id', $seller->id);
        })->where('status', 'processed')->sum('amount');

        $totalEarned = max(0, round($grossSales - $commissionDeducted - $refundsDeducted, 2));
        $alreadyPaidOut = (float) SellerPayout::where('seller_id', $seller->id)->where('status', 'paid')->sum('net_amount');
        $pendingPayout = (float) SellerPayout::where('seller_id', $seller->id)->whereIn('status', ['pending', 'processing'])->sum('net_amount');
        $availableForPayout = max(0, round($totalEarned - $alreadyPaidOut - $pendingPayout, 2));

        $payouts = SellerPayout::where('seller_id', $seller->id)->latest()->paginate(10);

        return view('seller.payouts.index', compact(
            'seller',
            'grossSales',
            'commissionDeducted',
            'refundsDeducted',
            'totalEarned',
            'alreadyPaidOut',
            'availableForPayout',
            'payouts'
        ));
    }

    public function requestPayout(Request $request)
    {
        $seller = Auth::user()->seller;

        $grossSales = (float) SellerOrder::where('seller_id', $seller->id)->where('status', 'delivered')->sum('grand_total');
        $commissionDeducted = (float) SellerOrder::where('seller_id', $seller->id)->where('status', 'delivered')->sum('platform_commission_amount');
        $refundsDeducted = (float) Refund::where('seller_order_id', function ($q) use ($seller) {
            $q->select('id')->from('seller_orders')->where('seller_id', $seller->id);
        })->where('status', 'processed')->sum('amount');

        $totalEarned = max(0, round($grossSales - $commissionDeducted - $refundsDeducted, 2));
        $alreadyPaidOut = (float) SellerPayout::where('seller_id', $seller->id)->where('status', 'paid')->sum('net_amount');
        $pendingPayout = (float) SellerPayout::where('seller_id', $seller->id)->whereIn('status', ['pending', 'processing'])->sum('net_amount');
        $availableForPayout = max(0, round($totalEarned - $alreadyPaidOut - $pendingPayout, 2));

        if ($availableForPayout < 500) {
            return back()->with('error', 'Minimum payout request amount is ₹500. Your available balance is ₹' . number_format($availableForPayout, 2));
        }

        if (empty($seller->bank_account_number) || empty($seller->bank_ifsc)) {
            return back()->with('error', 'Please configure your bank details in Store Settings before requesting a payout.');
        }

        SellerPayout::create([
            'seller_id' => $seller->id,
            'payout_number' => 'PO-' . strtoupper(Str::random(10)),
            'gross_sales' => $grossSales,
            'commission_deducted' => $commissionDeducted,
            'refunds_deducted' => $refundsDeducted,
            'net_amount' => $availableForPayout,
            'bank_details_snapshot' => [
                'bank_name' => $seller->bank_name,
                'account_name' => $seller->bank_account_name,
                'account_number' => $seller->bank_account_number,
                'ifsc' => $seller->bank_ifsc,
            ],
            'status' => 'pending',
            'notes' => 'Payout requested by seller.',
        ]);

        return back()->with('success', 'Payout request of ₹' . number_format($availableForPayout, 2) . ' submitted to admin for processing.');
    }
}
