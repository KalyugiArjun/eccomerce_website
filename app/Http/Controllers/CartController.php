<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Retrieve or create active cart for user or guest session
     */
    protected function getOrCreateCart(): Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            // If user had a guest session cart, migrate items to user cart
            $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
            if ($guestCart && $guestCart->id !== $cart->id) {
                foreach ($guestCart->items as $item) {
                    $item->update(['cart_id' => $cart->id]);
                }
                $guestCart->delete();
            }
        } else {
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        return $cart->load(['items.product', 'items.seller', 'items.variant']);
    }

    public function index()
    {
        $cart = $this->getOrCreateCart();
        $itemsBySeller = $cart->itemsGroupedBySeller();

        // Check for applied coupon in session
        $couponCode = session('applied_coupon');
        $couponDiscount = 0.0;
        $appliedCoupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon) {
                $validation = $coupon->isValidForAmount($cart->subtotal, Auth::id());
                if ($validation['valid']) {
                    $couponDiscount = $validation['discount'];
                    $appliedCoupon = $coupon;
                } else {
                    session()->forget('applied_coupon');
                }
            }
        }

        $grandTotal = max(0, round($cart->subtotal + $cart->tax_amount + $cart->shipping_fee - $couponDiscount, 2));

        return view('cart.index', compact('cart', 'itemsBySeller', 'appliedCoupon', 'couponDiscount', 'grandTotal'));
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:10',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->integer('quantity', 1);
        $variant = $request->product_variant_id ? ProductVariant::find($request->product_variant_id) : null;

        $price = $variant ? $variant->effective_price : $product->effective_price;
        $stock = $variant ? $variant->stock : $product->stock;

        if ($stock < $quantity) {
            return back()->with('error', "Only {$stock} units available in stock.");
        }

        $cart = $this->getOrCreateCart();

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($stock < $newQuantity) {
                return back()->with('error', "Cannot add more. Reached available stock limit.");
            }
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'seller_id' => $product->seller_id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Added {$product->name} to cart!",
                'cart_count' => $cart->fresh()->items_count,
            ]);
        }

        return redirect()->route('cart.index')->with('success', "Added {$product->name} to your cart!");
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:10']);

        $stock = $cartItem->variant ? $cartItem->variant->stock : $cartItem->product->stock;
        if ($stock < $request->quantity) {
            return back()->with('error', "Only {$stock} units available in stock.");
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function removeItem(CartItem $cartItem)
    {
        $cartItem->delete();
        return back()->with('info', 'Item removed from cart.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $cart = $this->getOrCreateCart();
        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return back()->with('error', 'Invalid coupon code.');
        }

        $validation = $coupon->isValidForAmount($cart->subtotal, Auth::id());

        if (!$validation['valid']) {
            return back()->with('error', $validation['message']);
        }

        session(['applied_coupon' => $coupon->code]);

        return back()->with('success', "Coupon '{$coupon->code}' applied! You saved ₹{$validation['discount']}");
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        return back()->with('info', 'Coupon removed.');
    }
}
