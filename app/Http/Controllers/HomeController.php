<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GroupBuyingGroup;
use App\Models\Product;
use App\Models\Seller;
use App\Services\RecommendationService;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected RecommendationService $recommendationService;
    protected SellerService $sellerService;

    public function __construct(RecommendationService $recommendationService, SellerService $sellerService)
    {
        $this->recommendationService = $recommendationService;
        $this->sellerService = $sellerService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Customer location (defaults to Lucknow coordinates 26.8467, 80.9462 if not set)
        $userLat = $request->cookie('shopkart_lat', 26.8467);
        $userLng = $request->cookie('shopkart_lng', 80.9462);
        $userCity = $user?->preferred_city ?? $request->cookie('shopkart_city', 'Lucknow');
        $userPincode = $user?->preferred_pincode ?? $request->cookie('shopkart_pincode', '226001');

        // 1. Featured Categories
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->take(10)
            ->get();

        // 2. Nearby Sellers
        $nearbySellers = $this->sellerService->getNearbySellers((float)$userLat, (float)$userLng, 30.0)->take(6);

        // 3. Fast Delivery Products (<45 min delivery)
        $fastDeliveryProducts = Product::with(['seller', 'category'])
            ->where('status', 'published')
            ->where('is_fast_delivery', true)
            ->where('stock', '>', 0)
            ->orderBy('delivery_time_minutes', 'asc')
            ->take(8)
            ->get();

        // 4. Today's Deals (Significant discounts)
        $todaysDeals = Product::with(['seller', 'category'])
            ->where('status', 'published')
            ->whereNotNull('discount_price')
            ->where('stock', '>', 0)
            ->orderByRaw('((price - discount_price) / price) DESC')
            ->take(8)
            ->get();

        // 5. Group Buying Deals
        $groupBuyingGroups = GroupBuyingGroup::with('product.seller')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->take(4)
            ->get();

        // 6. Price Drops (Products with recent price decrease)
        $priceDrops = Product::with(['seller', 'category', 'priceHistories'])
            ->where('status', 'published')
            ->whereHas('priceHistories', function ($q) {
                $q->whereColumn('new_price', '<', 'old_price');
            })
            ->take(8)
            ->get();

        // 7. Personalized Recommendations
        $recommendations = $this->recommendationService->getRecommendations($user, 8);

        // 8. Trending Products
        $trendingProducts = Product::with(['seller', 'category'])
            ->where('status', 'published')
            ->where('stock', '>', 0)
            ->orderBy('views_count', 'desc')
            ->take(8)
            ->get();

        return view('home', compact(
            'categories',
            'nearbySellers',
            'fastDeliveryProducts',
            'todaysDeals',
            'groupBuyingGroups',
            'priceDrops',
            'recommendations',
            'trendingProducts',
            'userCity',
            'userPincode'
        ));
    }

    public function setLocation(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|regex:/^[1-9][0-9]{5}$/',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (Auth::check()) {
            Auth::user()->update([
                'preferred_city' => $request->city,
                'preferred_pincode' => $request->pincode,
            ]);
        }

        return redirect()->back()
            ->withCookie(cookie('shopkart_city', $request->city, 60 * 24 * 30))
            ->withCookie(cookie('shopkart_pincode', $request->pincode, 60 * 24 * 30))
            ->withCookie(cookie('shopkart_lat', $request->latitude ?: 26.8467, 60 * 24 * 30))
            ->withCookie(cookie('shopkart_lng', $request->longitude ?: 80.9462, 60 * 24 * 30))
            ->with('success', "Location updated to {$request->city} - {$request->pincode}");
    }
}
