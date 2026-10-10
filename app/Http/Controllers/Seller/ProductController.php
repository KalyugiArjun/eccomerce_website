<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\PriceAlert;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::user()->seller;

        $query = Product::where('seller_id', $seller->id)
            ->with(['category', 'variants']);

        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%")->orWhere('sku', 'LIKE', "%{$search}%");
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('seller.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('subcategories')->get();
        $brands = Brand::where('is_active', true)->get();

        return view('seller.products.create', compact('categories', 'brands'));
    }

    public function store(ProductStoreRequest $request)
    {
        $seller = Auth::user()->seller;

        return DB::transaction(function () use ($request, $seller) {
            $data = $request->validated();
            $data['seller_id'] = $seller->id;
            $data['slug'] = Str::slug($data['name']) . '-' . rand(1000, 9999);
            $data['status'] = 'published';

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = '/storage/' . $request->file('thumbnail')->store('products/thumbnails', 'public');
            }

            $product = Product::create($data);

            // Handle Product Variants if provided
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $v['sku'],
                        'color' => $v['color'] ?? null,
                        'size' => $v['size'] ?? null,
                        'price' => $v['price'],
                        'discount_price' => $v['discount_price'] ?? null,
                        'stock' => $v['stock'] ?? 0,
                    ]);

                    Inventory::create([
                        'seller_id' => $seller->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'sku' => $variant->sku,
                        'stock_quantity' => $variant->stock,
                    ]);
                }
            } else {
                Inventory::create([
                    'seller_id' => $seller->id,
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock,
                ]);
            }

            // Initial Price History entry
            PriceHistory::create([
                'product_id' => $product->id,
                'old_price' => $product->price,
                'new_price' => $product->effective_price,
                'reason' => 'Initial product launch price',
            ]);

            return redirect()->route('seller.products.index')->with('success', "Product '{$product->name}' created successfully!");
        });
    }

    public function edit(Product $product)
    {
        $seller = Auth::user()->seller;
        if ($product->seller_id !== $seller->id) {
            abort(403, 'Unauthorized. You can only manage your own products.');
        }

        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('subcategories')->get();
        $brands = Brand::where('is_active', true)->get();

        return view('seller.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(ProductStoreRequest $request, Product $product)
    {
        $seller = Auth::user()->seller;
        if ($product->seller_id !== $seller->id) {
            abort(403, 'Unauthorized.');
        }

        return DB::transaction(function () use ($request, $product) {
            $oldEffectivePrice = $product->effective_price;
            $data = $request->validated();

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = '/storage/' . $request->file('thumbnail')->store('products/thumbnails', 'public');
            }

            $product->update($data);
            $newEffectivePrice = $product->effective_price;

            // If price changed, record in PriceHistory (Section 20)
            if ($oldEffectivePrice != $newEffectivePrice) {
                PriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $oldEffectivePrice,
                    'new_price' => $newEffectivePrice,
                    'reason' => 'Seller updated pricing',
                ]);

                // Check and trigger Price Drop Alerts (Section 21)
                $alerts = PriceAlert::where('product_id', $product->id)
                    ->where('is_triggered', false)
                    ->where('target_price', '>=', $newEffectivePrice)
                    ->get();

                foreach ($alerts as $alert) {
                    $alert->update([
                        'is_triggered' => true,
                        'triggered_at' => now(),
                    ]);
                }
            }

            return redirect()->route('seller.products.index')->with('success', "Product '{$product->name}' updated successfully!");
        });
    }

    public function destroy(Product $product)
    {
        $seller = Auth::user()->seller;
        if ($product->seller_id !== $seller->id) {
            abort(403, 'Unauthorized.');
        }

        $product->delete();
        return redirect()->route('seller.products.index')->with('success', 'Product deleted.');
    }
}
