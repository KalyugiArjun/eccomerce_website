<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => "{$user->name}'s Wishlist",
                'slug' => Str::slug($user->name . '-wishlist'),
                'is_public' => true,
            ]
        );

        $items = $wishlist->items()->with(['product.seller', 'product.category'])->get();

        return view('wishlist.index', compact('wishlist', 'items'));
    }

    public function toggle(Product $product)
    {
        $user = Auth::user();
        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => "{$user->name}'s Wishlist",
                'slug' => Str::slug($user->name . '-wishlist'),
                'is_public' => true,
            ]
        );

        $existing = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $message = "Removed {$product->name} from your wishlist.";
        } else {
            WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_id' => $product->id,
                'added_price' => $product->effective_price,
            ]);
            $status = 'added';
            $message = "Added {$product->name} to your wishlist!";
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message,
                'wishlist_count' => $wishlist->items()->count(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function moveToCart(WishlistItem $item)
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $item->product_id,
            'seller_id' => $item->product->seller_id,
            'quantity' => 1,
            'price' => $item->product->effective_price,
        ]);

        $item->delete();

        return redirect()->route('cart.index')->with('success', "Moved {$item->product->name} to your cart!");
    }

    public function updateSocialDetails(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'event_name' => 'nullable|string|max:100', // e.g. "Arjun's Birthday Wishlist"
            'is_public' => 'required|boolean',
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())->firstOrFail();
        $wishlist->update([
            'name' => $request->name,
            'event_name' => $request->event_name,
            'is_public' => $request->boolean('is_public'),
        ]);

        return back()->with('success', 'Social wishlist settings updated.');
    }

    /**
     * Public Shared Wishlist (Section 30) - Accessible via unique share token without leaking customer PII
     */
    public function shared(string $token)
    {
        $wishlist = Wishlist::with(['items.product.seller'])
            ->where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        return view('wishlist.shared', compact('wishlist'));
    }
}
