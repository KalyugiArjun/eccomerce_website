<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'customer')->count();
        $totalSellers = Seller::count();
        $pendingSellers = Seller::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        
        $totalGrossRevenue = (float) Order::where('payment_status', 'paid')->sum('grand_total');
        $totalPlatformCommission = (float) Commission::where('status', 'settled')->sum('commission_amount');
        $pendingRefunds = Refund::where('status', 'pending')->count();

        // Recent 5 Orders
        $recentOrders = Order::with(['user', 'sellerOrders.seller', 'payment'])->latest()->take(5)->get();

        // Pending Seller Approvals
        $pendingSellerList = Seller::with('user')->where('status', 'pending')->latest()->take(5)->get();

        // Monthly Revenue Chart Data (Last 6 Months)
        $monthLabels = [];
        $monthRevenue = [];
        $monthCommission = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabels[] = $month->format('M Y');
            
            $rev = (float) Order::where('payment_status', 'paid')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('grand_total');
            $comm = (float) Commission::where('status', 'settled')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('commission_amount');

            $monthRevenue[] = $rev;
            $monthCommission[] = $comm;
        }

        // Category Sales Breakdown
        $categories = Category::withCount('products')->get();
        $catLabels = $categories->pluck('name')->toArray();
        $catProductCounts = $categories->pluck('products_count')->toArray();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalSellers',
            'pendingSellers',
            'totalProducts',
            'totalOrders',
            'totalGrossRevenue',
            'totalPlatformCommission',
            'pendingRefunds',
            'recentOrders',
            'pendingSellerList',
            'monthLabels',
            'monthRevenue',
            'monthCommission',
            'catLabels',
            'catProductCounts'
        ));
    }
}
