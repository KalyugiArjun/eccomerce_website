<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Seller::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $sellers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.sellers.index', compact('sellers'));
    }

    public function show(Seller $seller)
    {
        $seller->load(['user', 'documents', 'products', 'sellerOrders']);
        return view('admin.sellers.show', compact('seller'));
    }

    public function updateStatus(Request $request, Seller $seller)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,suspended',
            'commission_rate' => 'nullable|numeric|min:0|max:50',
        ]);

        $seller->update([
            'status' => $request->status,
            'commission_rate' => $request->filled('commission_rate') ? $request->commission_rate : $seller->commission_rate,
        ]);

        return back()->with('success', "Seller '{$seller->store_name}' status updated to " . strtoupper($request->status));
    }
}
