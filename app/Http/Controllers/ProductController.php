<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\RecentlyViewed;
use App\Models\Review;
use App\Models\SearchHistory;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'brand'])
            ->where('status', 'published');

        // 1. Keyword Search
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('short_description', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('brand', fn($b) => $b->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('seller', fn($s) => $s->where('store_name', 'LIKE', "%{$search}%"));
            });

            // Save search history
            if ($search) {
                SearchHistory::create([
                    'user_id' => Auth::id(),
                    'query' => $search,
                    'filters' => $request->only(['category', 'brand', 'min_price', 'max_price', 'rating', 'delivery_time']),
                    'results_count' => 0,
                ]);
            }
        }

        // 2. Category Filter
        if ($categorySlug = $request->input('category')) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $subIds = $category->subcategories->pluck('id')->push($category->id);
                $query->whereIn('category_id', $subIds);
            }
        }

        // 3. Brand Filter
        if ($brandSlug = $request->input('brand')) {
            $query->whereHas('brand', fn($b) => $b->where('slug', $brandSlug));
        }

        // 4. Seller Filter
        if ($sellerSlug = $request->input('seller')) {
            $query->whereHas('seller', fn($s) => $s->where('slug', $sellerSlug));
        }

        // 5. Price Range Filter
        if ($minPrice = $request->input('min_price')) {
            $query->where(function ($q) use ($minPrice) {
                $q->whereNotNull('discount_price')->where('discount_price', '>=', $minPrice)
                  ->orWhere(fn($sq) => $sq->whereNull('discount_price')->where('price', '>=', $minPrice));
            });
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where(function ($q) use ($maxPrice) {
                $q->whereNotNull('discount_price')->where('discount_price', '<=', $maxPrice)
                  ->orWhere(fn($sq) => $sq->whereNull('discount_price')->where('price', '<=', $maxPrice));
            });
        }

        // 6. Rating Filter
        if ($rating = $request->input('rating')) {
            $query->where('rating', '>=', (float)$rating);
        }

        // 7. Delivery Time Filter (Section 9)
        if ($deliveryTime = $request->input('delivery_time')) {
            if ($deliveryTime === '30min') {
                $query->where('delivery_time_minutes', '<=', 30);
            } elseif ($deliveryTime === '1hour') {
                $query->where('delivery_time_minutes', '<=', 60);
            } elseif ($deliveryTime === 'today') {
                $query->where('delivery_time_minutes', '<=', 240);
            }
        }

        // 8. In Stock Filter
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // 9. Sorting
        $sort = $request->input('sort', 'relevance');
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(discount_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(discount_price, price) DESC'),
            'rating' => $query->orderBy('rating', 'desc'),
            'newest' => $query->latest(),
            'fastest_delivery' => $query->orderBy('delivery_time_minutes', 'asc'),
            'best_value' => $query->orderByRaw('(rating * 20) + (100 - (COALESCE(discount_price, price) / 100)) DESC'),
            default => $query->orderBy('is_featured', 'desc')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->whereNull('parent_id')->get();
        $brands = Brand::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function show(string $slug)
    {
        $product = Product::with([
            'seller.documents',
            'category',
            'brand',
            'variants',
            'priceHistories',
            'reviews.user',
            'groupBuyingGroups' => fn($q) => $q->where('status', 'active')->where('expires_at', '>', now())
        ])->where('slug', $slug)->firstOrFail();

        // Increment view count
        $product->increment('views_count');

        // Record in Recently Viewed
        RecentlyViewed::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'product_id' => $product->id,
            ],
            ['viewed_at' => now()]
        );

        // Smart Seller Comparison (Section 7)
        // Find alternative sellers for identical or closely matched products in the same category
        $alternativeSellers = Product::with('seller')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->where('stock', '>', 0)
            ->take(5)
            ->get();

        // Best Value Analysis (Section 26)
        $allOffers = collect([$product])->merge($alternativeSellers);
        $bestValueOffer = $allOffers->sortByDesc(function ($p) {
            // Factor score based on rating, trust score, fast delivery, and price ratio
            $priceScore = max(0, 100 - ($p->effective_price / 100));
            $trustScore = $p->seller->trust_score ?? 90;
            $speedScore = max(0, 100 - ($p->delivery_time_minutes * 1.5));
            return ($p->rating * 20) + ($trustScore * 0.4) + ($speedScore * 0.3) + ($priceScore * 0.3);
        })->first();

        // Related Products
        $relatedProducts = Product::with(['seller', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'alternativeSellers', 'bestValueOffer', 'relatedProducts'));
    }

    public function setPriceAlert(Request $request, Product $product)
    {
        $request->validate([
            'target_price' => 'required|numeric|min:1|lt:' . $product->effective_price,
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Please sign in to set price drop alerts.');
        }

        PriceAlert::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ],
            [
                'target_price' => $request->target_price,
                'is_triggered' => false,
            ]
        );

        return back()->with('success', "Price drop alert set for ₹" . number_format($request->target_price) . "! We will notify you when price drops.");
    }
}
