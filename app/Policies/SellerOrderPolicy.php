<?php

namespace App\Policies;

use App\Models\SellerOrder;
use App\Models\User;

class SellerOrderPolicy
{
    public function view(User $user, SellerOrder $sellerOrder): bool
    {
        return $user->isAdmin() || ($user->isSeller() && $user->seller?->id === $sellerOrder->seller_id);
    }

    public function update(User $user, SellerOrder $sellerOrder): bool
    {
        return $user->isAdmin() || ($user->isSeller() && $user->seller?->id === $sellerOrder->seller_id);
    }
}
