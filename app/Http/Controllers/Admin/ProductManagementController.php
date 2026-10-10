<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'brand']);

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%")->orWhere('sku', 'LIKE', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return back()->with('success', "Updated featured status for '{$product->name}'");
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate(['status' => 'required|in:published,draft,pending,rejected']);
        $product->update(['status' => $request->status]);

        return back()->with('success', "Product status updated to {$request->status}");
    }
}
