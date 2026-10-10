<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function getCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $items = $cart->items()->with(['product', 'seller'])->get();

        return response()->json([
            'success' => true,
            'items_count' => $cart->items_count,
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax_amount,
            'shipping' => $cart->shipping_fee,
            'grand_total' => $cart->grand_total,
            'items' => $items,
        ]);
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $product = Product::findOrFail($request->product_id);
        $qty = $request->integer('quantity', 1);

        $item = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ],
            [
                'seller_id' => $product->seller_id,
                'price' => $product->effective_price,
                'quantity' => $qty,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart.',
            'item' => $item,
        ]);
    }

    public function removeItem(Request $request, int $itemId)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->items()->where('id', $itemId)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }
}
