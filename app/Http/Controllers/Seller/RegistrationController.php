<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRegisterRequest;
use App\Models\Seller;
use App\Models\SellerDocument;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    protected SellerService $sellerService;

    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;
    }

    public function showRegistrationForm()
    {
        $user = Auth::user();
        if ($user && $user->seller) {
            return view('seller.status', ['seller' => $user->seller]);
        }
        return view('seller.register');
    }

    public function register(SellerRegisterRequest $request)
    {
        $user = Auth::user();

        if ($user->seller) {
            return redirect()->route('seller.dashboard');
        }

        $data = $request->validated();

        if ($request->hasFile('store_logo')) {
            $data['store_logo'] = '/storage/' . $request->file('store_logo')->store('sellers/logos', 'public');
        }

        $seller = $this->sellerService->registerSeller($user, $data);

        // Upload documents if provided
        if ($request->hasFile('pan_document')) {
            $path = $request->file('pan_document')->store('sellers/documents', 'public');
            SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'pan',
                'file_path' => '/storage/' . $path,
                'original_name' => $request->file('pan_document')->getClientOriginalName(),
                'status' => 'pending',
            ]);
        }

        if ($request->hasFile('gst_document')) {
            $path = $request->file('gst_document')->store('sellers/documents', 'public');
            SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'gst',
                'file_path' => '/storage/' . $path,
                'original_name' => $request->file('gst_document')->getClientOriginalName(),
                'status' => 'pending',
            ]);
        }

        return redirect()->route('seller.status')->with('success', 'Your seller registration has been submitted! Our team will review your business credentials and approve your store within 24 hours.');
    }

    public function status()
    {
        $seller = Auth::user()->seller;
        if (!$seller) {
            return redirect()->route('seller.register');
        }
        return view('seller.status', compact('seller'));
    }
}
