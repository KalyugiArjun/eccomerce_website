<?php

namespace App\Services;

use App\Models\GroupBuyingGroup;
use App\Models\GroupBuyingParticipant;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupBuyingService
{
    /**
     * Create a new Group Buying campaign
     */
    public function createGroup(User $user, Product $product, int $targetBuyers = 2, int $validHours = 48): GroupBuyingGroup
    {
        return DB::transaction(function () use ($user, $product, $targetBuyers, $validHours) {
            // Standard multi-tier discounts
            $tiers = [
                ['buyers' => 2, 'price' => round($product->effective_price * 0.90, 2)],
                ['buyers' => 5, 'price' => round($product->effective_price * 0.82, 2)],
                ['buyers' => 10, 'price' => round($product->effective_price * 0.75, 2)],
            ];

            // Determine discount price for chosen target
            $discountPrice = round($product->effective_price * 0.90, 2);
            foreach ($tiers as $tier) {
                if ($tier['buyers'] <= $targetBuyers) {
                    $discountPrice = $tier['price'];
                }
            }

            $group = GroupBuyingGroup::create([
                'product_id' => $product->id,
                'creator_user_id' => $user->id,
                'group_code' => 'GRP-' . strtoupper(Str::random(8)),
                'target_buyers' => $targetBuyers,
                'current_buyers' => 1,
                'original_price' => $product->effective_price,
                'discounted_price' => $discountPrice,
                'discount_tiers' => $tiers,
                'status' => 'active',
                'expires_at' => now()->addHours($validHours),
            ]);

            // Add creator as first participant
            GroupBuyingParticipant::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'paid_amount' => $discountPrice,
                'status' => 'confirmed',
                'joined_at' => now(),
            ]);

            return $group;
        });
    }

    /**
     * Join an existing Group Buying campaign with atomic locking
     */
    public function joinGroup(User $user, string $groupCode): GroupBuyingGroup
    {
        return DB::transaction(function () use ($user, $groupCode) {
            $group = GroupBuyingGroup::where('group_code', $groupCode)
                ->lockForUpdate()
                ->firstOrFail();

            if ($group->status !== 'active' || $group->isExpired()) {
                throw new Exception("This group buying campaign has expired or closed.");
            }

            // Check if user already joined
            $existing = GroupBuyingParticipant::where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw new Exception("You have already joined this group buying deal!");
            }

            // Add participant
            GroupBuyingParticipant::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'paid_amount' => $group->discounted_price,
                'status' => 'confirmed',
                'joined_at' => now(),
            ]);

            $group->increment('current_buyers');

            // If target reached, mark complete
            if ($group->current_buyers >= $group->target_buyers) {
                $group->update(['status' => 'completed']);
            }

            return $group;
        });
    }
}
