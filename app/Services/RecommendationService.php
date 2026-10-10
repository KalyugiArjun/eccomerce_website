<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\RecentlyViewed;
use App\Models\SearchHistory;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

class RecommendationService
{
    /**
     * Get personalized product recommendations for a user
     */
    public function getRecommendations(?User $user, int $limit = 8): Collection
    {
        if (!$user) {
            // Guest fallback: Trending and top rated products
            return Product::with(['seller', 'category'])
                ->where('status', 'published')
                ->where('stock', '>', 0)
                ->orderBy('views_count', 'desc')
                ->orderBy('rating', 'desc')
                ->take($limit)
                ->get();
        }

        // 1. Collect category preferences from user's history
        $categoryIds = [];

        // A. Recently viewed categories
        $recentProductIds = RecentlyViewed::where('user_id', $user->id)
            ->latest('viewed_at')
            ->take(10)
            ->pluck('product_id');

        $viewedCategories = Product::whereIn('id', $recentProductIds)->pluck('category_id')->toArray();
        $categoryIds = array_merge($categoryIds, $viewedCategories);

        // B. Wishlist categories
        $wishlistProductIds = WishlistItem::whereHas('wishlist', fn($q) => $q->where('user_id', $user->id))
            ->pluck('product_id');
        $wishlistCategories = Product::whereIn('id', $wishlistProductIds)->pluck('category_id')->toArray();
        $categoryIds = array_merge($categoryIds, $wishlistCategories);

        // C. Past purchased categories
        $purchasedProductIds = Order::where('user_id', $user->id)
            ->with('items')
            ->get()
            ->pluck('items.*.product_id')
            ->flatten()
            ->unique();
        $purchasedCategories = Product::whereIn('id', $purchasedProductIds)->pluck('category_id')->toArray();
        $categoryIds = array_merge($categoryIds, $purchasedCategories);

        $categoryIds = array_unique(array_filter($categoryIds));

        $query = Product::with(['seller', 'category'])
            ->where('status', 'published')
            ->where('stock', '>', 0);

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        // Exclude products already bought or in recent views to give fresh suggestions
        if ($recentProductIds->isNotEmpty()) {
            $query->whereNotIn('id', $recentProductIds->take(4));
        }

        $recommendations = $query->orderBy('rating', 'desc')
            ->orderBy('is_featured', 'desc')
            ->take($limit)
            ->get();

        // Fallback if not enough matching categories
        if ($recommendations->count() < $limit) {
            $fallback = Product::with(['seller', 'category'])
                ->where('status', 'published')
                ->where('stock', '>', 0)
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->orderBy('views_count', 'desc')
                ->take($limit - $recommendations->count())
                ->get();

            $recommendations = $recommendations->merge($fallback);
        }

        return $recommendations;
    }
}
