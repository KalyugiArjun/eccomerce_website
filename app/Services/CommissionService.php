<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Seller;
use App\Models\SellerOrder;

class CommissionService
{
    protected float $defaultGlobalCommissionRate = 10.0; // 10%

    /**
     * Determine commission rate based on precedence:
     * 1. Seller-specific custom rate (if configured on seller)
     * 2. Category rate (fallback)
     * 3. Global platform rate (default 10%)
     */
    public function calculateCommission(Seller $seller, float $subtotal): array
    {
        $rate = $this->defaultGlobalCommissionRate;

        if ($seller->commission_rate !== null && $seller->commission_rate >= 0) {
            $rate = (float) $seller->commission_rate;
        }

        $amount = round(($subtotal * $rate) / 100.0, 2);

        return [
            'rate' => $rate,
            'amount' => $amount,
            'seller_share' => max(0, round($subtotal - $amount, 2)),
        ];
    }

    /**
     * Create official commission ledger record
     */
    public function recordCommission(SellerOrder $sellerOrder): Commission
    {
        return Commission::create([
            'seller_order_id' => $sellerOrder->id,
            'seller_id' => $sellerOrder->seller_id,
            'order_amount' => $sellerOrder->subtotal,
            'commission_rate' => $sellerOrder->platform_commission_rate,
            'commission_amount' => $sellerOrder->platform_commission_amount,
            'seller_earnings' => $sellerOrder->seller_net_earnings,
            'status' => 'settled',
        ]);
    }
}
