<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'shipping_fee' => $this->shipping_fee,
            'tax_amount' => $this->tax_amount,
            'grand_total' => $this->grand_total,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status,
            'is_gift' => $this->is_gift,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'seller_sub_orders' => $this->sellerOrders->map(function ($so) {
                return [
                    'sub_order_number' => $so->sub_order_number,
                    'seller_name' => $so->seller->store_name,
                    'subtotal' => $so->subtotal,
                    'status' => $so->status,
                    'tracking_number' => $so->tracking_number,
                    'items' => $so->items->map(function ($item) {
                        return [
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    }),
                ];
            }),
        ];
    }
}
