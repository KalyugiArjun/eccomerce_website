<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function show(string $slug)
    {
        $seller = Seller::where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $products = Product::where('seller_id', $seller->id)
            ->where('status', 'published')
            ->latest()
            ->paginate(12);

        $reviews = $seller->reviews()->with('user')->latest()->take(10)->get();

        return view('seller.storefront', compact('seller', 'products', 'reviews'));
    }
}
