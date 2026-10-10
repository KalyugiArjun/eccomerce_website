<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class FastDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $speed = $request->input('speed', '30min'); // 30min, 1hour, today, tomorrow, anytime

        $query = Product::with(['seller', 'category'])
            ->where('status', 'published')
            ->where('stock', '>', 0)
            ->whereHas('seller', fn($s) => $s->where('status', 'approved')->where('local_delivery_available', true));

        match ($speed) {
            '30min' => $query->where('delivery_time_minutes', '<=', 30),
            '1hour' => $query->where('delivery_time_minutes', '<=', 60),
            'today' => $query->where('delivery_time_minutes', '<=', 360),
            'tomorrow' => $query->where('delivery_time_minutes', '<=', 1440),
            default => null,
        };

        if ($cat = $request->input('category')) {
            $query->whereHas('category', fn($c) => $c->where('slug', $cat));
        }

        $products = $query->orderBy('delivery_time_minutes', 'asc')->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->whereNull('parent_id')->get();

        return view('fast-delivery.index', compact('products', 'speed', 'categories'));
    }
}
