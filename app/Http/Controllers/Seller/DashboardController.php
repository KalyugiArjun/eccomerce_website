<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Refund;
use App\Models\SellerOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register');
        }

        // Metrics for this seller ONLY (Seller Isolation)
        $totalOrders = SellerOrder::where('seller_id', $seller->id)->count();
        $pendingOrders = SellerOrder::where('seller_id', $seller->id)->whereIn('status', ['pending', 'confirmed', 'processing'])->count();
        $completedOrders = SellerOrder::where('seller_id', $seller->id)->where('status', 'delivered')->count();
        $grossSales = (float) SellerOrder::where('seller_id', $seller->id)->sum('grand_total');
        $commissionDeducted = (float) SellerOrder::where('seller_id', $seller->id)->sum('platform_commission_amount');
        $netEarnings = (float) SellerOrder::where('seller_id', $seller->id)->sum('seller_net_earnings');
        
        $totalProducts = Product::where('seller_id', $seller->id)->count();
        $lowStockCount = Product::where('seller_id', $seller->id)->where('stock', '<=', 5)->count();
        $refundRequests = Refund::where('seller_order_id', function ($q) use ($seller) {
            $q->select('id')->from('seller_orders')->where('seller_id', $seller->id);
        })->where('status', 'pending')->count();

        // Recent 5 Sub-Orders
        $recentOrders = SellerOrder::with(['order.user', 'items'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->take(5)
            ->get();

        // Weekly Sales Chart Data (Last 7 Days)
        $chartLabels = [];
        $chartSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $chartLabels[] = $day->format('D, d M');
            $dayTotal = (float) SellerOrder::where('seller_id', $seller->id)
                ->whereDate('created_at', $day->toDateString())
                ->sum('grand_total');
            $chartSales[] = $dayTotal;
        }

        return view('seller.dashboard', compact(
            'seller',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'grossSales',
            'commissionDeducted',
            'netEarnings',
            'totalProducts',
            'lowStockCount',
            'refundRequests',
            'recentOrders',
            'chartLabels',
            'chartSales'
        ));
    }

    public function settings()
    {
        $seller = Auth::user()->seller;
        return view('seller.settings', compact('seller'));
    }

    public function updateSettings(Request $request)
    {
        $seller = Auth::user()->seller;

        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_tagline' => 'nullable|string|max:255',
            'phone' => 'required|string|regex:/^[6-9]\d{9}$/',
            'business_email' => 'required|email|max:255',
            'business_address' => 'required|string',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|regex:/^[1-9][0-9]{5}$/',
            'delivery_radius_km' => 'required|numeric|min:1|max:100',
            'estimated_delivery_minutes' => 'required|integer|min:10|max:300',
            'local_delivery_available' => 'nullable|boolean',
            'return_policy' => 'nullable|string',
            'about_store' => 'nullable|string',
            'store_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'store_banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = $request->except(['store_logo', 'store_banner']);

        if ($request->hasFile('store_logo')) {
            $data['store_logo'] = '/storage/' . $request->file('store_logo')->store('sellers/logos', 'public');
        }
        if ($request->hasFile('store_banner')) {
            $data['store_banner'] = '/storage/' . $request->file('store_banner')->store('sellers/banners', 'public');
        }

        $seller->update($data);

        return back()->with('success', 'Store settings and delivery configuration updated successfully!');
    }
}
