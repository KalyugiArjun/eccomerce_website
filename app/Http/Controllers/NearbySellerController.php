<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NearbySellerController extends Controller
{
    protected SellerService $sellerService;

    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;
    }

    public function index(Request $request)
    {
        // Default coordinates: Lucknow Hazratganj center (26.8500, 80.9499)
        $userLat = (float) $request->input('lat', $request->cookie('shopkart_lat', 26.8500));
        $userLng = (float) $request->input('lng', $request->cookie('shopkart_lng', 80.9499));
        $radiusKm = (float) $request->input('radius', 30.0);
        $pincode = $request->input('pincode', $request->cookie('shopkart_pincode', '226001'));
        $city = $request->input('city', $request->cookie('shopkart_city', 'Lucknow'));

        $nearbySellers = $this->sellerService->getNearbySellers($userLat, $userLng, $radiusKm);

        // Pre-package map data for Leaflet OpenStreetMap markers
        $mapMarkers = $nearbySellers->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->store_name,
                'slug' => $s->slug,
                'lat' => $s->latitude,
                'lng' => $s->longitude,
                'distance' => $s->distance,
                'delivery_time' => $s->estimated_delivery_minutes,
                'rating' => $s->rating,
                'trust_score' => $s->trust_score,
                'logo' => $s->store_logo,
                'url' => route('seller.storefront', $s->slug),
            ];
        });

        return view('nearby.index', compact('nearbySellers', 'mapMarkers', 'userLat', 'userLng', 'radiusKm', 'pincode', 'city'));
    }
}
