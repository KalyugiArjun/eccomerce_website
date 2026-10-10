<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::user()->seller;

        $query = Inventory::where('seller_id', $seller->id)
            ->with(['product', 'variant']);

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        }

        $inventories = $query->paginate(20)->withQueryString();

        return view('seller.inventory.index', compact('inventories'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $seller = Auth::user()->seller;
        if ($inventory->seller_id !== $seller->id) {
            abort(403);
        }

        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
        ]);

        $inventory->update($request->only('stock_quantity', 'low_stock_threshold'));

        // Sync with product/variant stock
        if ($inventory->variant) {
            $inventory->variant->update(['stock' => $request->stock_quantity]);
        } else {
            $inventory->product->update(['stock' => $request->stock_quantity]);
        }

        return back()->with('success', 'Stock updated successfully.');
    }
}
