<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SellerResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'brand', 'variants'])
            ->where('status', 'published');

        if ($search = $request->input('q')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if ($cat = $request->input('category')) {
            $query->whereHas('category', fn($c) => $c->where('slug', $cat));
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        $products = $query->latest()->paginate(15);

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = Product::with(['seller', 'category', 'brand', 'variants', 'reviews.user'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return new ProductResource($product);
    }

    public function categories()
    {
        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('subcategories')->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    public function sellers(Request $request)
    {
        $sellers = Seller::where('status', 'approved')->paginate(15);

        return SellerResource::collection($sellers);
    }
}
