<?php

namespace App\Http\Controllers;

use App\Services\AiAssistantService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        return view('ai.assistant');
    }

    public function query(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array',
        ]);

        $result = $this->aiService->ask(
            $request->input('message'),
            $request->input('history', [])
        );

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'products' => $result['products']->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'url' => route('products.show', $p->slug),
                    'price' => $p->price,
                    'discount_price' => $p->discount_price,
                    'effective_price' => $p->effective_price,
                    'thumbnail' => $p->thumbnail,
                    'rating' => $p->rating,
                    'seller_name' => $p->seller->store_name ?? 'ShopKart Verified',
                    'delivery_minutes' => $p->delivery_time_minutes,
                ];
            }),
            'intent' => $result['intent'],
        ]);
    }
}
