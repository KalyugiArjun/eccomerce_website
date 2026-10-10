<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->isSeller() && !$user->isAdmin())) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Seller account required.'], 403);
            }
            return redirect()->route('seller.register')->with('warning', 'Please register as a seller to access the seller portal.');
        }

        $seller = $user->seller;
        if (!$seller && !$user->isAdmin()) {
            return redirect()->route('seller.register')->with('info', 'Please complete your seller store registration.');
        }

        // Check if seller status is pending or suspended
        if ($seller && $seller->status === 'suspended') {
            return redirect()->route('home')->with('error', 'Your seller account has been suspended by administration.');
        }

        return $next($request);
    }
}
