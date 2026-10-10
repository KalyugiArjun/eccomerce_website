<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_cannot_view_or_update_another_sellers_sub_order(): void
    {
        // Seller 1
        $user1 = User::factory()->create(['role' => 'seller']);
        $seller1 = Seller::create([
            'user_id' => $user1->id,
            'store_name' => 'Store 1',
            'slug' => 'store-1',
            'phone' => '9876543211',
            'business_address' => 'Delhi',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => 'approved',
        ]);

        // Seller 2
        $user2 = User::factory()->create(['role' => 'seller']);
        $seller2 = Seller::create([
            'user_id' => $user2->id,
            'store_name' => 'Store 2',
            'slug' => 'store-2',
            'phone' => '9876543212',
            'business_address' => 'Mumbai',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => 'approved',
        ]);

        // Customer & Order
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'ORD-TEST12345',
            'user_id' => $customer->id,
            'customer_name' => 'Customer Test',
            'customer_email' => 'customer@test.com',
            'customer_phone' => '9876543210',
            'shipping_address' => 'Delhi 110001',
            'subtotal' => 1000,
            'grand_total' => 1180,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'confirmed',
        ]);

        // Sub order belonging exclusively to Seller 2
        $subOrder2 = SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller2->id,
            'sub_order_number' => 'SUB-TEST12345-B',
            'subtotal' => 1000,
            'grand_total' => 1180,
            'status' => 'confirmed',
        ]);

        // Seller 1 attempts to view Seller 2's sub-order -> MUST return 404 Not Found (scoped strictly to own store)
        $response = $this->actingAs($user1)->get(route('seller.orders.show', $subOrder2->sub_order_number));
        $response->assertStatus(404);

        // Seller 1 attempts to update status of Seller 2's sub-order -> MUST return 404 Not Found
        $updateResponse = $this->actingAs($user1)->post(route('seller.orders.update-status', $subOrder2->sub_order_number), [
            'status' => 'shipped',
        ]);
        $updateResponse->assertStatus(404);

        // Seller 2 CAN view their own sub-order -> 200 OK
        $ownerResponse = $this->actingAs($user2)->get(route('seller.orders.show', $subOrder2->sub_order_number));
        $ownerResponse->assertStatus(200);
    }

    public function test_seller_cannot_modify_another_sellers_product(): void
    {
        $user1 = User::factory()->create(['role' => 'seller']);
        $seller1 = Seller::create([
            'user_id' => $user1->id,
            'store_name' => 'Store 1',
            'slug' => 'store-1',
            'phone' => '9876543211',
            'business_address' => 'Delhi',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'status' => 'approved',
        ]);

        $user2 = User::factory()->create(['role' => 'seller']);
        $seller2 = Seller::create([
            'user_id' => $user2->id,
            'store_name' => 'Store 2',
            'slug' => 'store-2',
            'phone' => '9876543212',
            'business_address' => 'Mumbai',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => 'approved',
        ]);

        $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'is_active' => true]);

        $productSeller2 = Product::create([
            'seller_id' => $seller2->id,
            'category_id' => $category->id,
            'name' => 'Seller 2 Laptop',
            'slug' => 'seller-2-laptop',
            'sku' => 'SKU-LAP-2',
            'price' => 50000,
            'stock' => 10,
            'status' => 'published',
        ]);

        // Seller 1 attempts to edit Seller 2's product -> 403 Forbidden
        $response = $this->actingAs($user1)->get(route('seller.products.edit', $productSeller2->id));
        $response->assertStatus(403);

        // Seller 1 attempts to update Seller 2's product -> 403 Forbidden
        $updateResponse = $this->actingAs($user1)->put(route('seller.products.update', $productSeller2->id), [
            'name' => 'Hacked Product Name',
            'sku' => 'SKU-LAP-2',
            'category_id' => $category->id,
            'price' => 100,
            'description' => 'Hacked description',
            'stock' => 5,
        ]);
        $updateResponse->assertStatus(403);
    }
}
